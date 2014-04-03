<?php

namespace RestfulServer;

class APITestPageObject extends APITestObject implements \TestOnly {

	private static $db = array(
		'TestField' => 'Text'
	);

	private static $has_one = array(
		'Parent' => 'RestfulServer\APITestPageObject'
	);

	private static $has_many = array(
		'Children' => 'RestfulServer\APITestPageObject'
	);

	private static $api_access = array(
		'end_point_alias' => 'testpages',
		'singular_name' => 'testPage',
		'plural_name' => 'testPages'
	);

}