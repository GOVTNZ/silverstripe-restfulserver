<?php

namespace RestfulServer;

class StaffTestObjectWithView extends \DataObject implements \TestOnly {

	private static $db = array(
		'Name' => 'Text',
		'JobTitle' => 'Text'
	);

	private static $has_one = array(
		'Manager' => 'RestfulServer\StaffTestObjectWithView'
	);

	private static $has_many = array(
		'DirectReports' => 'RestfulServer\StaffTestObjectWithView',
		'InaccessibleDataObjects' => 'RestfulServer\InaccessibleDataObject'
	);

	private static $many_many = array(
		'Friends' => 'RestfulServer\StaffTestObjectWithView'
	);

	private static $belongs_many_many = array(
		'InverseFriends' => 'RestfulServer\StaffTestObjectWithView'
	);

	private static $api_access = array(
		'end_point_alias' => 'stafftestwithview',
		'singular_name' => 'staffMember',
		'plural_name' => 'staff',
		'view' => array(
			'ID',
			'Name'
		)
	);

}