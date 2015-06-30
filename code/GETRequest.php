<?php

namespace RestfulServer;

class GETRequest extends Request {

	private $resourceClassName = null;
	private $resourceID = null;
	private $relationClassName = null;

	private $resultClassName = null;

	private $resource = null;

	private $limit = null;
	private $offset = null;
	private $sort = null;
	private $order = null;

	private $totalCount = null;

	const MIN_LIMIT      = 1;
	const MAX_LIMIT      = 100;
	const DEFAULT_LIMIT  = 10;
	const DEFAULT_OFFSET = 0;
	const DEFAULT_SORT   = 'ID';
	const DEFAULT_ORDER  = 'ASC';

	public function outputResourceList() {
		// transform resource name into class name
		$this->resourceClassName = APIInfo::get_class_name_by_resource_name($this->httpRequest->param('ResourceName'));

		// transform GET parameters (and replace httpRequest)
		$this->httpRequest = Request::get_transformed_request($this->resourceClassName, $this->httpRequest);

		$className = $this->resourceClassName;
		$this->resultClassName = $className;
		$list = $className::get();

		return $this->outputList($list, $className);
	}

	/**
	 * generates the output and returns a formated version depending on the set format
	 *
	 * @param  \DataList $list
	 * @param  string    $className
	 * @return string
	 */
	private function outputList(\DataList $list, $className) {
		$this->setPagination();
		$this->setSorting($className);

		$list = $this->applyFilters($list);
		$this->setTotalCount($list);
		$this->setMetaData();
		$list = $list->sort($this->sort, $this->order);
		$list = $list->limit($this->limit, $this->offset);

		$results = array();

		foreach ($list as $item) {
			$result = array();
			$fields = APIInfo::get_database_fields_for($item->ClassName);

			foreach ($fields as $fieldName) {
				$result[$fieldName] = $item->$fieldName;
			}

			$result = $this->applyPartialResponse($result);
			$result = $this->removeForbiddenFields($result, $className);
			$result = $this->applyFieldNameAliasTransformation($result, $className);

			$results[] = $result;
		}

		$this->formatter->addResultsSet(
			$results,
			$this->getPluralName($className),
			$this->getSingularName($className)
		);

		return $this->formatter->format();
	}

	/**
	 * set the pagination (limit and offset) on the request
	 */
	private function setPagination() {
		$this->setLimit();
		$this->setOffset();
	}

	/**
	 * sets the limit within the allowed limits
	 */
	private function setLimit() {
		$limit = (int) $this->httpRequest->getVar('limit');

		if ($limit < self::MIN_LIMIT || $limit > self::MAX_LIMIT) {
			$this->limit = self::DEFAULT_LIMIT;
		} else {
			$this->limit = $limit;
		}
	}

	/**
	 * sets the offset
	 */
	private function setOffset() {
		$offset = (int) $this->httpRequest->getVar('offset');

		if ($offset < 0) {
			$this->offset = self::DEFAULT_OFFSET;
		} else {
			$this->offset = $offset;
		}
	}

	private function setSorting($sortClassName) {
		$this->setSort($sortClassName);
		$this->setOrder();
	}

	private function setSort($sortClassName) {
		$sort = $this->httpRequest->getVar('sort');

		if (!$sort) {
			$this->sort = self::DEFAULT_SORT;
			return;
		}

		if (!$this->isValidSortField($sort, $sortClassName)) {
			$this->sort = self::DEFAULT_SORT;
		} else {
			$this->sort = $sort;
		}
	}

	private function isValidSortField($sort, $sortClassName) {
		$fields = $this->getClassFields($sortClassName);

		return in_array($sort, $fields);
	}

	/**
	 * assembles the available fields of a given class
	 *
	 * @param  string $className
	 * @return array
	 */
	private function getClassFields($className) {
		$fields = array(
			'ID',
			'Created',
			'LastEdited'
		);

		return array_merge($fields, array_keys(singleton($className)->inheritedDatabaseFields()));
	}

	/**
	 * verifies and sets the order
	 */
	private function setOrder() {
		$validOrders = array(
			'ASC',
			'DESC'
		);

		$order = strtoupper($this->httpRequest->getVar('order'));

		if (in_array($order, $validOrders)) {
			$this->order = $order;
		} else {
			$this->order = self::DEFAULT_ORDER;
		}
	}

	/**
	 * applies the filters on the result list
	 *
	 * @param  \DataList $list
	 * @return \DataList
	 */
	private function applyFilters(\DataList $list) {
		$getVars = $this->httpRequest->getVars();
		$filterValues = $this->transformAliases($getVars, $list->dataClass());
		$filter = new ResponseFilter($list->dataClass());
		$filterArray = $filter->parseGET($filterValues);

		if (count($filterArray) > 0) {
			$list = $list->filter($filterArray);
		}

		return $list;
	}

	/**
	 * maps the fields names from the aliases to the real names
	 *
	 * @param  array $aliasValueMap
	 * @param  string $className
	 * @return array
	 */
	private function transformAliases($aliasValueMap, $className) {
		$apiAccess = singleton($className)->stat('api_access');

		if (!isset($apiAccess['field_aliases'])) {
			return $aliasValueMap;
		}

		$aliasToFieldNameMap = $apiAccess['field_aliases'];
		$fieldValueMap = array();

		foreach ($aliasValueMap as $aliasOrFieldName => $value) {
			if (isset($aliasToFieldNameMap[$aliasOrFieldName])) {
				$fieldValueMap[$aliasToFieldNameMap[$aliasOrFieldName]] = $value;
			} else {
				$fieldValueMap[$aliasOrFieldName] = $value;
			}
		}

		return $fieldValueMap;
	}

