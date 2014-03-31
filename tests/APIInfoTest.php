<?php

namespace RestfulServer;

class APIInfoTest extends BaseRestfulServerTest {

	protected $extraDataObjects = array(
		'RestfulServer\APITestObject',
		'RestfulServer\APITestPageObject',
		'RestfulServer\StaffTestObject',
		'RestfulServer\StaffTestObjectWithAliases'
	);

	public function testClassCanBeFilteredBy() {
		$result = APIInfo::class_can_be_filtered_by('RestfulServer\APITestObject', 'Name');

		$this->assertTrue($result);
	}

	public function testClassCanBeFilteredByWithInvalidFieldName() {
		$result = APIInfo::class_can_be_filtered_by('RestfulServer\APITestObject', 'NonExistentField');

		$this->assertFalse($result);
	}

	public function testGetRelationMethodFromName() {
		$relationMethod = APIInfo::get_relation_method_from_name('RestfulServer\StaffTestObjectWithAliases', 'direct-reports');

		$this->assertEquals('DirectReports', $relationMethod);
	}

	public function testGetRelationMethodFromNameWithInvalidName() {
		$exceptionThrown = false;

		try {
			APIInfo::get_relation_method_from_name('RestfulServer\StaffTestObject', 'invalid-name');
		} catch (Exception $exception) {
			$exceptionThrown = true;
		}

		$this->assertTrue($exceptionThrown);
	}

	public function testGetRelationMethodFromNameWithNoRelationAlias() {
		$relationMethod = APIInfo::get_relation_method_from_name('RestfulServer\APITestPageObject', 'Children');

		$this->assertEquals('Children', $relationMethod);
	}

	public function testGetRelationMethodFromNameWithInvalidNameAndNoRelationAlias() {
		$exceptionThrown = false;

		try {
			APIInfo::get_relation_method_from_name('RestfulServer\APITestPageObject', 'InvalidRelation');
		} catch (Exception $exception) {
			$exceptionThrown = true;
		}

		$this->assertTrue($exceptionThrown);
	}

	public function testGetAllAPIEndPoints() {
		$endPoints = APIInfo::get_all_end_points();

		$this->assertEquals(4, count($endPoints));
	}

	public function testGetFieldsFor() {
		$fields = APIInfo::get_fields_for('RestfulServer\StaffTestObject');

		$expectedFields = array('ID', 'Created', 'LastEdited', 'Name', 'JobTitle', 'ManagerID');

		foreach ($expectedFields as $expectedField) {
			$this->assertContains($expectedField, $fields);
		}
	}

	public function testGetFieldsForWithAliases() {
		$fields = APIInfo::get_fields_for('RestfulServer\StaffTestObjectWithAliases');

		$expectedFields = array('id', 'Created', 'LastEdited', 'name', 'jobTitleAlias', 'ManagerID');

		foreach ($expectedFields as $expectedField) {
			$this->assertContains($expectedField, $fields);
		}
	}

	public function testGetRelationsFor() {
		$relations = APIInfo::get_relations_for('RestfulServer\StaffTestObject');

		$expectedRelations = array(
			'DirectReports',
			'Friends',
			'InverseFriends'
		);

		foreach ($expectedRelations as $expectedRelation) {
			$this->assertContains($expectedRelation, $relations);
		}
	}

	public function testGetRelationsForWithAliases() {
		$relations = APIInfo::get_relations_for('RestfulServer\StaffTestObjectWithAliases');

		$expectedRelations = array(
			'direct-reports',
			'friends',
			'InverseFriends'
		);

		foreach ($expectedRelations as $expectedRelation) {
			$this->assertContains($expectedRelation, $relations);
		}
	}

}