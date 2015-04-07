<?php
/**
 * Autocomplete add-on
 *
 * @package SocialStrap add-on
 * @author Milos Stojanovic
 * @copyright 2014 interactive32.com
 */

require_once realpath(dirname(__FILE__)) . "/data.php";

$type = isset($_GET['search']) && $_GET['search'] == 'users' ? 'users' : 'tags';
$url_search_term = isset($_GET['term']) && $_GET['term'] ? $_GET['term'] : '';

// strip tags
$filter_st = new Zend_Filter_StripTags();
$url_search_term = $filter_st->filter($url_search_term);

if (!$url_search_term || strlen($url_search_term) < PLUGIN_AUTOCOMPLETE_MIN_CHARS) {
	die();
}

if ($type == 'users') {
	autocomplete_search_users($url_search_term, $this->GetStorageUrl('avatar'));
} else {
	autocomplete_search_tags($url_search_term);
}


function autocomplete_search_users($term, $storage_url)
{
	
	$Profiles = new Application_Model_Profiles();

	// quote
	$search_term = $Profiles->getDefaultAdapter()->quote("%{$term}%");
	
	if (Zend_Auth::getInstance()->hasIdentity()) {
		$user_id = (int)Zend_Auth::getInstance()->getIdentity()->id;
		$join = "LEFT JOIN connections c ON c.follow_id = p.id AND c.user_id = ".$user_id;
		$order = "ORDER BY c.created_on DESC, p.type DESC";
	} else {
		$join = "";
		$order = "ORDER BY p.type DESC";
	}
	
	$sql = "
	SELECT
	p.name AS label,
	p.screen_name AS name,
	p.avatar as avatar
	
	FROM profiles p
	{$join}
	
	WHERE p.is_hidden = 0
	AND (p.activationkey = 'activated' OR p.type != 'user')
	AND (p.name like {$search_term} OR p.screen_name like {$search_term})
	
	{$order}
	
	LIMIT 5
	";
	
	$result = $Profiles->getDefaultAdapter()->fetchAll($sql);
	
	if (!$result) die();
	
	foreach ($result as &$user) {
	$user['link'] = Application_Plugin_Common::getFullBaseUrl() .'/'. $user['label'];
	$user['avatar'] = $storage_url . $user['avatar'];
	}
	
	echo json_encode($result);
	
	// stop view render
	die();
	
}


function autocomplete_search_tags($term)
{
	if (!PLUGIN_AUTOCOMPLETE_SEARCH_TAGS) die();
	
	// Get cache from registry
	$cache = Zend_Registry::get('cache');
	
	if (($tags = $cache->load('addon_autocomplete')) === false) {
	
		// cache missed, we need to build this again
	
		$limit = (int) PLUGIN_AUTOCOMPLETE_TAGS_FETCH_LIMIT;
		
		$sql = "
		SELECT content
		FROM posts
		WHERE content like '%#%'
		ORDER BY created_on DESC
		LIMIT {$limit}
		";
	
		$Model = new Application_Model_Addons();
		$rows = $Model->getAdapter()->fetchCol($sql);
	
		if (empty($rows)) return;
	
		// merge all content to a single string
		$full_content = '';
		$raw_tags = null;
		$tags = array();
	
		foreach ($rows as $key => $val) {
			// remove dupes from a single post to avoid abuse
			$val = implode(' ',array_unique(explode(' ', $val)));
			$val = implode("\n",array_unique(explode("\n", $val)));
				
			// concate to one big string
			$full_content .= $val.' ';
		}
	
		// match tags
		//preg_match_all("/(^|[\t\r\n\s])#(\w+)/u", $full_content, $raw_tags); // not uft8 safe
		preg_match_all("/#([\p{L}\p{N}\-_]+)(?=\s|\Z)/u", $full_content, $raw_tags);
	
		if (!$raw_tags || empty($raw_tags[1])) return;
	
		// take 2nd match
		$raw_tags = $raw_tags[1];
	
		// transform and count
		foreach ($raw_tags as $key => $value) {
			$tags[$value] = isset($tags[$value]) ? ++$tags[$value] : 1;
		}
	
		// sort by count
		arsort($tags);
	
		// save to cache
		$cache->save($tags);
	}
	
	$result = array();
	$total = 0;
	
	foreach ($tags as $tag => $count) {
		if (strpos(strtolower($tag), strtolower($term)) !== false) {
			$result[] = array('value' => $tag, 'label' => $tag);
			$total++;
		}
		
		if ($total >= 5) break;
	}
	
	echo json_encode($result);

	die;
}
