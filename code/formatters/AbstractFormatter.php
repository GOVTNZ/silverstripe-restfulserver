<?php

abstract class AbstractFormatter implements Formatter {

	protected $resultsList   = null;
	protected $resultsItem   = null;
	protected $resultsFields = null;

	protected $extraData = null;

	protected $singularItemName = 'item';
	protected $pluralItemName = 'items';

	protected $outputContentType = 'text/plain';

	public function setSingularItemName($name) {
		$this->singularItemName = $name;
	}

	public function setPluralItemName($name) {
		$this->pluralItemName = $name;
	}

	public function setExtraData($extraData) {
		$this->extraData = $extraData;
	}

	public function getOutputContentType() {
		return $this->outputContentType;
	}

	public function setResultsList(SS_List $list) {
		$this->resultsList   = $list;
	}

	public function setResultsItem(DataObject $item) {
		$this->resultsItem = $item;
	}

	public function setResultsFields($fields) {
		$this->resultsFields = $fields;
	}

	public function format() {
		$response = array();

		if (!is_null($this->resultsList)) {
			$fields = $this->buildFieldList($this->resultsList->dataClass(), $this->resultsFields);

			$response[$this->pluralItemName] = array();
			$response[$this->pluralItemName] = $this->buildResultsArray($this->resultsList, $fields);
		}

		if (!is_null($this->resultsItem)) {
			$fields = $this->buildFieldList($this->resultsItem->ClassName, $this->resultsFields);

			$response[$this->singularItemName] = array();
			$response[$this->singularItemName] = $this->buildResultObject($this->resultsItem, $fields);
		}

		$response = $this->addExtraData($response);

		return $this->generateOutput($response);
	}

	// move to APIInfo? no reason this can't be a static method on that class
	// this allows us to get a list of fields for a DataObject
	// this could give us a list of key->value pairs for fieldAlias->actualFieldName - e.g. id => ID, name => Name
	// would need to include a check of 'view' array at some point to ensure fields are allowed to be access
	private function buildFieldList($dataClass, $fields) {
		if (!is_null($fields) && is_array($fields)) {
			if (!in_array('ID', $fields)) {
				array_unshift($fields, 'ID');
			}
		} else {
			// assume null or invalid value in $fields
			$fields = array('ID');

			foreach (array_keys(singleton($dataClass)->inheritedDatabaseFields()) as $fieldName) {
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

	private function buildResultObject($item, $fields) {
		$responseItem = array();

		foreach ($fields as $field) {
			$responseItem[$field] = $item->$field;
		}

		return $responseItem;
	}

	private function addExtraData($response) {
		if (!is_null($this->extraData) && is_array($this->extraData)) {
			$response = array_merge($response, $this->extraData);
		}

		return $response;
	}

	abstract protected function generateOutput($response);

}