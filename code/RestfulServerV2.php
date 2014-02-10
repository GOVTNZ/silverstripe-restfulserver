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
			$this->getResponse()->addHeader('Content-Type', 'text/plain');

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
		$sort   = $this->getResultsSort($className);
		$order  = $this->getResultsOrder();

		// very basic method for retrieving records for time being, improve this when adding sorting, pagination, etc.
		$list = $className::get();

		$list = $this->applyFilters($list, $className);

		$totalCount = (int) $list->Count();

		if ($totalCount > 0 && $offset >= $totalCount) {
			return $this->formattedError(400, APIError::get_messages_for('offsetOutOfBounds'));
		}

		$this->formatter->setExtraData(array(
			'_metadata' => array(
				'totalCount' => $totalCount,
				'limit' => $limit,
				'offset' => $offset
			)
		));

		// default sort until sorting via parameter is implemented
		$list = $list->sort($sort, $order);

		$list = $list->limit($limit, $offset);

		$this->setFormatterItemNames($className);

		$this->formatter->setResultsList($list);

		return $this->formatter->format();
	}

	private function getClassName() {
		$resourceName = $this->getRequest()->param('ResourceName');
		$className = APIInfo::get_class_name_by_resource_name($resourceName);

		if ($className === false) {
			return $this->formattedError(
				400,
				APIError::get_messages_for('resourceNotFound', array('resourceName' => $resourceName))
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

	private function getResultsSort($className) {
		$fieldMap = APIInfo::get_dataobject_field_alias_map($className);

		$sort = strtolower($this->getRequest()->getVar('sort'));

		if (isset($fieldMap[$sort])) {
			return $fieldMap[$sort];
		}

		return self::DEFAULT_SORT;
	}

	private function getResultsOrder() {
		$validOrders = array(
			'ASC',
			'DESC'
		);

		$order = strtoupper($this->getRequest()->getVar('order'));

		if (in_array($order, $validOrders)) {
			return $order;
		}

		return self::DEFAULT_ORDER;
	}

	private function applyFilters(DataList $list, $className) {
		$getVars = $this->getRequest()->getVars();
		$filter = new APIFilter($className);
		$filterArray = $filter->parseGET($getVars);

		if ($filterArray === false) {
			$invalidFilterFields = $filter->getInvalidFields();

			return $this->formattedError(400, APIError::get_messages_for('invalidFilterFields', array(
				'fields' => implode(', ', $invalidFilterFields)
			)));
		}

		if (count($filterArray) > 0) {
			$list = $list->filter($filterArray);
		}

		return $list;
	}

	private function formattedError($statusCode, $data) {
		$this->formatter->setExtraData($data);
		return $this->throwAPIError($statusCode, $this->formatter->format());
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
			return $this->formattedError(400, APIError::get_messages_for('recordNotFound'));
		}

		$this->setFormatterItemNames($className);

		$this->formatter->setResultsItem($resource);

		return $this->formatter->format();
	}

	public function listRelations() {
		return $this->formattedError(500, array(
			'developerMessage' => 'Relationship access not yet implemented',
			'userMessage' => 'Something went wrong',
			'moreInfo' => 'coming soon'
		));
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
