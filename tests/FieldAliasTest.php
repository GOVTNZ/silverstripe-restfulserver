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
		$this->assertArrayHasKey('jobTitleAlias', $output['staff'][0]);
	}

	public function testFieldAliasesWithSort() {
		$response = Director::test('/api/v2/stafftestfieldalias?sort=jobTitleAlias&order=asc');

		$this->assertEquals(200, $response->getStatusCode());

		$output = json_decode($response->getBody(), true);

		$this->assertEquals('Developer', $output['staff'][0]['jobTitleAlias']);
	}

	public function testFieldAliasesWithPartialResponse() {
		$response = Director::test('/api/v2/stafftestfieldalias?fields=jobTitleAlias');

		$this->assertEquals(200, $response->getStatusCode());

		$output = json_decode($response->getBody(), true);

		$this->assertEquals(2, count($output['staff'][0]));
	}

	public function testFieldAliasesWithFilter() {
		$response = Director::test('/api/v2/stafftestfieldalias?jobTitleAlias=Senior');

		$this->assertEquals(200, $response->getStatusCode());

		$output = json_decode($response->getBody(), true);

		$this->assertEquals(1, count($output['staff']));
	}

}
