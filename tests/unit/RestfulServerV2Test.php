<?php

class RestfulServerV2Test extends SapphireTest {

	protected static $fixture_file = 'APITestObjects.yml';

	protected $extraDataObjects = array(
		'APITestObject'
	);

	public function testListJSONRequest() {
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

	public function testListXMLRequest() {
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

	public function testShowJSONRequest() {
		$testObject = APITestObject::get()->First();

		$response = Director::test('/api/v2/testobjects/' . $testObject->ID . '.json');

		$this->assertEquals(200, $response->getStatusCode());

		$results = json_decode($response->getBody(), true);

		$this->assertInternalType('array', $results);
		$this->assertArrayHasKey('testObject', $results);

		$this->assertArrayHasKey('ID', $results['testObject']);
		$this->assertArrayHasKey('Name', $results['testObject']);

		$this->assertEquals($testObject->Name, $results['testObject']['Name']);
	}

	public function testShowXMLRequest() {
		$testObject = APITestObject::get()->First();

		$response = Director::test('/api/v2/testobjects/' . $testObject->ID . '.xml');

		$this->assertEquals(200, $response->getStatusCode());

		$results = simplexml_load_string($response->getBody());

		$this->assertInstanceOf('SimpleXMLElement', $results);
		$this->objectHasAttribute('testObject', $results);

		$this->objectHasAttribute('ID', $results->testObject);
		$this->objectHasAttribute('Name', $results->testObject);

		$this->assertEquals($testObject->Name, (string) $results->testObject->Name);
	}

	public function testRecordNotFound() {
		$response = Director::test('/api/v2/testobjects/9999');

		$this->assertEquals(400, $response->getStatusCode());

		$output = json_decode($response->getBody(), true);

		$this->assertArrayHasKey('developerMessage', $output);
		$this->assertArrayHasKey('userMessage', $output);
		$this->assertArrayHasKey('moreInfo', $output);

		$this->assertEquals(
			APIError::get_developer_message_for('recordNotFound'),
			$output['developerMessage']
		);
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

	public function testPaginationInvalidOffset() {
		$response = Director::test('/api/v2/testobjects?offset=-5');

		$output = json_decode($response->getBody(), true);

		$this->assertEquals(RestfulServerV2::DEFAULT_OFFSET, $output['_metadata']['offset']);
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
		$this->assertEquals(
			APIError::get_developer_message_for('offsetOutOfBounds'),
			$body['developerMessage'],
			'Incorrect developer message supplied'
		);
	}

	public function testListErrors() {
		$response = Director::test('/api/v2/errors');

		$this->assertEquals(200, $response->getStatusCode());

		$body = $response->getBody();
		$errors = APIError::config()->get('errors');

		foreach ($errors as $error) {
			$this->assertContains($error['name'], $body);
		}
	}

	public function testShowError() {
		$errors = APIError::config()->get('errors');

		foreach ($errors as $key => $error) {
			$response = Director::test('/api/v2/errors/' . $key);
			$body = $response->getBody();

			$this->assertEquals(200, $response->getStatusCode());
			$this->assertContains(APIError::get_description($key)->forTemplate(), $body);
		}
	}

	public function testShowErrorWithContext() {
		$context = array('resourceName' => 'invalid-resource');

		$response = Director::test(
			'/api/v2/errors/resourceNotFound?' . http_build_query(array('context' => json_encode($context)))
		);

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertContains(APIError::get_description('resourceNotFound', $context)->forTemplate(), $response->getBody());
	}

	public function testShowErrorWithInvalidKey() {
		$response = Director::test('/api/v2/errors/someInvalidKey');

		$this->assertEquals(404, $response->getStatusCode());
		$this->assertEquals('Error detail not found', $response->getBody());
	}

}
