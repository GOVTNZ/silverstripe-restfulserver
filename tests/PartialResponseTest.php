<?php

class PartialResponseTest extends SapphireTest {

	protected static $fixture_file = 'fixtures/PartialResponseTest.yml';

	protected $extraDataObjects = array(
		'StaffTestObject'
	);

	public function testPartialResponse() {
		$response = Director::test('/api/v2/stafftest?fields=Name');

		$this->assertEquals(200, $response->getStatusCode());
	}

}
