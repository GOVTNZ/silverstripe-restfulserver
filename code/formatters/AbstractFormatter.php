<?php

abstract class AbstractFormatter implements Formatter {

	protected $metaData = null;

	protected $singularItemName = 'item';
	protected $pluralItemName = 'items';

	protected $outputContentType = 'text/plain';

	public function setSingularItemName($name) {
		$this->singularItemName = $name;
	}

	public function setPluralItemName($name) {
		$this->pluralItemName = $name;
	}

	public function setMetaData($metaData) {
		$this->metaData = $metaData;
	}

	public function getOutputContentType() {
		return $this->outputContentType;
	}

	public function formatList(SS_List $list, $fields = null) {
		$response = array();

		$fields = $this->buildFieldList($list->dataClass(), $fields);

		$response[$this->pluralItemName] = array();
		$response[$this->pluralItemName] = $this->buildResultsArray($list, $fields);

		$response = $this->addMetaData($response);

		return $this->generateOutput($response);
	}

	private function buildFieldList($dataClass, $fields) {
		if (!is_null($fields) && is_array($fields)) {
			if (!in_array($fields, 'ID')) {
				array_unshift($fields, 'ID');
			}
		} else {
			// assume null or invalid value in $fields
			$fields = array('ID');

			foreach (DataObject::custom_database_fields($dataClass) as $fieldName => $fieldType) {
				$fields[] = $fieldName;
			}
		}

		return $fields;
	}

	private function buildResultsArray($list, $fields) {
		$results = array();

		foreach ($list as $item) {
			$responseItem = array();

			foreach ($fields as $field) {
				$responseItem[$field] = $item->$field;
			}

			$results[] = $responseItem;
		}

		return $results;
	}

	private function addMetaData($response) {
		if (!is_null($this->metaData) && is_array($this->metaData)) {
			$response['_metadata'] = $this->metaData;
		}

		return $response;
	}

	abstract protected function generateOutput($response);

}