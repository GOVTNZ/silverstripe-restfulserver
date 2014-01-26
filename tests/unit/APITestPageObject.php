<?php

class APITestPageObject extends SiteTree implements TestOnly {

	private static $db = array(
		'TestField' => 'Text'
	);

	private static $api_access = array(
		'end_point_alias' => 'testpages',
		'singular_name' => 'testPage',
		'plural_name' => 'testPages'
	);

}