	/**
	 * sets the total count
	 *
	 * @param \DataList $list
	 */
	private function setTotalCount(\DataList $list) {
		$this->totalCount = (int) $list->Count();

		if ($this->totalCount > 0 && $this->offset >= $this->totalCount) {
			throw new UserException('offsetOutOfBounds');
		}
	}

	/**
	 * adds the additional data to the output
	 */
	private function setMetaData() {
		$this->formatter->addExtraData(array(
			'_metadata' => array(
				'totalCount' => $this->totalCount,
				'limit' => $this->limit,
				'offset' => $this->offset
			)
		));
	}

	private function applyPartialResponse($itemFieldValueMap) {
		$partialResponseFields = $this->httpRequest->getVar('fields');

		if ($partialResponseFields) {
			$partialResponseFields = explode(',', $partialResponseFields);
		} else {
			$partialResponseFields = APIInfo::get_viewable_fields_for($this->resultClassName);
		}

		// we always want ID
		$result = array(
			'ID' => $itemFieldValueMap['ID']
		);

		$availableFields = APIInfo::get_viewable_fields_for($this->resultClassName);
		$invalidFields = array();

		foreach ($partialResponseFields as $fieldName) {
			if (in_array($fieldName, $availableFields)) {
				$result[$fieldName] = $itemFieldValueMap[$fieldName];
			} else {
				$invalidFields[] = $fieldName;
			}
		}

		// check for any fields that don't exist on our object
		if (count($invalidFields) > 0) {
			throw new UserException(
				'invalidField',
				array(
					'fields' => implode(', ', $invalidFields)
				)
			);
		}

		return $result;
	}

	private function removeForbiddenFields($result, $className) {
		$availableFields = APIInfo::get_viewable_fields_for($className);

		foreach ($result as $fieldName => $value) {
			if (!in_array($fieldName, $availableFields)) {
				unset($result[$fieldName]);
			}
		}

		return $result;
	}

	private function applyFieldNameAliasTransformation($response, $className) {
		$apiAccess = singleton($className)->stat('api_access');

		if (!isset($apiAccess['field_aliases'])) {
			return $response;
		}

		$fieldNameAliases = array_flip(APIInfo::get_alias_field_map_for($className));
		$aliasedResponse = array();

		foreach ($response as $fieldName => $value) {
			if (isset($fieldNameAliases[$fieldName])) {
				$aliasedResponse[$fieldNameAliases[$fieldName]] = $value;
			} else {
				$aliasedResponse[$fieldName] = $value;
			}
		}

		return $aliasedResponse;
	}

	private function getPluralName($className) {
		$apiAccess = singleton($className)->stat('api_access');

		if (isset($apiAccess['plural_name'])) {
			return $apiAccess['plural_name'];
		}

		return 'items';
	}

	private function getSingularName($className) {
		$apiAccess = singleton($className)->stat('api_access');

		if (isset($apiAccess['singular_name'])) {
			return $apiAccess['singular_name'];
		}

		return 'item';
	}

	public function outputResourceDetail() {
		$this->resourceClassName = APIInfo::get_class_name_by_resource_name($this->httpRequest->param('ResourceName'));
		$this->resourceID = (int) $this->httpRequest->param('ResourceID');

		$this->resultClassName = $this->resourceClassName;

		// transform GET parameters (and replace httpRequest)
		$this->httpRequest = Request::get_transformed_request($this->resourceClassName, $this->httpRequest);

		$this->setResource();

		$result = array();
		$fields = APIInfo::get_database_fields_for($this->resource->ClassName);

		foreach ($fields as $fieldName) {
			$result[$fieldName] = $this->resource->$fieldName;
		}

		$result = $this->applyPartialResponse($result);
		$result = $this->removeForbiddenFields($result, $this->resultClassName);
		$result = $this->applyFieldNameAliasTransformation($result, $this->resultClassName);

		$this->formatter->addExtraData(array(
			$this->getSingularName($this->resourceClassName) => $result
		));

		return $this->formatter->format();
	}

	private function setResource() {
		$className = $this->resourceClassName;

		$this->resource = $className::get()->byID($this->resourceID);

		if (!$this->resource) {
			throw new UserException('recordNotFound');
		}
	}

	public function outputRelationList() {
		$this->resourceClassName = APIInfo::get_class_name_by_resource_name($this->httpRequest->param('ResourceName'));
		$this->resourceID = (int) $this->httpRequest->param('ResourceID');

		$this->setResource();

		$relationMethod = APIInfo::get_relation_method_from_name(
			$this->resourceClassName,
			$this->httpRequest->param('RelationName')
		);

		$this->relationClassName = APIInfo::get_class_name_by_relation($this->resourceClassName, $relationMethod);

		$this->resultClassName = $this->relationClassName;

		// transform GET parameters (and replace httpRequest)
		$this->httpRequest = Request::get_transformed_request($this->relationClassName, $this->httpRequest);

		$list = $this->resource->$relationMethod();

		return $this->outputList($list, $this->relationClassName);
	}

}
