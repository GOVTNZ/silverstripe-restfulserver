<?php

class APIAliasTest extends SapphireTest {

	protected static $fixture_file = 'APIAliasTest.yml';

	protected $extraDataObjects = array(
		'APIAliasTestObject'
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
		$aliasCache = SS_Cache::factory(APIInfo::RESOURCE_NAME_CACHE_KEY);
		$aliasCache->clean();
	}

	public function testAPIAlias() {
		$aliasURL = '/api/v2/testobjects';
		$defaultURL = '/api/v2/APIAliasTestObject';

		$aliasResponse = Director::test($aliasURL, null, null, 'GET');
		$defaultResponse = Director::test($defaultURL, null, null, 'GET');

		$this->assertEquals(200, $aliasResponse->getStatusCode(), 'Did not receive a 200 response on alias URL');

		$this->assertEquals($defaultResponse->getBody(), $aliasResponse->getBody(), 'Alias response did not match default response');
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

		$this->assertEquals(404, $badResponse->getStatusCode(), 'Did not receive a 400 response for bad URL');
	}

}

class APIAliasTestObject extends DataObject implements TestOnly {

	private static $db = array(
		'Name' => 'Varchar(255)'
	);

	private static $api_access = array(
		'end_point_alias' => 'testobjects'
	);

}