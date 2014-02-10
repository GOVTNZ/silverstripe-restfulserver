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

	public function testGetRelationListWithNonExistentResourceID() {
		$fixtureIDs = $this->allFixtureIDs('StaffTestObject');

		$unusedID = 1;

		while (true) {
			if (!in_array($unusedID, $fixtureIDs)) {
				break;
			} else {
				$unusedID += 1;
			}
		}

		$response = Director::test('/api/v2/staff/' . $unusedID . '/direct-reports');

		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testGetRelationListWithNonExistentRelation() {
		$managerId = $this->idFromFixture('StaffTestObject', 'one');

		$response = Director::test('/api/v2/staff/' . $managerId . '/non-existent-relation');

		$this->assertEquals(400, $response->getStatusCode());
	}

}
