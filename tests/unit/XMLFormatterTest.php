<?php

class XMLFormatterTest extends SapphireTest {

	protected static $fixture_file = 'APITestObjects.yml';

	protected $extraDataObjects = array(
		'APITestObject',
		'APITestPageObject'
	);

	public function testExtraData() {
		$formatter = new XMLFormatter();

		$formatter->setExtraData(array(
			'_metadata' => array(
				'totalCount' => 5,
				'limit' => 10,
				'offset' => 0
			)
		));

		$output = simplexml_load_string($formatter->format());

		$this->assertObjectHasAttribute('_metadata', $output, '_metadata attribute missing');

		$this->assertEquals(5, (int) $output->_metadata->totalCount);
		$this->assertEquals(10, (int) $output->_metadata->limit);
		$this->assertEquals(0, (int) $output->_metadata->offset);
	}

	public function testResultData() {
		$testObjects = APITestObject::get();

		$totalCount   = $testObjects->Count();
		$pagedResults = $testObjects->limit(10, 0);

		$formatter = new XMLFormatter();

		$formatter->setResultsList($pagedResults);

		$formatter->setExtraData(array(
			'_metadata' => array(
				'totalCount' => $totalCount,
				'limit' => 10,
				'offset' => 0
			)
		));

		$output = simplexml_load_string($formatter->format());

		$this->assertObjectHasAttribute('items', $output, 'items key missing');
		$this->assertObjectHasAttribute('_metadata', $output, '_metadata key missing');
	}

	public function testErrorFormat() {
		$developerMessage = 'A detailed error message goes here';
		$userMessage      = 'A message for the user goes here';
		$moreInfo         = 'A link to more info';

		$formatter = new XMLFormatter();

		$formatter->setExtraData(array(
			'developerMessage' => $developerMessage,
			'userMessage' => $userMessage,
			'moreInfo' => $moreInfo
		));

		$output = simplexml_load_string($formatter->format());

		$this->assertObjectHasAttribute('developerMessage', $output);
		$this->assertObjectHasAttribute('userMessage', $output);
		$this->assertObjectHasAttribute('moreInfo', $output);

		$this->assertEquals($developerMessage, (string) $output->developerMessage);
		$this->assertEquals($userMessage, (string) $output->userMessage);
		$this->assertEquals($moreInfo, (string) $output->moreInfo);
	}

	public function testResultDataWithPluralItemName() {
		$testObjects = APITestObject::get();

		$totalCount   = $testObjects->Count();
		$pagedResults = $testObjects->limit(10, 0);

		$formatter = new XMLFormatter();

		$formatter->setPluralItemName('results');

		$formatter->setResultsList($pagedResults);

		$formatter->setExtraData(array(
			'_metadata' => array(
				'totalCount' => $totalCount,
				'limit' => 10,
				'offset' => 0
			)
		));

		$output = simplexml_load_string($formatter->format());

		$this->assertObjectHasAttribute('results', $output, 'results key missing');
		$this->assertObjectHasAttribute('_metadata', $output, '_metadata key missing');
	}

}
