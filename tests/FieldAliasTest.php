<?php

class FieldAliasTest extends SapphireTest {

	protected static $fixture_file = 'fixtures/FieldAliasTest.yml';

	protected $extraDataObjects = array(
		'StaffTestObjectWithFieldAliases'
	);

	public function testFieldAliases() {
		$response = Director::test('/api/v2/stafftestfieldalias');

		$this->assertEquals(200, $response->getStatusCode());

		$output = json_decode($response->getBody(), true);

		$this->assertArrayHasKey('staff', $output);
		$this->assertArrayHasKey('id', $output['staff'][0]);
		$this->assertArrayHasKey('name', $output['staff'][0]);
		$this->assertArrayHasKey('jobTitle', $output['staff'][0]);
	}

}
