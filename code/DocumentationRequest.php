<?php

namespace RestfulServer;

use ArrayList, ContentController;

class DocumentationRequest extends Request {

	public function outputResourceList() {
		$className = APIInfo::get_class_name_by_resource_name($this->httpRequest->param('ResourceName'));
		$data = array();

		$data['AvailableFields'] = $this->getAvailableFields($className);
		$data['Relations'] = $this->getRelations();
		$data['EndPoint'] = $this->httpRequest->param('ResourceName');
		$data['APIBaseURL'] = ControllerV2::get_base_url();

		$template = new ContentController();

		return $template->customise($data)->renderWith(array('DocumentationList', 'Page'));
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
		$data = array();

		$data['AvailableFields'] = $this->getAvailableFields($className);
		$data['Relations'] = $this->getRelations();
		$data['APIBaseURL'] = ControllerV2::get_base_url();
		$data['EndPoint'] = $this->httpRequest->param('ResourceName');
		$data['ResourceID'] = $this->httpRequest->param('ResourceID');

		$template = new ContentController();

		return $template->customise($data)->renderWith(array('DocumentationDetail', 'Page'));
	}

	public function outputRelationList() {
		$resourceClassName = APIInfo::get_class_name_by_resource_name($this->httpRequest->param('ResourceName'));
		$relationMethod = APIInfo::get_relation_method_from_name($resourceClassName, $this->httpRequest->param('RelationName'));
		$relationClassName = APIInfo::get_class_name_by_relation($resourceClassName, $relationMethod);
		$data = array();

		$data['AvailableFields'] = $this->getAvailableFields($relationClassName);
		$data['APIBaseURL'] = ControllerV2::get_base_url();
		$data['EndPoint'] = $this->httpRequest->param('ResourceName');
		$data['ResourceID'] = $this->httpRequest->param('ResourceID');
		$data['RelationName'] = $this->httpRequest->param('RelationName');

		$template = new ContentController();

		return $template->customise($data)->renderWith(array('DocumentationRelations', 'Page'));
	}

}
