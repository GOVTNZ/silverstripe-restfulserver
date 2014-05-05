<?php

namespace RestfulServer;

use ArrayList, SS_HTTPRequest;

class DocumentationRequest extends Request {

	public $templateRenderer;

	public function __construct(SS_HTTPRequest $request, Formatter $formatter) {
		parent::__construct($request, $formatter);
		/*
		 * Use ContentController if it is available as it provides a better output (default values like
		 * $SiteConfig.Title become available) but fall back to ViewableData if cms module is not installed.
		 */
		if (class_exists('ContentController')) {
			$this->templateRenderer = new \ContentController();
		} else {
			$this->templateRenderer = new \ViewableData();
		}
	}

	public function outputResourceList() {
		$className = APIInfo::get_class_name_by_resource_name($this->httpRequest->param('ResourceName'));
		$data = $this->getBaseData($className);

		return $this->templateRenderer->customise($data)->renderWith(array('DocumentationList', 'Page'));
	}

	private function getBaseData($className) {
		$data = array();

		if (singleton($className)->stat('singular_name')) {
			$data['SingularName'] = strtolower(singleton($className)->stat('singular_name'));
		} else {
			$data['SingularName'] = $className;
		}

		$data['AvailableFields'] = $this->getAvailableFields($className);
		$data['Relations'] = $this->getRelations();
		$data['EndPoint'] = $this->httpRequest->param('ResourceName');
		$data['APIBaseURL'] = ControllerV2::get_base_url();

		return $data;
	}

	private function getAvailableFields($className) {
		$fields = APIInfo::get_viewable_fields_with_aliases_for($className);
		$availableFields = new ArrayList();

		foreach ($fields as $fieldName) {
			$availableFields->push(array(
				'Name' => $fieldName
			));
		}

		return $availableFields;
	}

	private function getRelations() {
		$className = APIInfo::get_class_name_by_resource_name($this->httpRequest->param('ResourceName'));
		$relations = APIInfo::get_available_relations_with_aliases_for($className);

		$availableRelations = new ArrayList();

		foreach ($relations as $relationName) {
			$availableRelations->push(array(
				'Name' => $relationName
			));
		}

		return $availableRelations;
	}

	public function outputResourceDetail() {
		$className = APIInfo::get_class_name_by_resource_name($this->httpRequest->param('ResourceName'));
		$data = $this->getBaseData($className);

		$data['ResourceID'] = $this->httpRequest->param('ResourceID');

		return $this->templateRenderer->customise($data)->renderWith(array('DocumentationDetail', 'Page'));
	}

	public function outputRelationList() {
		$resourceClassName = APIInfo::get_class_name_by_resource_name($this->httpRequest->param('ResourceName'));
		$relationMethod = APIInfo::get_relation_method_from_name($resourceClassName, $this->httpRequest->param('RelationName'));
		$relationClassName = APIInfo::get_class_name_by_relation($resourceClassName, $relationMethod);

		$data = $this->getBaseData($relationClassName);

		$data['RelationName'] = $this->httpRequest->param('RelationName');

		return $this->templateRenderer->customise($data)->renderWith(array('DocumentationRelations', 'Page'));
	}

}
