<?php

class APIAliasTest extends SapphireTest {

	protected $extraDataObjects = array(
		'APIAliasTestObject'
	);

	public function setUpOnce() {
		$this->cleanAliasCache();
	}

	public function tearDownOnce() {
		$this->cleanAliasCache();
	}

	private function cleanAliasCache() {
		$aliasCache = SS_Cache::factory(RestfulServerV2::ALIAS_CACHE_KEY);
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

}

class APIAliasTestObject extends DataObject implements TestOnly {

	private static $api_access = array(
		'end_point_alias' => 'testobjects'
	);

	public static $db = array(
		'Name' => 'Varchar(255)'
	);

}