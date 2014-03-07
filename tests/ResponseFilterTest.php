<?php

namespace RestfulServer;

use SapphireTest;

class ResponseFilterTest extends SapphireTest {

	protected $extraDataObjects = array(
		'RestfulServer\APITestObject'
	);

	public function testParseGET() {
		$filter = new ResponseFilter('RestfulServer\APITestObject');

		$filterArray = $filter->parseGET(array('Name' => 'test value'));

		$this->assertArrayHasKey('Name:PartialMatch', $filterArray);
		$this->assertEquals('test value', $filterArray['Name:PartialMatch']);
	}

	public function testParseGETWithInvalidFilter() {
		$filter = new ResponseFilter('RestfulServer\APITestObject');

		$exceptionThrown = false;

		try {
			$filter->parseGET(array(
				'Name' => 'test value',
				'InvalidField' => 'another test value'
			));
		} catch (Exception $exception) {
			$exceptionThrown = true;

			$messages = $exception->getErrorMessages();
			$this->assertContains('InvalidField', $messages['developerMessage']);
		}

		$this->assertTrue($exceptionThrown);
	}

}