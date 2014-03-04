<?php

namespace RestfulServer;

abstract class Exception extends \Exception {

	protected $statusCode;

	private $userMessage;
	private $developerMessage;
	private $moreInfo;

	/**
	 * @param string $errorKey
	 * @param array $context
	 */
	public function __construct($errorKey, $context = array()) {
		$errors = \APIError::get_messages_for($errorKey, $context);

		$this->userMessage = $errors['userMessage'];
		$this->developerMessage = $errors['developerMessage'];
		$this->moreInfo = $errors['moreInfo'];
	}

	/**
	 * @return int
	 */
	public function getStatusCode() {
		return $this->statusCode;
	}

	/**
	 * @return array
	 */
	public function getErrorMessages() {
		return array(
			'userMessage' => $this->userMessage,
			'developerMessage' => $this->developerMessage,
			'moreInfo' => $this->moreInfo
		);
	}

}
