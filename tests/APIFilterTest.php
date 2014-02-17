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

		$filterArray = $filter->parseGET(array(
			'Name' => 'test value',
			'InvalidField' => 'another test value'
		));

		$this->assertFalse($filterArray);

		$invalidFields = $filter->getInvalidFields();
		$this->assertContains('InvalidField', $invalidFields);
	}

}