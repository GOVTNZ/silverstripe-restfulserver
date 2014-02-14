<?php

class APIError extends Object {

	public static function throw_formatted_error($formatter, $statusCode, $errorKey, $context = array()) {
		$formatter->setExtraData(self::get_messages_for($errorKey, $context));
		return self::throw_error($statusCode, $formatter->format(), $formatter->getOutputContentType());
	}

	public static function throw_error($statusCode, $message, $contentType = 'text/plain') {
		$response = new SS_HTTPResponse();
		$response->addHeader('Content-Type', $contentType);
		$response->setBody($message);
		throw new SS_HTTPResponse_Exception($response, $statusCode);
	}

	/**
	 * @param $key Error message key as in _config/error-definitions.yml (e.g. resourceNotFound)
	 * @param array $context map of placeholders to values that should be replaced in the message
	 * @return array|null Returns null on invalid key or an array containing all relevant error messages otherwise
	 */
	public static function get_messages_for($key, $context = array()) {
		if (!self::valid_key($key)) {
			return null;
		}

		return array(
			'developerMessage' => self::get_developer_message_for($key, $context),
			'userMessage' => self::get_user_message_for($key, $context),
			'moreInfo' => self::get_more_info_link_for($key, $context)
		);
	}

	public static function valid_key($key) {
		$errors = self::config()->get('errors');

		return isset($errors[$key]);
	}

	/**
	 * @param $key string The config key to get the developer error message
	 * @param array $context Array of key->value pairs where key is a placeholder in the error message
	 * and value is the value to replace the placeholder with
	 * @return null|string Returns null on error or the relevant error message on success
	 */
	public static function get_developer_message_for($key, $context = array()) {
		return self::get_message('developerMessage', $key, $context);
	}

	/**
	 * @param $key string The config key to get the user error message
	 * @param array $context Array of key->value pairs where key is a placeholder in the error message
	 * and value is the value to replace the placeholder with
	 * @return null|string Returns null on error or the relevant error message on success
	 */
	public static function get_user_message_for($key, $context = array()) {
		return self::get_message('userMessage', $key, $context);
	}

	/**
	 * @param $key string The config key to get the more info message
	 * @return null|string Returns null on invalid key or a link to error information on success
	 */
	public static function get_more_info_link_for($key, $context = array()) {
		if (!self::valid_key($key)) {
			return null;
		}

		$apiBaseURL = RestfulServerV2::get_base_url();

		$errorsURL = $apiBaseURL . '/errors';
		$errorURL = $errorsURL . '/' . $key;

		$queryString = '';

		if (count($context) > 0) {
			$queryString = '?' . http_build_query(array('context' => json_encode($context)));
		}

		return $errorURL . $queryString;
	}

	/**
	 * Get the human readable name for this error
	 *
	 * @param $key string The error key to look up
	 * @return string|null Returns null on invalid key or the relevant name on success
	 */
	public static function get_name($key) {
		return self::get_message('name', $key);
	}


	/**
	 * Get the full description for this error
	 *
	 * @param $key string The error key to look up
	 * @return HTMLText|null Returns null on invalid key or the relevant description (inside HTMLText) on success
	 */
	public static function get_description($key, $context = array()) {
		$descriptionText = self::get_message('description', $key);

		$viewer = new SSViewer_FromString($descriptionText);

		$description = new HTMLText();
		$description->setValue(Controller::curr()->renderWith($viewer, $context));

		return $description;
	}

	private static function get_message($type, $key, $context = array()) {
		$errors = self::config()->get('errors');

		if (!isset($errors[$key]) || !isset($errors[$key][$type])) {
			return null;
		}

		$message = $errors[$key][$type];

		if (count($context) === 0) {
			return $message;
		}

		foreach ($context as $placeholder => $value) {
			$placeholder = '{' . $placeholder . '}';
			$message = str_replace($placeholder, $value, $message);
		}

		return $message;
	}

}
