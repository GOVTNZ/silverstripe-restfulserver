<?php

class PartialResponseTest extends SapphireTest {

	protected static $fixture_file = 'fixtures/PartialResponseTest.yml';

	protected $extraDataObjects = array(
		'StaffTestObject'
	);

	public function testPartialResponse() {
		$response = Director::test('/api/v2/stafftest?fields=Name');

		$this->assertEquals(200, $response->getStatusCode());

		$results = json_decode($response->getBody(), true);

		$this->assertEquals(3, count($results['staff']));
		$this->assertEquals(2, count($results['staff'][0]));
		$this->assertArrayHasKey('Name', $results['staff'][0]);
		$this->assertArrayHasKey('ID', $results['staff'][0]);
	}

	public function testPartialResponseWithInvalidField() {
		$response = Director::test('/api/v2/stafftest?fields=Name,InvalidField');

		$this->assertEquals(400, $response->getStatusCode());

		$results = json_decode($response->getBody(), true);

		$this->assertEquals(
			APIError::get_developer_message_for('invalidField', array('fields' => 'InvalidField')),
			$results['developerMessage']
		);
	}

}
