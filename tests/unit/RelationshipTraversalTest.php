<?php

class RelationshipTraversalTest extends SapphireTest {

	protected static $fixture_file = 'RelationshipTraversalTest.yml';

	protected $extraDataObjects = array(
		'StaffTestObject'
	);

	public function testGetRelationList() {
		$managerId = $this->idFromFixture('StaffTestObject', 'one');

		$response = Director::test('/api/v2/staff/' . $managerId . '/direct-reports');

		$this->assertEquals(200, $response->getStatusCode());

		$results = json_decode($response->getBody(), true);

		$this->assertArrayHasKey('staff', $results);
		$this->assertEquals(2, count($results['staff']));
	}

	public function testGetRelationListWithNoResults() {
		$staffMemberId = $this->idFromFixture('StaffTestObject', 'two');

		$response = Director::test('/api/v2/staff/' . $staffMemberId . '/direct-reports');

		$this->assertEquals(200, $response->getStatusCode());

		$results = json_decode($response->getBody(), true);

		$this->assertArrayHasKey('staff', $results);
		$this->assertEquals(0, count($results['staff']));
	}

	public function testGetRelationListWithNonExistentRelation() {
		$managerId = $this->idFromFixture('StaffTestObject', 'one');

		$response = Director::test('/api/v2/staff/' . $managerId . '/non-existent-relation');

		$this->assertEquals(400, $response->getStatusCode());
	}

}
