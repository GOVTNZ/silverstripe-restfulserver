<?php

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
		$method = new ReflectionMethod('APIError', 'get_message');
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

}