<?php

class APIAliasTest extends SapphireTest {

	protected static $fixture_file = 'fixtures/APITestObjects.yml';

	protected $extraDataObjects = array(
		'APITestObject',
		'APITestPageObject'
	);

	public function setUpOnce() {
		$this->cleanAliasCache();

		parent::setUpOnce();
	}

	public function tearDownOnce() {
		$this->cleanAliasCache();

		parent::tearDownOnce();
	}

	private function cleanAliasCache() {
		$aliasCache = SS_Cache::factory(\RestfulServer\APIInfo::RESOURCE_NAME_CACHE_KEY);
		$aliasCache->clean();
	}

	public function testAPIAlias() {
		$aliasURL = '/api/v2/testobjects';
		$defaultURL = '/api/v2/APITestObject';

		$aliasResponse = Director::test($aliasURL, null, null, 'GET');
		$defaultResponse = Director::test($defaultURL, null, null, 'GET');

		$this->assertEquals(200, $aliasResponse->getStatusCode(), 'Did not receive a 200 response on alias URL');

		$this->assertEquals(
			$defaultResponse->getBody(),
			$aliasResponse->getBody(),
			'Alias response did not match default response'
		);
	}

	public function testAPIAliasCache() {
		$aliasURL = '/api/v2/testobjects';

		// grab results twice to confirm cache does not break anything
		Director::test($aliasURL, null, null, 'GET');
		$aliasResponse = Director::test($aliasURL, null, null, 'GET');

		$this->assertEquals(200, $aliasResponse->getStatusCode(), 'Did not receive a 200 response on alias URL');
	}

	public function testInvalidAliasCacheKey() {
		// because we try to load the $ResourceName from the cache, we must ensure it is a valid cache key
		// (and handle any exceptions)
		$badURL = '/api/v2/randomobjects.';

		$badResponse = Director::test($badURL, null, null, 'GET');

		$this->assertEquals(400, $badResponse->getStatusCode(), 'Did not receive a 400 response for bad URL');

		$output = json_decode($badResponse->getBody(), true);

		$this->assertArrayHasKey('developerMessage', $output);
		$this->assertArrayHasKey('userMessage', $output);
		$this->assertArrayHasKey('moreInfo', $output);

		$this->assertEquals(
			\RestfulServer\APIError::get_developer_message_for(
				'resourceNotFound',
				array('resourceName' => 'randomobjects.')
			),
			$output['developerMessage']
		);
	}

}
