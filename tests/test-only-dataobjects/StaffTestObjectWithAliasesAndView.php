<?php

namespace RestfulServer;

class StaffTestObjectWithAliasesAndView extends \DataObject implements \TestOnly {

	private static $db = array(
		'Name' => 'Text',
		'JobTitle' => 'Text'
	);

	private static $has_one = array(
		'Manager' => 'RestfulServer\StaffTestObjectWithAliasesAndView'
	);

	private static $has_many = array(
		'DirectReports' => 'RestfulServer\StaffTestObjectWithAliasesAndView',
		'InaccessibleDataObjects' => 'RestfulServer\InaccessibleDataObject'
	);

	private static $many_many = array(
		'Friends' => 'RestfulServer\StaffTestObjectWithAliasesAndView',
		'TestRelations' => 'RestfulServer\APITestObject'
	);

	private static $belongs_many_many = array(
		'InverseFriends' => 'RestfulServer\StaffTestObjectWithAliasesAndView',
		'InverseTestRelations' => 'RestfulServer\APITestObject'
	);

	private static $api_access = array(
		'end_point_alias' => 'stafftestaliasandview',
		'singular_name' => 'staffMember',
		'plural_name' => 'staff',
		'view' => array(
			'ID',
			'Name'
		),
		'field_aliases' => array(
			'id' => 'ID',
			'name' => 'Name',
			'jobTitleAlias' => 'JobTitle'
		),
		'relation_aliases' => array(
			'direct-reports' => 'DirectReports',
			'friends' => 'Friends',
			'test-relations' => 'TestRelations',
			'inverse-test-relations' => 'InverseTestRelations',
			'inaccessible-relation' => 'InaccessibleDataObjects'
		)
	);

}
