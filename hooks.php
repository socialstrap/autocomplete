<?php
/**
 * Autocomplete add-on
 *
 * @package SocialStrap add-on
 * @author Milos Stojanovic
 * @copyright 2014 interactive32.com
 * 
 */

$this->attach('view_head', 10, function($view) {

	echo '<style type="text/css">
		.user-autocomplete {padding: 5px 10px;}
		.user-autocomplete a:hover {text-decoration: none;}
		.user-autocomplete img {height: 32px;width: 32px;margin-right: 10px;}
		.ui-autocomplete li {opacity:0.9;}
		.ui-autocomplete li:hover {opacity:1;}
		.ui-helper-hidden-accessible {display: none;}
		.ui-autocomplete.dropdown-menu {z-index: 5000;}
		</style>';
});

$this->attach('view_body', 10, function($view) {
	
	require_once realpath(dirname(__FILE__)) . "/data.php";
	
	$base_url = $view->baseUrl();
	$base_addon_url = $view->baseUrl().'/addons/'.basename(__DIR__);
	
	$front = Zend_Controller_Front::getInstance();
	$request = $front->getRequest();
	$controller = $request->getControllerName();
	$action = $request->getActionName();
	
	echo '<script type="text/javascript" src="'. $base_addon_url. '/js/jquery-ui-1.10.4.custom.min.js"></script>';
	echo '<script type="text/javascript" src="'.$base_addon_url.'/js/jquery-ui.triggeredAutocomplete.js"></script>';
	
	if (PLUGIN_AUTOCOMPLETE_SEARCH_TAGS) {
		$sources = '{"#" : "'.$base_addon_url.'/?search=tags", "@": "'.$base_addon_url.'/?search=users"}';
	} else {
		$sources = '{"@": "'.$base_addon_url.'/?search=users"}';
	}
	
	echo '
		<script>
		// attach autocomplete
		$(document).on("keydown", "textarea[name=\'content\'], textarea#comment, input[name=\'comment\'], #term", function (e) {
			$(this).triggeredAutocomplete({
				sources:  '.$sources.',
				minLength: '.PLUGIN_AUTOCOMPLETE_MIN_CHARS.'
			}); 
		})
		// hide on scroll
		$(window).scroll(function(event){
			var el = $(".ui-autocomplete:visible");
			if (el.length == 0) return;
			var offset = el.offset();
			var w = $(window);
			if (offset.top-w.scrollTop() < 40) $(el).css("z-index","1000"); else $(el).css("z-index","5000");
		});
		</script>
		';
	
	// push main search action to posts instead uf users
	if ($controller != 'search') {
		echo '
		<script>
		$(document).ready(function () {
			$(".nav.navbar-search form").attr("action", "'.$base_url.'/search/posts/");
		});
		</script>
		';
	}

});

