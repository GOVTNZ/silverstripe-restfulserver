<?php

class JSONFormatterTest extends SapphireTest {

	protected static $fixture_file = 'APITestObjects.yml';

	protected $extraDataObjects = array(
		'APITestObject'
	);

	public function testExtraData() {
		$formatter = new JSONFormatter();

		$formatter->setExtraData(array(
			'_metadata' => array(
				'totalCount' => 5,
				'limit' => 10,
				'offset' => 0
			)
		));

		$output = json_decode($formatter->format(), true);

		$this->assertArrayHasKey('_metadata', $output, '_metadata key missing');

		$this->assertEquals(5, $output['_metadata']['totalCount']);
		$this->assertEquals(10, $output['_metadata']['limit']);
		$this->assertEquals(0, $output['_metadata']['offset']);
	}

	public function testResultData() {
		$testObjects = APITestObject::get();

		$totalCount   = $testObjects->Count();
		$pagedResults = $testObjects->limit(10, 0);

		$formatter = new JSONFormatter();

		$formatter->setResultsList($pagedResults);

		$formatter->setExtraData(array(
			'_metadata' => array(
				'totalCount' => $totalCount,
				'limit' => 10,
				'offset' => 0
			)
		));

		$output = json_decode($formatter->format(), true);

		$this->assertArrayHasKey('items', $output, 'items key missing');
		$this->assertArrayHasKey('_metadata', $output, '_metadata key missing');
	}

	public function testErrorFormat() {
		$developerMessage = 'A detailed error message goes here';
		$userMessage      = 'A message for the user goes here';
		$moreInfo         = 'A link to more info';

		$formatter = new JSONFormatter();

		$formatter->setExtraData(array(
			'developerMessage' => $developerMessage,
			'userMessage' => $userMessage,
			'moreInfo' => $moreInfo
		));

		$output = json_decode($formatter->format(), true);

		$this->assertArrayHasKey('developerMessage', $output);
		$this->assertArrayHasKey('userMessage', $output);
		$this->assertArrayHasKey('moreInfo', $output);

		$this->assertEquals($developerMessage, $output['developerMessage']);
		$this->assertEquals($userMessage, $output['userMessage']);
		$this->assertEquals($moreInfo, $output['moreInfo']);
	}

	public function testResultDataWithPluralItemName() {
		$testObjects = APITestObject::get();

		$totalCount   = $testObjects->Count();
		$pagedResults = $testObjects->limit(10, 0);

		$formatter = new JSONFormatter();

		$formatter->setPluralItemName('results');

		$formatter->setResultsList($pagedResults);

		$formatter->setExtraData(array(
			'_metadata' => array(
				'totalCount' => $totalCount,
				'limit' => 10,
				'offset' => 0
			)
		));

		$output = json_decode($formatter->format(), true);

		$this->assertArrayHasKey('results', $output, 'results key missing');
		$this->assertArrayHasKey('_metadata', $output, '_metadata key missing');
	}

}