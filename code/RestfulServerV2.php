<?php

class RestfulServerV2 extends Controller {

	public static $default_extension = 'json';

	public static $url_handlers = array(
		'$ResourceName/$ResourceID!/$RelationName!' => 'listRelations',
		'$ResourceName/$ResourceID!' => 'showResource',
		'$ResourceName!' => 'listResources'
	);

	public static $allowed_actions = array(
		'index',
		'listResources',
		'showResource',
		'listRelations'
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

		$this->setFormatter();

		if (is_null($this->formatter)) {
			// errors are caused when running unit tests if we don't include $this->popCurrent() here
			// I think it's related to needing to clean up the Controller stack when we kill a request
			// within the init method - doesn't seem to affect normal operation
			$this->popCurrent();
			$this->getResponse()->addHeader('Content-Type', 'text/plain');
			return $this->apiError(400, 'Invalid format type');
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

	private function apiError($statusCode, $responseBody) {
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
			$this->formattedError(400, array(
				'developerMessage' => 'Query parameter \'offset\' is out of bounds',
				'userMessage' => 'Oops something went wrong',
				'moreInfo' => 'coming soon'
			));
		}

		$this->formatter->setExtraData(array(
			'_metadata' => array(
				'totalCount' => $totalCount,
				'limit' => $limit,
				'offset' => $offset
			)
		));

		$list = $list->limit($limit, $offset);

		$this->setFormatterItemNames($className);

		$this->formatter->setResultsList($list);

		return $this->formatter->format();
	}

	private function getClassName() {
		$resourceName = $this->getRequest()->param('ResourceName');
		$className = APIInfo::get_class_name_by_resource_name($resourceName);

		if ($className === false) {
			$this->formattedError(400, array(
				'developerMessage' => 'Resource \'' . $resourceName . '\' was not found.',
				'userMessage' => 'Oops something went wrong',
				'moreInfo' => 'coming soon'
			));
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
		$this->apiError($statusCode, $this->formatter->format());
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
			$this->formattedError(400, array(
				'developerMessage' => 'Record not found',
				'userMessage' => 'We couldn\'t find that item',
				'moreInfo' => 'coming soon'
			));
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

}
