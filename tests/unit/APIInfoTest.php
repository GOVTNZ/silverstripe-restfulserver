<?php

class APIInfoTest extends SapphireTest {

	protected $extraDataObjects = array(
		'APITestObject'
	);

	public function testClassCanBeFilteredBy() {
		$result = APIInfo::class_can_be_filtered_by('APITestObject', 'Name');

		$this->assertTrue($result);
	}

	public function testClassCanBeFilteredByWithInvalidFieldName() {
		$result = APIInfo::class_can_be_filtered_by('APITestObject', 'NonExistentField');

		$this->assertFalse($result);
	}

}