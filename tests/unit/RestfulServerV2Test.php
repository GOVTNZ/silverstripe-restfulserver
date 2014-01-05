<?php

class RestfulServerV2Test extends SapphireTest {

	protected static $fixture_file = 'APITestObjects.yml';

	protected $extraDataObjects = array(
		'APITestObject'
	);

	// these tests fail until pagination is implemented

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
		$this->assertLessThanOrEqual($results->_metadata->limit, $numResults);
	}

}
