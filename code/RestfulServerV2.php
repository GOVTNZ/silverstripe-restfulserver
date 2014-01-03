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

	public function listResources() {
		$resourceName = $this->request->param('ResourceName');

		$className = APIInfo::get_class_name_by_resource_name($resourceName);

		if ($className === false) {
			return $this->httpError(404, 'Not found.'); // eventually needs to be displayed in requested format
		}

		// only GET is supported for the time being so no checks are done
		// $formatter = $this->getDataFormatter();
		$formatter = $this->getFormatter();

		if (is_null($formatter)) {
			return $this->httpError(400, 'Invalid format type');
		}

		$this->getResponse()->addHeader('Content-Type', $formatter->getOutputContentType());

		// very basic method for retrieving records for time being, improve this when adding sorting, pagination, etc.
		$list = $className::get();

		$formatter->setMetaData(array(
			'totalCount' => (int) $list->Count(),
			'limit' => 10,
			'offset' => 0
		));

		$apiAccess = singleton($className)->stat('api_access');

		if (isset($apiAccess['singular_name'])) {
			$formatter->setSingularItemName($apiAccess['singular_name']);
		}

		if (isset($apiAccess['plural_name'])) {
			$formatter->setPluralItemName($apiAccess['plural_name']);
		}

		return $formatter->formatList($list);
	}

	private function getFormatter() {
		// we only use the URL extension to determine format for the time being
		$extension = $this->request->getExtension();

		if (!$extension) {
			$extension = self::$default_extension;
		}

		if (!in_array($extension, array_keys(self::$valid_formats))) {
			return null;
		}

		return new self::$valid_formats[$extension]();
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
