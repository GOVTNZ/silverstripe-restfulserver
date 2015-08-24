<?php

namespace RestfulServer;

use SS_HTTPRequest;

abstract class Request {
	/**
	 * @var SS_HTTPRequest
	 */
	protected $httpRequest = null;

	/**
	 * @var Formatter
	 */
	protected $formatter = null;

	/**
	 * @param SS_HTTPRequest $request
	 * @param Formatter      $formatter
	 */
	public function __construct(SS_HTTPRequest $request, Formatter $formatter) {
		$this->httpRequest = $request;
		$this->formatter = $formatter;
	}

	abstract public function outputResourceList();
	abstract public function outputResourceDetail();
	abstract public function outputRelationList();

	/**
	 * transforms a request
	 *
	 * @param  string         $className
	 * @param  SS_HTTPRequest $originalRequest
	 * @return SS_HTTPRequest
	 */
	public static function get_transformed_request($className, SS_HTTPRequest $originalRequest) {
		// define the original and new vars
		$originalGetVars = $originalRequest->getVars();
		$newGetVars = array();

		// some fields in the original request are simply ignored (e.g. url)
		$originalGetVars = self::remove_ignored_fields($originalGetVars);

		// some fields are just copied (e.g. limit)
		$newGetVars = self::copy_field_without_transformation($originalGetVars, $newGetVars);

		// get the list of fields and the aliases
		$fields = APIInfo::get_aliased_fields_for($className);
		$aliasToFieldMap = APIInfo::get_alias_field_map_for($className);

		// replace the sorting field, if it has been an alias
		if (isset($originalGetVars['sort'])) {
			$newGetVars['sort'] = self::transform_sort($originalGetVars['sort'], $aliasToFieldMap);
		}

		// replace all aliases in fields with the real field name
		if (isset($originalGetVars['fields'])) {
			$newGetVars['fields'] = self::transform_partial_response($originalGetVars['fields'], $aliasToFieldMap);
		}

		// the remaining fields should be either an alias or in the list of fields
		foreach ($originalGetVars as $fieldName => $fieldValue) {
			if (isset($aliasToFieldMap[$fieldName])) {
				$newGetVars[$aliasToFieldMap[$fieldName]] = $fieldValue;
			} else if (in_array($fieldName, $fields)) {
				$newGetVars[$fieldName] = $fieldValue;
			}
		}

		// if there are any fields unmapped it means there is a problem - we should throw an exception
		if (count($originalGetVars) > count($newGetVars)) {
			throw new UserException(
				'invalidFilterFields',
				array(
					'fields' => implode(', ', array_diff(
						array_keys($originalGetVars),
						array_keys($newGetVars)
					))
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

	/**
	 * maps the sort field in case an alias has been used.
	 *
	 * @param  string $requestField
	 * @param  array  $aliasFieldMap
	 * @return string
	 */
	private static function transform_sort($requestField, $aliasFieldMap) {
		return (isset($aliasFieldMap[$requestField])) ? $aliasFieldMap[$requestField] : $requestField;
	}

	/**
	 * transforms a field list into a field list without aliases
	 *
	 * @param  string $requestValue  comma separated list of fields including aliases
	 * @param  array  $aliasFieldMap
	 * @return string                comma separated list of fields without aliases
	 */
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

	/**
	 * some fields aren't relevant and need to be removed.
	 *
	 * @param  array $originalGetVars
	 * @return array $originalGetVars
	 */
	protected static function remove_ignored_fields($originalGetVars) {
		$fieldsToIgnore = array(
			'url',
			'flush',
			'flushtoken'
		);

		return array_diff_key($originalGetVars, array_flip($fieldsToIgnore));
	}

	/**
	 * some fields aren't transformed and will only be copied.
	 *
	 * @param  array $originalGetVars
	 * @param  array $newGetVars
	 * @return array $newGetVars
	 */
	protected static function copy_field_without_transformation($originalGetVars, $newGetVars) {
		$fieldsToCopyWithoutTransform = array(
			'order',
			'offset',
			'limit'
		);

		foreach ($fieldsToCopyWithoutTransform as $fieldToCopy) {
			if (isset($originalGetVars[$fieldToCopy])) {
				$newGetVars[$fieldToCopy] = $originalGetVars[$fieldToCopy];
			}
		}

		return $newGetVars;
	}
}
