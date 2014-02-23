<?php

class StaffTestObjectWithFieldAliases extends DataObject implements TestOnly {

	private static $db = array(
		'Name' => 'Text',
		'JobTitle' => 'Text'
	);

	private static $has_one = array(
		'Manager' => 'StaffTestObjectWithFieldAliases'
	);

	private static $has_many = array(
		'DirectReports' => 'StaffTestObjectWithFieldAliases'
	);

	private static $many_many = array(
		'Friends' => 'StaffTestObjectWithFieldAliases'
	);

	private static $belongs_many_many = array(
		'InverseFriends' => 'StaffTestObjectWithFieldAliases'
	);

	private static $api_access = array(
		'end_point_alias' => 'stafftestfieldalias',
		'singular_name' => 'staffMember',
		'plural_name' => 'staff',
		'field_aliases' => array(
			'id' => 'ID',
			'name' => 'Name',
			'jobTitleAlias' => 'JobTitle'
		),
		'relation_aliases' => array(
			'direct-reports' => 'DirectReports',
			'friends' => 'Friends'
		)
	);

}
