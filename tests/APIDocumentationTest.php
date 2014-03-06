<?php

class APIDocumentationTest extends BaseRestfulServerTest {

	protected $extraDataObjects = array(
		'APITestObject',
		'APITestPageObject',
		'StaffTestObject',
		'StaffTestObjectWithFieldAliases'
	);

	public function testBaseDocumentation() {
		$response = Director::test('/api/v2');

		$this->assertEquals(200, $response->getStatusCode());

		$body = $response->getBody();

		$this->assertContains('List of end points', $body);

		$this->assertContains('/api/v2/testobjects', $body);
		$this->assertContains('/api/v2/testpages', $body);
		$this->assertContains('/api/v2/stafftest', $body);
		$this->assertContains('/api/v2/stafftestfieldalias', $body);

		$this->assertContains('test object description', $body);

		$this->assertContains('Available formats', $body);

		foreach (RestfulServer\ControllerV2::get_available_formats() as $format) {
			$this->assertContains('<li>' . $format . '</li>', $body);
		}
	}

	public function testListDocumentation() {
		$response = Director::test('/api/v2/stafftest.html');

		$this->assertEquals(200, $response->getStatusCode());
	}
}
