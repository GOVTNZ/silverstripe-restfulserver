<?php

class APIAliasTest extends SapphireTest {

	protected $extraDataObjects = array(
		'APIAliasTestObject'
	);

	public function testAPIAlias() {
		$aliasURL = '/api/v2/testobjects';
		$defaultURL = '/api/v2/APIAliasTestObject';

		$aliasResponse = Director::test($aliasURL, null, null, 'GET');
		$defaultResponse = Director::test($defaultURL, null, null, 'GET');

		$this->assertEquals(200, $aliasResponse->getStatusCode(), 'Did not receive a 200 response on alias URL');

		$this->assertEquals($defaultResponse, $aliasResponse, 'Alias response did not match default response');
	}

}

class APIAliasTestObject extends DataObject implements TestOnly {

	private static $api_access = array(
		'alias' => 'testobjects'
	);

	public static $db = array(
		'Name' => 'Varchar(255)'
	);

}