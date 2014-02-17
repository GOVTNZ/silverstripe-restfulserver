<?php

class APITestPageObject extends APITestObject implements TestOnly {

	private static $db = array(
		'TestField' => 'Text'
	);

	private static $has_one = array(
		'Parent' => 'APITestPageObject'
	);

	private static $has_many = array(
		'Children' => 'APITestPageObject'
	);

	private static $api_access = array(
		'end_point_alias' => 'testpages',
		'singular_name' => 'testPage',
		'plural_name' => 'testPages'
	);

}