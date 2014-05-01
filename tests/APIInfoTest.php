<?php

namespace RestfulServer;

class APIInfoTest extends BaseRestfulServerTest {

	protected $extraDataObjects = array(
		'RestfulServer\APITestObject',
		'RestfulServer\APITestPageObject',
		'RestfulServer\StaffTestObject',
		'RestfulServer\StaffTestObjectWithAliases',
		'RestfulServer\StaffTestObjectWithView',
		'RestfulServer\StaffTestObjectWithAliasesAndView'
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

		$this->assertEquals(6, count($endPoints));
	}

	public function testGetAliasedFieldsFor() {
		$fields = APIInfo::get_aliased_fields_for('RestfulServer\StaffTestObject');

		$expectedFields = array('ID', 'Created', 'LastEdited', 'Name', 'JobTitle', 'ManagerID');

		foreach ($expectedFields as $expectedField) {
			$this->assertContains($expectedField, $fields);
		}
	}

	public function testGetAliasedFieldsForWithAliases() {
		$fields = APIInfo::get_aliased_fields_for('RestfulServer\StaffTestObjectWithAliases');

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

	public function testGetClassNameByRelation() {
		$className = APIInfo::get_class_name_by_relation('RestfulServer\StaffTestObjectWithAliases', 'DirectReports');
		$this->assertEquals($className, 'RestfulServer\StaffTestObjectWithAliases');

		$className = APIInfo::get_class_name_by_relation('RestfulServer\StaffTestObjectWithAliases', 'Friends');
		$this->assertEquals($className, 'RestfulServer\StaffTestObjectWithAliases');

		$className = APIInfo::get_class_name_by_relation('RestfulServer\StaffTestObjectWithAliases', 'InverseFriends');
		$this->assertEquals($className, 'RestfulServer\StaffTestObjectWithAliases');
	}

	public function testGetClassNameByRelationWithNonExistentRelation() {
		$exceptionThrown = false;

		try {
			APIInfo::get_class_name_by_relation('RestfulServer\StaffTestObjectWithAliases', 'NonExistent');
		} catch (Exception $e) {
			$exceptionThrown = true;
		}

		$this->assertTrue($exceptionThrown);
	}

	public function testHasApiAccess() {
		$this->assertTrue(APIInfo::has_api_access('RestfulServer\StaffTestObject'));
		$this->assertFalse(APIInfo::has_api_access('RestfulServer\InaccessibleDataObject'));
	}

	public function testGetAvailableFieldsFor() {
		$expectedWithoutView = array(
			'ID',
			'Created',
			'LastEdited',
			'ClassName',
			'RecordClassName',
			'Name',
			'JobTitle',
			'ManagerID'
		);

		$availableFields = APIInfo::get_viewable_fields_for('RestfulServer\StaffTestObject');
		$this->assertEquals($expectedWithoutView, $availableFields);

		$availableFields = APIInfo::get_viewable_fields_for('RestfulServer\StaffTestObjectWithAliases');
		$this->assertEquals($expectedWithoutView, $availableFields);

		$expectedWithView = array(
			'ID',
			'Name'
		);

		$availableFields = APIInfo::get_viewable_fields_for('RestfulServer\StaffTestObjectWithView');
		$this->assertEquals($expectedWithView, $availableFields);

		$availableFields = APIInfo::get_viewable_fields_for('RestfulServer\StaffTestObjectWithAliasesAndView');
		$this->assertEquals($expectedWithView, $availableFields);
	}

	public function testGetAvailableFieldsWithAliasesFor() {
		$expectedWithoutView = array(
			'ID',
			'Created',
			'LastEdited',
			'ClassName',
			'RecordClassName',
			'Name',
			'JobTitle',
			'ManagerID'
		);

		$availableFields = APIInfo::get_viewable_fields_with_aliases_for('RestfulServer\StaffTestObject');
		$this->assertEquals($expectedWithoutView, $availableFields);

		$expectedWithoutView = array(
			'id',
			'Created',
			'LastEdited',
			'ClassName',
			'RecordClassName',
			'name',
			'jobTitleAlias',
			'ManagerID'
		);

		$availableFields = APIInfo::get_viewable_fields_with_aliases_for('RestfulServer\StaffTestObjectWithAliases');
		$this->assertEquals($expectedWithoutView, $availableFields);

		$expectedWithView = array(
			'ID',
			'Name'
		);

		$availableFields = APIInfo::get_viewable_fields_with_aliases_for('RestfulServer\StaffTestObjectWithView');
		$this->assertEquals($expectedWithView, $availableFields);

		$expectedWithView = array(
			'id',
			'name'
		);

		$availableFields = APIInfo::get_viewable_fields_with_aliases_for('RestfulServer\StaffTestObjectWithAliasesAndView');
		$this->assertEquals($expectedWithView, $availableFields);
	}

	public function testGetAvailableRelationsFor() {
		$availableRelations = APIInfo::get_available_relations_for('RestfulServer\StaffTestObject');
		$expectedRelations = array(
			'DirectReports',
			'InverseFriends',
			'Friends'
		);

		$this->assertEquals(count($expectedRelations), count($availableRelations));

		foreach ($expectedRelations as $relationName) {
			$this->assertContains($relationName, $availableRelations);
		}

		$availableRelations = APIInfo::get_available_relations_for('RestfulServer\StaffTestObjectWithAliases');
		$expectedRelations = array(
			'DirectReports',
			'InverseFriends',
			'Friends',
			'TestRelations',
			'InverseTestRelations'
		);

		$this->assertEquals(count($expectedRelations), count($availableRelations));

		foreach ($expectedRelations as $relationName) {
			$this->assertContains($relationName, $availableRelations);
		}
	}

	public function testGetAvailableRelationsWithAliasesFor() {
		$availableRelations = APIInfo::get_available_relations_with_aliases_for('RestfulServer\StaffTestObject');
		$expectedRelations = array(
			'DirectReports',
			'InverseFriends',
			'Friends'
		);

		$this->assertEquals(count($expectedRelations), count($availableRelations));

		foreach ($expectedRelations as $relationName) {
			$this->assertContains($relationName, $availableRelations);
		}

		$availableRelations = APIInfo::get_available_relations_with_aliases_for('RestfulServer\StaffTestObjectWithAliases');
		$expectedRelations = array(
			'direct-reports',
			'InverseFriends',
			'friends',
			'test-relations',
			'inverse-test-relations'
		);

		$this->assertEquals(count($expectedRelations), count($availableRelations));

		foreach ($expectedRelations as $relationName) {
			$this->assertContains($relationName, $availableRelations);
		}
	}

}