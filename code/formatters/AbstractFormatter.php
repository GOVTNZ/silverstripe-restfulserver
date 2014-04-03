<?php

namespace RestfulServer;

abstract class AbstractFormatter implements Formatter {

	protected $resultsSets = array();
	protected $extraData = array();

	protected $outputContentType = 'text/plain';

	abstract public function format();

	public function getOutputContentType() {
		return $this->outputContentType;
	}

	public function addResultsSet($set, $pluralName = 'items', $singularName = 'item') {
		$resultsSet = array(
			'set' => $set,
			'pluralName' => $pluralName,
			'singularName' => $singularName
		);

		$this->resultsSets[] = $resultsSet;
	}

	public function addExtraData($data) {
		$this->extraData[] = $data;
	}

	public function clearData() {
		$this->resultsSets = array();
		$this->extraData = array();
	}

}