<?php

class APIFormatTest extends SapphireTest {

	protected static $fixture_file = 'APITestObjects.yml';

	protected $extraDataObjects = array(
		'APITestObject'
	);

	public function testJSONFormat() {
		$response = Director::test('/api/v2/testobjects');

		$this->assertEquals(200, $response->getStatusCode(), 'Did not receive 200 response');

		$expectedResponse = file_get_contents('../restfulserver/tests/unit/ExpectedJSONResponse.json');
		$this->assertEquals(trim($expectedResponse), trim($response->getBody()), 'Did not receive expected response');
	}

	public function testXMLFormat() {
		$response = Director::test('/api/v2/testobjects.xml');

		$this->assertEquals(200, $response->getStatusCode(), 'Did not receive 200 response');

		$expectedResponse = file_get_contents('../restfulserver/tests/unit/ExpectedXMLResponse.xml');
		$this->assertEquals(trim($expectedResponse), trim($response->getBody()), 'Did not receive expected response');
	}

}