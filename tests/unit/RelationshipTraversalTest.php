<?php

class RelationshipTraversalTest extends SapphireTest {

	protected static $fixture_file = 'RelationshipTraversalTest.yml';

	protected $extraDataObjects = array(
		'StaffTestObject'
	);

	public function testGetRelationList() {
		$managerID = $this->idFromFixture('StaffTestObject', 'one');

		$response = Director::test('/api/v2/stafftest/' . $managerID . '/direct-reports');

		$this->assertEquals(200, $response->getStatusCode());

		$results = json_decode($response->getBody(), true);

		$this->assertArrayHasKey('staff', $results);
		$this->assertEquals(2, count($results['staff']));
	}

	public function testGetRelationListWithNoResults() {
		$staffMemberId = $this->idFromFixture('StaffTestObject', 'two');

		$response = Director::test('/api/v2/stafftest/' . $staffMemberId . '/direct-reports');

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

		$response = Director::test('/api/v2/stafftest/' . $unusedID . '/direct-reports');

		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testGetRelationListWithNonExistentRelation() {
		$managerId = $this->idFromFixture('StaffTestObject', 'one');

		$response = Director::test('/api/v2/stafftest/' . $managerId . '/non-existent-relation');

		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testGetRelationListWithManyManyRelation() {
		$staffID = $this->idFromFixture('StaffTestObject', 'one');

		$response = Director::test('/api/v2/stafftest/' . $staffID . '/friends');

		$this->assertEquals(200, $response->getStatusCode());
	}

	public function testGetRelationListWithFilter() {
		$managerId = $this->idFromFixture('StaffTestObject', 'one');

		$response = Director::test('/api/v2/stafftest/' . $managerId . '/direct-reports?Name=bob');

		$this->assertEquals(200, $response->getStatusCode());

		$results = json_decode($response->getBody(), true);

		$this->assertEquals(1, count($results['staff']));
	}

	public function testGetRelationListWithSort() {
		$managerId = $this->idFromFixture('StaffTestObject', 'one');

		$response = Director::test('/api/v2/stafftest/' . $managerId . '/direct-reports?sort=Name&order=asc');

		$this->assertEquals(200, $response->getStatusCode());

		$results = json_decode($response->getBody(), true);

		$this->assertEquals('Bob Jones', $results['staff'][0]['Name']);
		$this->assertEquals('John Smith', $results['staff'][1]['Name']);
	}

}
