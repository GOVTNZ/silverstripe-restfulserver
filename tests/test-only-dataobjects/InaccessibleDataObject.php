<?php

namespace RestfulServer;

class InaccessibleDataObject extends \DataObject implements \TestOnly {

	private static $db = array(
		'Name' => 'Text'
	);

	private static $has_one = array(
		'StaffTest' => 'RestfulServer\StaffTestObject',
		'StaffTestAlias' => 'RestfulServer\StaffTestObjectWithAliases'
	);

}
