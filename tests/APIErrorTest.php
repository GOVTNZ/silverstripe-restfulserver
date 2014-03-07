<?php

namespace RestfulServer;

use Config, Director, ReflectionMethod, ReflectionProperty, SapphireTest;

class APIErrorTest extends SapphireTest {

	public function testGetMessagesFor() {
		$errors = APIError::get_messages_for('resourceNotFound');

		$this->assertInternalType('array', $errors);
		$this->assertEquals(3, count($errors));

		foreach ($errors as $message) {
			$this->assertInternalType('string', $message);
		}
	}

	public function testGetMessagesForWithInvalidKey() {
		$errors = APIError::get_messages_for('incorrectKey');

		$this->assertNull($errors);
	}

	public function testGetMessagesForWithContext() {
		$errors = APIError::get_messages_for('resourceNotFound', array('resourceName' => 'testResource'));

		$this->assertContains('testResource', $errors['developerMessage']);
	}

	public function testGetMessage() {
		$message = $this->invokeGetMessage('developerMessage', 'resourceNotFound');

		$this->assertInternalType('string', $message);
	}

	private function invokeGetMessage($type, $key, $context = array()) {
		$method = new ReflectionMethod('\RestfulServer\APIError', 'get_message');
		$method->setAccessible(true);

		return $method->invokeArgs(null, array(
			$type,
			$key,
			$context
		));
	}

	public function testGetMessageWithInvalidKey() {
		$message = $this->invokeGetMessage('developerMessage', 'incorrectKey');

		$this->assertNull($message);
	}

	public function testGetMessageWithContext() {
		$message = $this->invokeGetMessage(
			'developerMessage',
			'resourceNotFound',
			array(
				'resourceName' => 'testResource'
			)
		);

		$this->assertContains('testResource', $message);
	}

	public function testGetMoreInfoLink() {
		$link = APIError::get_more_info_link_for('resourceNotFound');
		$expectedLink = Director::absoluteBaseURL() . 'api/v2/errors/resourceNotFound';

		$this->assertEquals($expectedLink, $link);
	}

	public function testGetMoreInfoLinkWithNoRestfulServerV2Route() {
		// clear static base_url value
		$reflectionProperty = new ReflectionProperty('RestfulServer\ControllerV2', 'base_url');
		$reflectionProperty->setAccessible(true);

		$originalBaseURL = $reflectionProperty->getValue();

		$reflectionProperty->setValue(null);

		$originalRules = Config::inst()->get('Director', 'rules');

		// clear routes so that we don't find one for RestfulServerV2
		Config::inst()->remove('Director', 'rules');
		Config::inst()->update('Director', 'rules', array());

		$link = APIError::get_more_info_link_for('resourceNotFound');
		$expectedLink = Director::absoluteBaseURL() . 'RestfulServer\ControllerV2/errors/resourceNotFound';

		$reflectionProperty->setValue($originalBaseURL);
		$reflectionProperty->setAccessible(false);
		Config::inst()->update('Director', 'rules', $originalRules);

		$this->assertEquals($expectedLink, $link);
	}

}