<?php

namespace RestfulServer;

use Director, SapphireTest;

class FieldAliasTest extends SapphireTest {

	protected static $fixture_file = 'fixtures/FieldAliasTest.yml';

	protected $extraDataObjects = array(
		'RestfulServer\StaffTestObjectWithAliases'
	);

	public function testFieldAliases() {
		$response = Director::test('/api/v2/stafftestalias');

		$this->assertEquals(200, $response->getStatusCode());

		$output = json_decode($response->getBody(), true);

		$this->assertArrayHasKey('staff', $output);
		$this->assertArrayHasKey('id', $output['staff'][0]);
		$this->assertArrayHasKey('name', $output['staff'][0]);
		$this->assertArrayHasKey('jobTitleAlias', $output['staff'][0]);
	}

	public function testFieldAliasesWithSort() {
		$response = Director::test('/api/v2/stafftestalias?sort=jobTitleAlias&order=asc');

		$this->assertEquals(200, $response->getStatusCode());

		$output = json_decode($response->getBody(), true);

		$this->assertEquals('Developer', $output['staff'][0]['jobTitleAlias']);
	}

	public function testFieldAliasesWithPartialResponse() {
		$response = Director::test('/api/v2/stafftestalias?fields=jobTitleAlias');

		$this->assertEquals(200, $response->getStatusCode());

		$output = json_decode($response->getBody(), true);

		$this->assertEquals(2, count($output['staff'][0]));
	}

	public function testFieldAliasesWithFilter() {
		$response = Director::test('/api/v2/stafftestalias?jobTitleAlias=Senior');

		$this->assertEquals(200, $response->getStatusCode());

		$output = json_decode($response->getBody(), true);

		$this->assertEquals(1, count($output['staff']));
	}

}
