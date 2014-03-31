<?php

namespace RestfulServer;

use Director;

class APIDocumentationTest extends BaseRestfulServerTest {

	protected $extraDataObjects = array(
		'RestfulServer\APITestObject',
		'RestfulServer\APITestPageObject',
		'RestfulServer\StaffTestObject',
		'RestfulServer\StaffTestObjectWithAliases'
	);

	public function testBaseDocumentation() {
		$response = Director::test('/api/v2');

		$this->assertEquals(200, $response->getStatusCode());

		$body = $response->getBody();

		$this->assertContains('List of end points', $body);

		$this->assertContains('/api/v2/testobjects', $body);
		$this->assertContains('/api/v2/testpages', $body);
		$this->assertContains('/api/v2/stafftest', $body);
		$this->assertContains('/api/v2/stafftestalias', $body);

		$this->assertContains('test object description', $body);

		$this->assertContains('Available formats', $body);

		foreach (ControllerV2::get_available_formats() as $format) {
			$this->assertContains('<li>' . $format . '</li>', $body);
		}
	}

	public function testListDocumentation() {
		$response = Director::test('/api/v2/stafftest.html');

		$this->assertEquals(200, $response->getStatusCode());

		$body = $response->getBody();

		$this->assertContains('Available fields', $body);
		$this->assertContains('Name', $body);
		$this->assertContains('JobTitle', $body);

		$this->assertContains('Relations', $body);
		$this->assertContains('DirectReports', $body);
		$this->assertContains('Friends', $body);
		$this->assertContains('InverseFriends', $body);
	}
}
