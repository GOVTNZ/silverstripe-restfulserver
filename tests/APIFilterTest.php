<?php

class APIFilterTest extends SapphireTest {

	protected $extraDataObjects = array(
		'APITestObject'
	);

	public function testParseGET() {
		$filter = new APIFilter('APITestObject');

		$filterArray = $filter->parseGET(array('Name' => 'test value'));

		$this->assertArrayHasKey('Name:PartialMatch', $filterArray);
		$this->assertEquals('test value', $filterArray['Name:PartialMatch']);
	}

	public function testParseGETWithInvalidFilter() {
		$filter = new APIFilter('APITestObject');

		$exceptionThrown = false;

		try {
			$filterArray = $filter->parseGET(array(
				'Name' => 'test value',
				'InvalidField' => 'another test value'
			));
		} catch (RestfulServer\Exception $exception) {
			$exceptionThrown = true;

			$messages = $exception->getErrorMessages();
			$this->assertContains('InvalidField', $messages['developerMessage']);
		}

		$this->assertTrue($exceptionThrown);
	}

}