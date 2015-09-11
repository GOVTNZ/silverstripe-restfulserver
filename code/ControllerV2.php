<?php

namespace RestfulServer;

use Controller, ArrayList, ArrayData, Director, SS_HTTPResponse, SS_HTTPResponse_Exception, Config;

class ControllerV2 extends Controller {
	/**
	 * @var string
	 */
	public static $default_extension = 'json';

	/**
	 * @var array
	 */
	private static $url_handlers = array(
		'errors/$ErrorID!' => 'showError',
		'errors' => 'listErrors',
		'$ResourceName/$ResourceID!/$RelationName!' => 'listRelations',
		'$ResourceName/$ResourceID!' => 'showResource',
		'$ResourceName!' => 'listResources'
	);

	/**
	 * @var array
	 */
	private static $allowed_actions = array(
		'index',
		'listResources',
		'showResource',
		'listRelations',
		'listErrors',
		'showError'
	);

	/**
	 * @var array
	 */
	private static $valid_formats = array(
		'json' => '\\RestfulServer\\JSONFormatter',
		'xml' => '\\RestfulServer\\XMLFormatter',
		'html' => '\\RestfulServer\\DocumentationFormatter'
	);

	private static $base_url = null;

	/**
	 * @var Formatter
	 */
	private $formatter = null;

	/**
	 * @var Request
	 */
	private $apiRequest = null;

	/**
	 * @var RequestLogger
	 */
	private static $logger = null;

	public $templateRenderer;

	const MIN_LIMIT      = 1;
	const MAX_LIMIT      = 100;
	const DEFAULT_LIMIT  = 10;
	const DEFAULT_OFFSET = 0;
	const DEFAULT_SORT   = 'ID';
	const DEFAULT_ORDER  = 'ASC';

	/**
	 * run all initial steps like set the the templateRenderer, logger etc.
	 */
	public function init() {
		parent::init();

		/*
		 * Use ContentController if it is available as it provides a better output (default values like
		 * $SiteConfig.Title become available) but fall back to ViewableData if cms module is not installed.
		 */
		if (class_exists('ContentController')) {
			$this->templateRenderer = new \ContentController();
		} else {
			$this->templateRenderer = new \ViewableData();
		}

		if ($this->getRequest()->param('ResourceName') === 'errors') {
			return;
		}

		if (is_null(self::$logger)) {
			self::$logger = new NullRequestLogger();
		}

		self::$logger->log($this->getRequest());

		$this->setFormatter();
		$this->setAPIRequest();

		$this->getResponse()->addHeader('Content-Type', $this->formatter->getOutputContentType());
	}

	/**
	 * sets the formatter based on the extension provided in the request
	 */
	private function setFormatter() {
		// we only use the URL extension to determine format for the time being
		$extension = $this->getRequest()->getExtension();

		if (!$extension) {
			$extension = self::$default_extension;
		}

		if (!in_array($extension, array_keys(self::$valid_formats))) {
			return $this->formatterError();
		}

		$this->formatter = new self::$valid_formats[$extension]();
	}

