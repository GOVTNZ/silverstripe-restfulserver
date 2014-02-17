<?php

class APIInfoTest extends SapphireTest {

	protected $extraDataObjects = array(
		'APITestObject',
		'APITestPageObject',
		'StaffTestObject'
	);

	public function testClassCanBeFilteredBy() {
		$result = APIInfo::class_can_be_filtered_by('APITestObject', 'Name');

		$this->assertTrue($result);
	}

	public function testClassCanBeFilteredByWithInvalidFieldName() {
		$result = APIInfo::class_can_be_filtered_by('APITestObject', 'NonExistentField');

		$this->assertFalse($result);
	}

	public function testGetRelationMethodFromName() {
		$relationMethod = APIInfo::get_relation_method_from_name('StaffTestObject', 'direct-reports');

		$this->assertEquals('DirectReports', $relationMethod);
	}

	public function testGetRelationMethodFromNameWithInvalidName() {
		$relationMethod = APIInfo::get_relation_method_from_name('StaffTestObject', 'invalid-name');

		$this->assertNull($relationMethod);
	}

	public function testGetRelationMethodFromNameWithNoRelationAlias() {
		$relationMethod = APIInfo::get_relation_method_from_name('APITestPageObject', 'Children');

		$this->assertEquals('Children', $relationMethod);
	}

	public function testGetRelationMethodFromNameWithInvalidNameAndNoRelationAlias() {
		$relationMethod = APIInfo::get_relation_method_from_name('APITestPageObject', 'InvalidRelation');

		$this->assertNull($relationMethod);
	}

}