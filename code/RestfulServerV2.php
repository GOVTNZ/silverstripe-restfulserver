<?php

class RestfulServerV2 extends Controller {

	public static $default_extension = 'json';

	private static $url_handlers = array(
		'errors/$ErrorID!' => 'showError',
		'errors' => 'listErrors',
		'$ResourceName/$ResourceID!/$RelationName!' => 'listRelations',
		'$ResourceName/$ResourceID!' => 'showResource',
		'$ResourceName!' => 'listResources'
	);

	private static $allowed_actions = array(
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

	private static $base_url = null;

	private $formatter = null;

	const MIN_LIMIT      = 1;
	const MAX_LIMIT      = 100;
	const DEFAULT_LIMIT  = 10;
	const DEFAULT_OFFSET = 0;
	const DEFAULT_SORT   = 'ID';
	const DEFAULT_ORDER  = 'ASC';

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

			$message = APIError::get_developer_message_for(
				'invalidFormat',
				array(
					'extension' => $this->getRequest()->getExtension()
				)
			);

			$message .= "\n";
			$message .= APIError::get_more_info_link_for(
				'invalidFormat',
				array(
					'extension' => $this->getRequest()->getExtension()
				)
			);

			return APIError::throw_error(400, $message);
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
		$apiRequest = new APIRequest($this->getRequest(), $this->formatter);
		return $apiRequest->outputResourceList();
	}

	private function formattedError($statusCode, $data) {
		$this->formatter->setExtraData($data);
		return $this->throwAPIError($statusCode, $this->formatter->format());
	}

	public function showResource() {
		$apiRequest = new APIRequest($this->getRequest(), $this->formatter);
		return $apiRequest->outputResourceDetail();
	}

	public function listRelations() {
		$apiRequest = new APIRequest($this->getRequest(), $this->formatter);
		return $apiRequest->outputRelationList();
	}

	public function listErrors() {
		$errors = APIError::config()->get('errors');
		$errorOutput = array();

		foreach ($errors as $key => $error) {
			$temp = array();

			$temp['Name'] = $error['name'];
			$temp['Link'] = APIError::get_more_info_link_for($key);

			$errorOutput[] = $temp;
		}

		return $this->renderWith('ErrorList', array('Errors' => new ArrayList($errorOutput)));
	}

	public function showError() {
		$errorID = $this->getRequest()->param('ErrorID');

		if (!APIError::valid_key($errorID)) {
			$this->getResponse()->setStatusCode(404);
			return 'Error detail not found';
		}

		$context = array();

		if ($this->getRequest()->getVar('context')) {
			$context = json_decode($this->getRequest()->getVar('context'), true);
		}

		return $this->renderWith('ErrorDetail', array(
			'Name' => APIError::get_name($errorID),
			'Description' => APIError::get_description($errorID, $context)
		));
	}

	public function index() {
		return $this->formattedError(500, array(
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

	public static function get_available_formats() {
		return array_keys(self::$valid_formats);
	}

	public static function get_base_url() {
		if (!is_null(self::$base_url)) {
			return self::$base_url;
		}

		$rules = Config::inst()->get('Director', 'rules');

		$matchedRoute = null;

		foreach ($rules as $route => $className) {
			if ($className === __CLASS__) {
				$matchedRoute = $route;
				break;
			}
		}

		if (!is_null($matchedRoute)) {
			$matchedRoute = explode('//', $matchedRoute);
			$matchedRoute = $matchedRoute[0];
		} else {
			$matchedRoute = 'RestfulServerV2';
		}

		self::$base_url = Director::absoluteBaseURL() . $matchedRoute;

		return self::$base_url;
	}

}