	private function formatterError() {
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

	private function setAPIRequest() {
		if ($this->formatter instanceof DocumentationFormatter) {
			$this->apiRequest = new DocumentationRequest($this->getRequest(), $this->formatter);
		} else if ($this->getRequest()->isGET()) {
			$this->apiRequest = new GETRequest($this->getRequest(), $this->formatter);
		}
	}

	public function listResources() {
		try {
			return $this->apiRequest->outputResourceList();
		} catch (Exception $exception) {
			return $this->throwFormattedAPIError($exception);
		}
	}

	private function throwFormattedAPIError(Exception $exception) {
		$this->formatter->clearData();
		$this->formatter->addExtraData($exception->getErrorMessages());

		$response = new SS_HTTPResponse();

		$response->setStatusCode($exception->getStatusCode());
		$response->addHeader('Content-Type', $this->formatter->getOutputContentType());
		$response->setBody($this->formatter->format());

		return $this->throwAPIError($response);
	}

	private function throwAPIError($response) {
		throw new SS_HTTPResponse_Exception($response, $response->getStatusCode());
	}

	public function showResource() {
		try {
			return $this->apiRequest->outputResourceDetail();
		} catch (Exception $exception) {
			return $this->throwFormattedAPIError($exception);
		}
	}

	public function listRelations() {
		try {
			return $this->apiRequest->outputRelationList();
		} catch (Exception $exception) {
			return $this->throwFormattedAPIError($exception);
		}
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

		return $this->templateRenderer->customise(array(
			'Title' => 'API error definitions',
			'Errors' => new ArrayList($errorOutput)
		))->renderWith(array('ErrorList', 'Page'));
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

		return $this->templateRenderer->customise(array(
			'Title' => 'API error (' . APIError::get_name($errorID) . ')',
			'Name' => APIError::get_name($errorID),
			'Description' => APIError::get_description($errorID, $context)
		))->renderWith(array('ErrorDetail', 'Page'));
	}

	public function index() {
		$this->getResponse()->addHeader('Content-Type', 'text/html');

		$endPointClassMap = APIInfo::get_all_end_points();

		$endPoints = new ArrayList();

		foreach ($endPointClassMap as $className => $endPoint) {
			$endPoints->push(new ArrayData(array(
				'Name' => $endPoint,
				'Link' => self::get_base_url() . '/' . $endPoint . '.html',
				'Description' => $this->getEndPointDescription($className)
			)));
		}

		$formats = new ArrayList();

		foreach (self::get_available_formats() as $format) {
			$formats->push(array(
				'Extension' => $format
			));
		}

		return $this->templateRenderer->customise(array(
			'Title' => 'API Documentation',
			'APIBaseURL' => ControllerV2::get_base_url(),
			'EndPoints' => $endPoints,
			'Formats' => $formats
		))->renderWith(array('DocumentationBase', 'Page'));
	}

	private function getEndPointDescription($className) {
		$apiAccess = singleton($className)->stat('api_access');

		if (isset($apiAccess['description'])) {
			return $apiAccess['description'];
		} else {
			return null;
		}
	}

	/**
	 * adds a new formatter to the controller
	 *
	 * @param string $extension
	 * @param Formatter $formatterClassName
	 */
	public static function add_format($extension, $formatterClassName) {
		if (!class_exists($formatterClassName)) {
			user_error('Formatter class (' . $formatterClassName . ') not found');
		}

		self::$valid_formats[$extension] = $formatterClassName;
	}

	/**
	 * removes a existing formatter
	 *
	 * @param  string $extension
	 */
	public static function remove_format($extension) {
		if (isset(self::$valid_formats[$extension])) {
			unset(self::$valid_formats[$extension]);
		}
	}

	/**
	 * returns an array of the available formats
	 *
	 * @return array
	 */
	public static function get_available_formats() {
		return array_keys(self::$valid_formats);
	}

	/**
	 * returns the applicable base url
	 *
	 * @return string
	 */
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
			$matchedRoute = __CLASS__;
		}

		self::$base_url = Director::absoluteBaseURL() . $matchedRoute;

		return self::$base_url;
	}

	public static function use_as_version_one_api() {
		$routes = Config::inst()->get('Director', 'rules');

		$routesToRemove = array(
			'api/v1/live',
			'api/v1',
			'api/v2//$ResourceName/$ResourceID/$RelationName'
		);

		foreach ($routesToRemove as $route) {
			unset($routes[$route]);
		}

		$routes = array_merge(
			array(
				'api/v1//$ResourceName/$ResourceID/$RelationName' => 'RestfulServer\ControllerV2'
			),
			$routes
		);

		Config::inst()->update('Director', 'rules', $routes);
	}

	/**
	 * set the logger for the requests onto the controller
	 *
	 * @param RequestLogger $logger
	 */
	public static function set_request_logger(RequestLogger $logger) {
		self::$logger = $logger;
	}
}
