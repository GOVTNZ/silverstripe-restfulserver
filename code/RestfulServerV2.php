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

	public function init() {
		parent::init();

		$this->setFormatter();

		if (is_null($this->formatter)) {
			// errors are caused when running unit tests if we don't include $this->popCurrent() here
			// I think it's related to needing to clean up the Controller stack when we kill a request
			// within the init method - doesn't seem to affect normal operation
			$this->popCurrent();
			return $this->httpError(400, 'Invalid format type');
		}

		$this->getResponse()->addHeader('Content-Type', $this->formatter->getOutputContentType());
	}

	private function setFormatter() {
		// we only use the URL extension to determine format for the time being
		$extension = $this->request->getExtension();

		if (!$extension) {
			$extension = self::$default_extension;
		}

		if (!in_array($extension, array_keys(self::$valid_formats))) {
			return null;
		}

		$this->formatter = new self::$valid_formats[$extension]();
	}

	public function listResources() {
		$resourceName = $this->request->param('ResourceName');

		$className = APIInfo::get_class_name_by_resource_name($resourceName);

		if ($className === false) {
			return $this->httpError(404, 'Not found.'); // eventually needs to be displayed in requested format
		}

		// very basic method for retrieving records for time being, improve this when adding sorting, pagination, etc.
		$list = $className::get();

		$this->formatter->setMetaData(array(
			'totalCount' => (int) $list->Count(),
			'limit' => 10,
			'offset' => 0
		));

		$apiAccess = singleton($className)->stat('api_access');

		if (isset($apiAccess['singular_name'])) {
			$this->formatter->setSingularItemName($apiAccess['singular_name']);
		}

		if (isset($apiAccess['plural_name'])) {
			$this->formatter->setPluralItemName($apiAccess['plural_name']);
		}

		return $this->formatter->formatList($list);
	}

	public function showResource() {
		return $this->httpError(500, 'Resource detail not yet implemented');
	}

	public function listRelations() {
		return $this->httpError(500, 'Relationship access not yet implemented');
	}

	public function index() {
		return $this->httpError(500, 'Base documentation not yet implemented');
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
