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

	public function listResources() {
		$resourceName = $this->request->param('ResourceName');

		$className = APIInfo::get_class_name_by_resource_name($resourceName);

		if ($className === false) {
			return $this->httpError(404, 'Not found.'); // eventually needs to be displayed in requested format
		}

		// only GET is supported for the time being so no checks are done
		$formatter = $this->getDataFormatter();

		if (is_null($formatter)) {
			return $this->httpError(400, 'Invalid format type');
		}

		$this->getResponse()->addHeader('Content-Type', $formatter->getOutputContentType());

		// very basic method for retrieving records for time being, improve this when adding sorting, pagination, etc.
		$list = $className::get();

		$formatter->setTotalSize($list->Count());
		return $formatter->convertDataObjectSet($list);
	}

	private function getDataFormatter() {
		// we only use the URL extension to determine format for the time being
		$extension = $this->request->getExtension();

		if (!$extension) {
			$extension = self::$default_extension;
		}

		return DataFormatter::for_extension($extension);
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

}
