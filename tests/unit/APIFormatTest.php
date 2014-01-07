<?php

class APIFormatTest extends SapphireTest {

	protected static $fixture_file = 'APITestObjects.yml';

	protected $extraDataObjects = array(
		'APITestObject'
	);

	// these tests fail until pagination is implemented

	public function testJSONFormat() {
		$response = Director::test('/api/v2/testobjects');

		$this->assertEquals(200, $response->getStatusCode(), 'Did not receive 200 response');

		$output = json_decode($response->getBody(), true);

		$this->assertInternalType('array', $output);

		$this->assertArrayHasKey('testObjects', $output);
		$this->assertArrayHasKey('_metadata', $output);

		$this->assertEquals(10, count($output['testObjects']));
	}

	public function testXMLFormat() {
		$response = Director::test('/api/v2/testobjects.xml');

		$this->assertEquals(200, $response->getStatusCode(), 'Did not receive 200 response');

		$output = simplexml_load_string($response->getBody());

		$this->assertInstanceOf('SimpleXMLElement', $output);

		$this->assertObjectHasAttribute('testObjects', $output);
		$this->assertObjectHasAttribute('_metadata', $output);

		$this->assertEquals(10, count($output->testObjects->testObject));
	}

	public function testInvalidFormat() {
		$response = Director::test('/api/v2/testobjects.txt');

		$this->assertEquals(400, $response->getStatusCode(), 'Incorrect status code received for invalid format');
		$this->assertEquals(
			_t('RestfulServer.developerMessage.INVALID_FORMAT', '', '', array('extension' => 'txt')),
			$response->getBody(),
			'Incorrect error message received for invalid format'
		);
	}

}