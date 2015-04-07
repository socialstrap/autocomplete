<?php 
// double-check if admin
if (! defined('PUBLIC_PATH') || ! Zend_Auth::getInstance()->hasIdentity() || Zend_Auth::getInstance()->getIdentity()->role != 'admin') die('not allowed');

$fn = realpath(dirname(__FILE__)) . "/data.php";

// save?
if (isset($_POST['submitbtn'])) {

	$min_chars = isset($_POST['min-chars']) ? (int)$_POST['min-chars'] : 1;
	
	$search_tags = isset($_POST['search-tags']) ? 1 : 0;
	$tags_fetch_limit = isset($_POST['tags-fetch-limit']) ? (int)$_POST['tags-fetch-limit'] : 300;
	
	$fcontent ="<?php\n
	define('PLUGIN_AUTOCOMPLETE_MIN_CHARS', '".$min_chars."');
	define('PLUGIN_AUTOCOMPLETE_SEARCH_TAGS', ".$search_tags.");
	define('PLUGIN_AUTOCOMPLETE_TAGS_FETCH_LIMIT', ".$tags_fetch_limit.");
	
		";
	@file_put_contents($fn, $fcontent);
}

require_once $fn;
?>


<div class="well">

<?php if (! is_writable($fn)) echo '<p>Error: file not writtable: <br />' .$fn. '<hr /></p>';?>

<form action="" method="post">

<div class="form-group">
	<label for="min-chars">Minimum characters for server query:</label><br/>
	<input value="<?php echo PLUGIN_AUTOCOMPLETE_MIN_CHARS;?>" class="form-control" name="min-chars" id="min-chars">
</div>

<div class="form-group">
	<label for="search-tags">Search and suggest recent Hashtags?</label>
	<input type="checkbox" <?php if (PLUGIN_AUTOCOMPLETE_SEARCH_TAGS) echo 'checked="checked"';?> id="search-tags" name="search-tags">
</div>

<div class="form-group">
	<label for="tags-fetch-limit">Number of Posts to fetch when searching for recent HashTags:</label><br/>
	<input value="<?php echo PLUGIN_AUTOCOMPLETE_TAGS_FETCH_LIMIT;?>" class="form-control" name="tags-fetch-limit" id="tags-fetch-limit">
</div>

<hr/>

<div class="pull-right">
	<input type="submit" name="submitbtn" id="submitbtn" value="Update" class="submit btn btn-default">
</div>

</form>

</div>


