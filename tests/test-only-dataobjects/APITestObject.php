<?php

class APITestObject extends DataObject implements TestOnly {

	private static $db = array(
		'Name' => 'Varchar(255)'
	);

	private static $api_access = array(
		'end_point_alias' => 'testobjects',
		'singular_name' => 'testObject',
		'plural_name' => 'testObjects'
	);

}
