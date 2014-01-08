<?php

class APIError extends Object {

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
			'moreInfo' => self::get_more_info_message_for($key, $context)
		);
	}

	private static function valid_key($key) {
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
	 * @param array $context Array of key->value pairs where key is a placeholder in the error message
	 * and value is the value to replace the placeholder with
	 * @return null|string Returns null on error or the relevant error message on success
	 */
	public static function get_more_info_message_for($key, $context = array()) {
		return self::get_message('moreInfo', $key, $context);
	}

	private static function get_message($type, $key, $context) {
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
