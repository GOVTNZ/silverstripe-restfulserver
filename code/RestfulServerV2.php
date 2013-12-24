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
			$this->httpError(404, 'Not found.'); // eventually needs to be displayed in requested format
		}

		// data here
		return 'data here';
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
