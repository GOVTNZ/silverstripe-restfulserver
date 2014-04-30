<?php

namespace RestfulServer;

use ArrayList, ViewableData;

class DocumentationRequest extends Request {

	public function outputResourceList() {
		$className = APIInfo::get_class_name_by_resource_name($this->httpRequest->param('ResourceName'));
		$data = array();

		$data['AvailableFields'] = $this->getAvailableFields($className);
		$data['Relations'] = $this->getRelations();
		$data['EndPoint'] = $this->httpRequest->param('ResourceName');
		$data['APIBaseURL'] = ControllerV2::get_base_url();

		$template = new ViewableData();

		return $template->customise($data)->renderWith('DocumentationList');
	}

	private function getAvailableFields($className) {
		$fields = APIInfo::get_aliased_fields_for($className);
		$fieldAliasMap = APIInfo::get_field_alias_map_for($className);
		$viewableFields = APIInfo::get_viewable_fields($className);

		foreach ($viewableFields as $key => $viewableField) {
			if (isset($fieldAliasMap[$viewableField])) {
				$viewableFields[$key] = $fieldAliasMap[$viewableField];
			}
		}

		$availableFields = new ArrayList();

		foreach ($fields as $fieldName) {
			if (count($viewableFields) === 0 || in_array($fieldName, $viewableFields)) {
				$availableFields->push(array(
					'Name' => $fieldName
				));
			}
		}

		return $availableFields;
	}

	private function getRelations() {
		$className = APIInfo::get_class_name_by_resource_name($this->httpRequest->param('ResourceName'));
		$relations = APIInfo::get_relations_for($className);

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

		$template = new ViewableData();

		return $template->customise($data)->renderWith('DocumentationDetail');
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

		$template = new ViewableData();

		return $template->customise($data)->renderWith('DocumentationRelations');
	}

}
