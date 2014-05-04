<?php

namespace RestfulServer;

class StaffTestObject extends \DataObject implements \TestOnly {

	private static $db = array(
		'Name' => 'Text',
		'JobTitle' => 'Text'
	);

	private static $has_one = array(
		'Manager' => 'RestfulServer\StaffTestObject'
	);

	private static $has_many = array(
		'DirectReports' => 'RestfulServer\StaffTestObject',
		'InaccessibleDataObjects' => 'RestfulServer\InaccessibleDataObject'
	);

	private static $many_many = array(
		'Friends' => 'RestfulServer\StaffTestObject'
	);

	private static $belongs_many_many = array(
		'InverseFriends' => 'RestfulServer\StaffTestObject'
	);

	private static $api_access = array(
		'end_point_alias' => 'stafftest',
		'singular_name' => 'staffMember',
		'plural_name' => 'staff',
		'dynamic_relations' => array(
			'AllStaff' => 'RestfulServer\StaffTestObject'
		)
	);

	public function AllStaff() {
		return self::get();
	}

}