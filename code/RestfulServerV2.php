<?php

class RestfulServerV2 extends Controller {

	public static $default_extension = 'json';

	public static $url_handlers = array(
		'errors/$ErrorID!' => 'showError',
		'errors' => 'listErrors',
		'$ResourceName/$ResourceID!/$RelationName!' => 'listRelations',
		'$ResourceName/$ResourceID!' => 'showResource',
		'$ResourceName!' => 'listResources'
	);

	public static $allowed_actions = array(
		'index',
		'listResources',
		'showResource',
		'listRelations',
		'listErrors',
		'showError'
	);

	private static $valid_formats = array(
		'json' => 'JSONFormatter',
		'xml' => 'XMLFormatter'
	);

	private $formatter = null;

	const MIN_LIMIT      = 1;
	const MAX_LIMIT      = 100;
	const DEFAULT_LIMIT  = 10;
	const DEFAULT_OFFSET = 0;

	public function init() {
		parent::init();

		if ($this->getRequest()->param('ResourceName') === 'errors') {
			return;
		}

		$this->setFormatter();

		if (is_null($this->formatter)) {
			// errors are caused when running unit tests if we don't include $this->popCurrent() here
			// I think it's related to needing to clean up the Controller stack when we kill a request
			// within the init method - doesn't seem to affect normal operation
			$this->popCurrent();
			$this->getResponse()->addHeader('Content-Type', 'text/plain');

			$message = self::get_developer_error_message(
				'invalidFormat',
				array(
					'extension' => $this->getRequest()->getExtension()
				)
			);

			return $this->throwAPIError(400, $message);
		}

		$this->getResponse()->addHeader('Content-Type', $this->formatter->getOutputContentType());
	}

	private function setFormatter() {
		// we only use the URL extension to determine format for the time being
		$extension = $this->getRequest()->getExtension();

		if (!$extension) {
			$extension = self::$default_extension;
		}

		if (!in_array($extension, array_keys(self::$valid_formats))) {
			return null;
		}

		$this->formatter = new self::$valid_formats[$extension]();
	}

	private function throwAPIError($statusCode, $responseBody) {
		$this->getResponse()->setBody($responseBody);
		throw new SS_HTTPResponse_Exception($this->response, $statusCode);
	}

	public function listResources() {
		$className = $this->getClassName();

		$limit  = $this->getResultsLimit();
		$offset = $this->getResultsOffset();

		// very basic method for retrieving records for time being, improve this when adding sorting, pagination, etc.
		$list = $className::get();

		$totalCount = (int) $list->Count();

		if ($offset >= $totalCount) {
			$this->formattedError(400, self::get_error_messages('offsetOutOfBounds'));
		}

		$this->formatter->setExtraData(array(
			'_metadata' => array(
				'totalCount' => $totalCount,
				'limit' => $limit,
				'offset' => $offset
			)
		));

		// default sort until sorting via parameter is implemented
		$list = $list->sort('ID', 'ASC');

		$list = $list->limit($limit, $offset);

		$this->setFormatterItemNames($className);

		$this->formatter->setResultsList($list);

		return $this->formatter->format();
	}

	private function getClassName() {
		$resourceName = $this->getRequest()->param('ResourceName');
		$className = APIInfo::get_class_name_by_resource_name($resourceName);

		if ($className === false) {
			$this->formattedError(
				400,
				self::get_error_messages('resourceNotFound', array('resourceName' => $resourceName))
			);
		}

		return $className;
	}

	private function getResultsLimit() {
		if (!$this->getRequest()->getVar('limit')) {
			return self::DEFAULT_LIMIT;
		}

		$limit = (int) $this->getRequest()->getVar('limit');

		if ($limit < self::MIN_LIMIT || $limit > self::MAX_LIMIT) {
			return self::DEFAULT_LIMIT;
		}

		return $limit;
	}

	private function getResultsOffset() {
		if (!$this->getRequest()->getVar('offset')) {
			return self::DEFAULT_OFFSET;
		}

		$offset = (int) $this->getRequest()->getVar('offset');

		if ($offset < 0) {
			return self::DEFAULT_OFFSET;
		}

		return $offset;
	}

	private function formattedError($statusCode, $data) {
		$this->formatter->setExtraData($data);
		$this->throwAPIError($statusCode, $this->formatter->format());
	}

	private function setFormatterItemNames($className) {
		$apiAccess = singleton($className)->stat('api_access');

		if (isset($apiAccess['singular_name'])) {
			$this->formatter->setSingularItemName($apiAccess['singular_name']);
		}

		if (isset($apiAccess['plural_name'])) {
			$this->formatter->setPluralItemName($apiAccess['plural_name']);
		}
	}

	public function showResource() {
		$className = $this->getClassName();

		$resource = $className::get()->byID((int) $this->getRequest()->param('ResourceID'));

		if (is_null($resource)) {
			$this->formattedError(400, self::get_error_messages('recordNotFound'));
		}

		$this->setFormatterItemNames($className);

		$this->formatter->setResultsItem($resource);

		return $this->formatter->format();
	}

	public function listRelations() {
		$this->formattedError(500, array(
			'developerMessage' => 'Relationship access not yet implemented',
			'userMessage' => 'Something went wrong',
			'moreInfo' => 'coming soon'
		));
	}

	public function listErrors() {
		$errors = sfYaml::load(file_get_contents('../restfulserver/lang/en.yml'));

		Debug::dump($errors);
	}

	public function showError() {

	}

	public function index() {
		$this->formattedError(500, array(
			'developerMessage' => 'Base documentation not yet implemented',
			'userMessage' => 'Something went wrong',
			'moreInfo' => 'coming soon'
		));
	}

	public static function add_format($extension, $formatterClassName) {
		if (!class_exists($formatterClassName)) {
			user_error('Formatter class (' . $formatterClassName . ') not found');
		}

		self::$valid_formats[$extension] = $formatterClassName;
	}

	public static function remove_format($extension) {
		if (isset(self::$valid_formats[$extension])) {
			unset(self::$valid_formats[$extension]);
		}
	}

	public static function get_error_messages($key, $context = array()) {
		if (!self::valid_error_key($key)) {
			return null;
		}

		return array(
			'developerMessage' => self::get_developer_error_message($key, $context),
			'userMessage' => self::get_user_error_message($key, $context),
			'moreInfo' => self::get_more_info_error_message($key, $context)
		);
	}

	private static function valid_error_key($key) {
		$errors = self::config()->get('errors');

		return isset($errors[$key]);
	}

	/**
	 * @param $key string The config key to get the developer error message
	 * @param array $context Array of key->value pairs where key is a placeholder in the error message
	 * and value is the value to replace the placeholder with
	 * @return null|string Returns null on error or the relevant error message on success
	 */
	public static function get_developer_error_message($key, $context = array()) {
		return self::get_error_message('developerMessage', $key, $context);
	}

	public static function get_user_error_message($key, $context = array()) {
		return self::get_error_message('userMessage', $key, $context);
	}

	public static function get_more_info_error_message($key, $context = array()) {
		return self::get_error_message('moreInfo', $key, $context);
	}

	private static function get_error_message($type, $key, $context) {
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
