<?php

namespace RestfulServer;

use SS_HTTPRequest;

abstract class Request {

	protected $httpRequest = null;
	protected $formatter = null;

	public function __construct(SS_HTTPRequest $request, Formatter $formatter) {
		$this->httpRequest = $request;
		$this->formatter = $formatter;
	}

	abstract public function outputResourceList();
	abstract public function outputResourceDetail();
	abstract public function outputRelationList();

	public static function get_transformed_request($className, SS_HTTPRequest $originalRequest) {
		$originalGetVars = $originalRequest->getVars();
		$newGetVars = array();

		$fieldsToIgnore = array(
			'url',
			'flush',
			'flushtoken'
		);

		foreach ($fieldsToIgnore as $fieldToIgnore) {
			unset($originalGetVars[$fieldToIgnore]);
		}

		$fieldsToCopyWithoutTransform = array(
			'order',
			'offset',
			'limit'
		);

		$fields = APIInfo::get_fields_for($className);
		$aliasToFieldMap = APIInfo::get_field_alias_map_for($className);

		if (isset($originalGetVars['sort'])) {
			$newGetVars['sort'] = self::transform_sort($originalGetVars['sort'], $aliasToFieldMap);
			unset($originalGetVars['sort']);
		}

		if (isset($originalGetVars['fields'])) {
			$newGetVars['fields'] = self::transform_partial_response($originalGetVars['fields'], $aliasToFieldMap);
			unset($originalGetVars['fields']);
		}

		foreach ($fieldsToCopyWithoutTransform as $fieldToCopy) {
			if (!isset($originalGetVars[$fieldToCopy])) {
				continue;
			}

			$newGetVars[$fieldToCopy] = $originalGetVars[$fieldToCopy];
			unset($originalGetVars[$fieldToCopy]);
		}

		foreach ($originalGetVars as $fieldName => $fieldValue) {
			if (isset($aliasToFieldMap[$fieldName])) {
				$newGetVars[$aliasToFieldMap[$fieldName]] = $fieldValue;
				unset($originalGetVars[$fieldName]);
			} else if (in_array($fieldName, $fields)) {
				$newGetVars[$fieldName] = $fieldValue;
				unset($originalGetVars[$fieldName]);
			}
		}

		if (count($originalGetVars) > 0) {
			throw new UserException(
				'invalidFilterFields',
				array(
					'fields' => implode(', ', array_keys($originalGetVars))
				)
			);
		}

		return new SS_HTTPRequest(
			$originalRequest->httpMethod(),
			$originalRequest->getURL(),
			$newGetVars,
			$originalRequest->postVars(),
			$originalRequest->getBody()
		);
	}

	private static function transform_sort($requestField, $aliasFieldMap) {
		if (isset($aliasFieldMap[$requestField])) {
			return $aliasFieldMap[$requestField];
		} else {
			return $requestField;
		}
	}

	private static function transform_partial_response($requestValue, $aliasFieldMap) {
		$fields = explode(',', $requestValue);
		$unaliasedFields = array();

		foreach ($fields as $field) {
			if (isset($aliasFieldMap[$field])) {
				$unaliasedFields[] = $aliasFieldMap[$field];
			} else {
				$unaliasedFields[] = $field;
			}
		}

		return implode(',', $unaliasedFields);
	}

}
