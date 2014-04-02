<?php

namespace RestfulServer;

use ArrayList, ViewableData;

class DocumentationRequest extends Request {

	public function outputResourceList() {
		$data = array();

		$data['AvailableFields'] = $this->getAvailableFields();
		$data['Relations'] = $this->getRelations();
		$data['EndPoint'] = $this->httpRequest->param('ResourceName');
		$data['APIBaseURL'] = ControllerV2::get_base_url();

		$template = new ViewableData();

		return $template->customise($data)->renderWith('DocumentationList');
	}

	private function getAvailableFields() {
		$className = APIInfo::get_class_name_by_resource_name($this->httpRequest->param('ResourceName'));
		$fields = APIInfo::get_fields_for($className);

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
		return 'something';
	}

	public function outputRelationList() {
		return 'many related things';
	}

}
