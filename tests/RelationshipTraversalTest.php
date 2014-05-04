<?php

namespace RestfulServer;

use Director, SapphireTest;

class RelationshipTraversalTest extends SapphireTest {

	protected static $fixture_file = 'fixtures/RelationshipTraversalTest.yml';

	protected $extraDataObjects = array(
		'RestfulServer\StaffTestObject',
		'RestfulServer\StaffTestObjectWithAliases',
		'RestfulServer\InaccessibleDataObject'
	);

	public function testGetRelationList() {
		$managerID = $this->idFromFixture('RestfulServer\StaffTestObjectWithAliases', 'one');

		$response = Director::test('/api/v2/stafftestalias/' . $managerID . '/direct-reports');

		$this->assertEquals(200, $response->getStatusCode());

		$results = json_decode($response->getBody(), true);

		$this->assertArrayHasKey('staff', $results);
		$this->assertEquals(2, count($results['staff']));

		$this->assertArrayHasKey('_metadata', $results);
		$this->assertEquals(2, $results['_metadata']['totalCount']);
		$this->assertEquals(ControllerV2::DEFAULT_OFFSET, $results['_metadata']['offset']);
		$this->assertEquals(ControllerV2::DEFAULT_LIMIT, $results['_metadata']['limit']);
	}

	public function testGetRelationListWithNoResults() {
		$staffMemberId = $this->idFromFixture('RestfulServer\StaffTestObjectWithAliases', 'two');

		$response = Director::test('/api/v2/stafftestalias/' . $staffMemberId . '/direct-reports');

		$this->assertEquals(200, $response->getStatusCode());

		$results = json_decode($response->getBody(), true);

		$this->assertArrayHasKey('staff', $results);
		$this->assertEquals(0, count($results['staff']));
	}

	public function testGetRelationListWithNonExistentResourceID() {
		$fixtureIDs = $this->allFixtureIDs('RestfulServer\StaffTestObjectWithAliases');

		$unusedID = 1;

		while (true) {
			if (!in_array($unusedID, $fixtureIDs)) {
				break;
			} else {
				$unusedID += 1;
			}
		}

		$response = Director::test('/api/v2/stafftestalias/' . $unusedID . '/direct-reports');

		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testGetRelationListWithNonExistentRelation() {
		$managerId = $this->idFromFixture('RestfulServer\StaffTestObject', 'one');

		$response = Director::test('/api/v2/stafftest/' . $managerId . '/non-existent-relation');

		$results = json_decode($response->getBody(), true);

		$this->assertEquals(400, $response->getStatusCode());
		$this->assertEquals(
			APIError::get_developer_message_for('relationNotFound', array('relation' => 'non-existent-relation')),
			$results['developerMessage']
		);
	}

	public function testGetRelationListWithManyManyRelation() {
		$staffID = $this->idFromFixture('RestfulServer\StaffTestObjectWithAliases', 'one');

		$response = Director::test('/api/v2/stafftestalias/' . $staffID . '/friends');

		$this->assertEquals(200, $response->getStatusCode());
	}

	public function testGetRelationListWithFilter() {
		$managerId = $this->idFromFixture('RestfulServer\StaffTestObjectWithAliases', 'one');

		$response = Director::test('/api/v2/stafftestalias/' . $managerId . '/direct-reports?name=bob');

		$this->assertEquals(200, $response->getStatusCode());

		$results = json_decode($response->getBody(), true);

		$this->assertEquals(1, count($results['staff']));
	}

	public function testGetRelationListWithSort() {
		$managerId = $this->idFromFixture('RestfulServer\StaffTestObjectWithAliases', 'one');

		$response = Director::test('/api/v2/stafftestalias/' . $managerId . '/direct-reports?sort=Name&order=asc');

		$this->assertEquals(200, $response->getStatusCode());

		$results = json_decode($response->getBody(), true);

		$this->assertEquals('Bob Jones', $results['staff'][0]['name']);
		$this->assertEquals('John Smith', $results['staff'][1]['name']);
	}

	public function testGetRelationListWithPagination() {
		$managerId = $this->idFromFixture('RestfulServer\StaffTestObjectWithAliases', 'one');

		$response = Director::test('/api/v2/stafftestalias/' . $managerId . '/direct-reports?limit=1&offset=1');

		$this->assertEquals(200, $response->getStatusCode());

		$results = json_decode($response->getBody(), true);

		$this->assertEquals(1, count($results['staff']));
		$this->assertEquals('Bob Jones', $results['staff'][0]['name']);
	}

	public function testInaccessibleRelation() {
		$managerId = $this->idFromFixture('RestfulServer\StaffTestObject', 'one');

		$response = Director::test('/api/v2/stafftest/' . $managerId . '/InaccessibleDataObjects');

		$this->assertEquals(400, $response->getStatusCode());

		$responseJSON = json_decode($response->getBody(), true);

		$expectedError = APIError::get_messages_for('relationNotFound', array(
			'relation' => 'InaccessibleDataObjects'
		));

		$this->assertEquals($expectedError['developerMessage'], $responseJSON['developerMessage']);
		$this->assertEquals($expectedError['userMessage'], $responseJSON['userMessage']);
		$this->assertEquals($expectedError['moreInfo'], $responseJSON['moreInfo']);
	}

	public function testInaccessibleRelationWithAliases() {
		$managerId = $this->idFromFixture('RestfulServer\StaffTestObjectWithAliases', 'one');

		$response = Director::test('/api/v2/stafftestalias/' . $managerId . '/inaccessible-relation');

		$this->assertEquals(400, $response->getStatusCode());

		$responseJSON = json_decode($response->getBody(), true);

		$expectedError = APIError::get_messages_for('relationNotFound', array(
			'relation' => 'inaccessible-relation'
		));

		$this->assertEquals($expectedError['developerMessage'], $responseJSON['developerMessage']);
		$this->assertEquals($expectedError['userMessage'], $responseJSON['userMessage']);
		$this->assertEquals($expectedError['moreInfo'], $responseJSON['moreInfo']);
	}

	public function testDynamicRelation() {
		$managerId = $this->idFromFixture('RestfulServer\StaffTestObject', 'one');

		$response = Director::test('/api/v2/stafftest/' . $managerId . '/AllStaff');

		$this->assertEquals(200, $response->getStatusCode());

		$results = json_decode($response->getBody(), true);

		$this->assertEquals(
			count($this->allFixtureIDs('RestfulServer\StaffTestObject')),
			count($results['staff'])
		);
	}

	public function testDynamicRelationWithAliases() {
		$managerId = $this->idFromFixture('RestfulServer\StaffTestObjectWithAliases', 'one');

		$response = Director::test('/api/v2/stafftestalias/' . $managerId . '/all-staff');

		$this->assertEquals(200, $response->getStatusCode());

		$results = json_decode($response->getBody(), true);

		$this->assertEquals(
			count($this->allFixtureIDs('RestfulServer\StaffTestObjectWithAliases')),
			count($results['staff'])
		);
	}

}
