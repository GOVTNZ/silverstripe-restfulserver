<?php

class APIInfoTest extends BaseRestfulServerTest {

	protected $extraDataObjects = array(
		'APITestObject',
		'APITestPageObject',
		'StaffTestObject'
	);

	public function testClassCanBeFilteredBy() {
		$result = \RestfulServer\APIInfo::class_can_be_filtered_by('APITestObject', 'Name');

		$this->assertTrue($result);
	}

	public function testClassCanBeFilteredByWithInvalidFieldName() {
		$result = \RestfulServer\APIInfo::class_can_be_filtered_by('APITestObject', 'NonExistentField');

		$this->assertFalse($result);
	}

	public function testGetRelationMethodFromName() {
		$relationMethod = \RestfulServer\APIInfo::get_relation_method_from_name('StaffTestObject', 'direct-reports');

		$this->assertEquals('DirectReports', $relationMethod);
	}

	public function testGetRelationMethodFromNameWithInvalidName() {
		$exceptionThrown = false;

		try {
			\RestfulServer\APIInfo::get_relation_method_from_name('StaffTestObject', 'invalid-name');
		} catch (RestfulServer\Exception $exception) {
			$exceptionThrown = true;
		}

		$this->assertTrue($exceptionThrown);
	}

	public function testGetRelationMethodFromNameWithNoRelationAlias() {
		$relationMethod = \RestfulServer\APIInfo::get_relation_method_from_name('APITestPageObject', 'Children');

		$this->assertEquals('Children', $relationMethod);
	}

	public function testGetRelationMethodFromNameWithInvalidNameAndNoRelationAlias() {
		$exceptionThrown = false;

		try {
			\RestfulServer\APIInfo::get_relation_method_from_name('APITestPageObject', 'InvalidRelation');
		} catch (RestfulServer\Exception $exception) {
			$exceptionThrown = true;
		}

		$this->assertTrue($exceptionThrown);
	}

	public function testGetAllAPIEndPoints() {
		$endPoints = \RestfulServer\APIInfo::get_all_end_points();

		$this->assertEquals(3, count($endPoints));
	}

}