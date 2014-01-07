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

		$expectedResponse = file_get_contents($this->getCurrentAbsolutePath() . '/ExpectedJSONResponse.json');
		$this->assertEquals(trim($expectedResponse), trim($response->getBody()), 'Did not receive expected response');
	}

	public function testXMLFormat() {
		$response = Director::test('/api/v2/testobjects.xml');

		$this->assertEquals(200, $response->getStatusCode(), 'Did not receive 200 response');

		$expectedResponse = file_get_contents($this->getCurrentAbsolutePath() . '/ExpectedXMLResponse.xml');
		$this->assertEquals(trim($expectedResponse), trim($response->getBody()), 'Did not receive expected response');
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