<?php

namespace RestfulServer;

class ResponseFilter {

	private $className;

	private static $reserved_get_vars = array(
		'offset',
		'limit',
		'url',
		'sort',
		'order',
		'flush',
		'flushtoken',
		'fields'
	);

	public function __construct($className) {
		$this->className = $className;
	}

	public function parseGET($getVars) {
		$filterArray = array();
		$invalidFields = array();

		foreach ($getVars as $key => $value) {
			if (in_array($key, self::$reserved_get_vars)) {
				continue; // we don't filter on any reserved get variables
			}

			if (!APIInfo::class_can_be_filtered_by($this->className, $key)) {
				$invalidFields[] = $key;
				continue;
			}

			// this will need improving when we go to implement field name aliases
			$fieldName = $key;

			// attempt a partial match for whatever value is provided
			$filterArray[$fieldName . ':PartialMatch'] = $value;
		}

		if (count($invalidFields) > 0) {
			throw new UserException(
				'invalidFilterFields',
				array(
					'fields' => implode(', ', $invalidFields)
				)
			);
		}

		return $filterArray;
	}

}
