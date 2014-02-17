<?php

class StaffTestObject extends DataObject implements TestOnly {

	private static $db = array(
		'Name' => 'Text',
		'JobTitle' => 'Text'
	);

	private static $has_one = array(
		'Manager' => 'StaffTestObject'
	);

	private static $has_many = array(
		'DirectReports' => 'StaffTestObject'
	);

	private static $many_many = array(
		'Friends' => 'StaffTestObject'
	);

	private static $belongs_many_many = array(
		'InverseFriends' => 'StaffTestObject'
	);

	private static $api_access = array(
		'end_point_alias' => 'stafftest',
		'singular_name' => 'staffMember',
		'plural_name' => 'staff',
		'relation_aliases' => array(
			'direct-reports' => 'DirectReports',
			'friends' => 'Friends'
		)
	);

}