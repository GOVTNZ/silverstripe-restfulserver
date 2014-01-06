<?php

class RestfulServerV2Test extends SapphireTest {

	protected static $fixture_file = 'APITestObjects.yml';

	protected $extraDataObjects = array(
		'APITestObject'
	);

	public function testJSONRequest() {
		$response = Director::test('/api/v2/testobjects.json');

		$this->assertEquals(200, $response->getStatusCode(), 'Incorrect status code returned');

		$results = json_decode($response->getBody(), true);

		$this->assertInternalType('array', $results, 'Results not an array');
		$this->assertArrayHasKey('testObjects', $results, 'Results key is not set');
		$this->assertArrayHasKey('_metadata', $results, 'Metadata key is not set');

		$numResults = count($results['testObjects']);

		$this->assertEquals(10, $numResults);
		$this->assertLessThanOrEqual($results['_metadata']['limit'], $numResults);
	}

	public function testXMLRequest() {
		$response = Director::test('/api/v2/testobjects.xml');

		$this->assertEquals(200, $response->getStatusCode(), 'Incorrect status code returned');

		$results = simplexml_load_string($response->getBody());

		$this->assertInstanceOf('SimpleXMLElement', $results);
		$this->assertObjectHasAttribute('testObjects', $results);
		$this->assertObjectHasAttribute('_metadata', $results);

		$numResults = count($results->testObjects->testObject);

		$this->assertEquals(10, $numResults);
		$this->assertLessThanOrEqual((int) $results->_metadata->limit, $numResults);
	}

	public function testPagination() {
		for ($i = 0; $i < 13; $i += 2) {
			$response = Director::test('/api/v2/testobjects?limit=2&offset=' . $i);

			$this->assertEquals(200, $response->getStatusCode(), 'Incorrect status code returned');

			$body = json_decode($response->getBody(), true);

			if ($i === 12) {
				$expectedResults = 1;
			} else {
				$expectedResults = 2;
			}

			$this->assertEquals($expectedResults, count($body['testObjects']), 'Incorrect number of results returned');
		}
	}

	public function testInvalidPagination() {
		$response = Director::test('/api/v2/testobjects?limit=' . (RestfulServerV2::MAX_LIMIT + 1) . '&offset=0');

		$this->assertEquals(200, $response->getStatusCode(), 'Incorrect status code returned');

		$body = json_decode($response->getBody(), true);

		// when a request exceeds the max limit we use the default limit instead
		$this->assertEquals(RestfulServerV2::DEFAULT_LIMIT, $body['_metadata']['limit'], 'Incorrect limit returned');
	}

	public function testOutOfRangePagination() {
		$response = Director::test('/api/v2/testobjects?limit=10&offset=9999');

		$this->assertEquals(400, $response->getStatusCode(), 'Incorrect status code returned');

		$body = json_decode($response->getBody(), true);

		$this->assertArrayHasKey('developerMessage', $body, 'Developer message not set');
		$this->assertEquals('Query parameter \'offset\' is out of bounds', $body['developerMessage'], 'Incorrect developer message supplied');
	}

}
