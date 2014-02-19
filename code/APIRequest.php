<?php

class APIRequest {

	private $httpRequest = null;

	private $resourceClassName = null;
	private $resourceID = null;
	private $relationClassName = null;

	private $resource = null;

	private $formatter = null;

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

	public function __construct(SS_HTTPRequest $request, Formatter $formatter) {
		$this->httpRequest = $request;
		$this->formatter = $formatter;
	}

	public function outputResourceList() {
		$this->resourceClassName = APIInfo::get_class_name_by_resource_name($this->httpRequest->param('ResourceName'));

		$className = $this->resourceClassName;
		$list = $className::get();

		return $this->outputList($list, $className);
	}

	private function outputList(DataList $list, $className) {
		$this->setPagination();
		$this->setSorting($className);


		$list = $this->applyFilters($list);
		$this->setTotalCount($list);
		$this->setMetaData();
		$list = $list->sort($this->sort, $this->order);
		$list = $list->limit($this->limit, $this->offset);

		$results = array();

		foreach ($list as $item) {
			$itemFieldValueMap = $item->toMap();

			$results[] = $this->applyPartialResponse($itemFieldValueMap);
		}

		$this->formatter->addResultsSet(
			$results,
			$this->getPluralName($className),
			$this->getSingularName($className)
		);

		return $this->formatter->format();
	}

	private function applyPartialResponse($itemFieldValueMap) {
		$excludeFields = array(
			'ClassName',
			'RecordClassName',
			'Created',
			'LastEdited'
		);

		$result = array();
		$partialResponseFields = $this->httpRequest->getVar('fields');

		if ($partialResponseFields) {
			$partialResponseFields = explode(',', $partialResponseFields);
		} else {
			$partialResponseFields = array_keys($itemFieldValueMap);
		}

		// we always want ID
		$result['ID'] = $itemFieldValueMap['ID'];
		unset($itemFieldValueMap['ID']);
		$partialResponseFields = array_diff($partialResponseFields, array('ID'));

		// remove excluded fields
		$partialResponseFields = array_diff($partialResponseFields, $excludeFields);

		foreach ($itemFieldValueMap as $fieldName => $value) {
			if (in_array($fieldName, $partialResponseFields) && !in_array($fieldName, $excludeFields)) {
				$result[$fieldName] = $value;
				// remove the field name from partialResponseFields
				$partialResponseFields = array_diff($partialResponseFields, array($fieldName));
			}
		}

		// check for any fields that don't exist on our object
		if (count($partialResponseFields) > 0) {
			throw new APIUserException('invalidField', array('fields' => implode(', ', $partialResponseFields)));
		}

		return $result;
	}

	private function setPagination() {
		$this->setLimit();
		$this->setOffset();
	}

	private function setLimit() {
		$limit = (int) $this->httpRequest->getVar('limit');

		if ($limit < self::MIN_LIMIT || $limit > self::MAX_LIMIT) {
			$this->limit = self::DEFAULT_LIMIT;
		} else {
			$this->limit = $limit;
		}
	}

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
		$fieldMap = APIInfo::get_dataobject_field_alias_map($sortClassName);

		$sort = strtolower($this->httpRequest->getVar('sort'));

		if (isset($fieldMap[$sort])) {
			$this->sort = $fieldMap[$sort];
		} else {
			$this->sort = self::DEFAULT_SORT;
		}
	}

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

	private function applyFilters(DataList $list) {
		$getVars = $this->httpRequest->getVars();
		$filter = new APIFilter($list->dataClass());
		$filterArray = $filter->parseGET($getVars);

		if (count($filterArray) > 0) {
			$list = $list->filter($filterArray);
		}

		return $list;
	}

	private function setTotalCount(DataList $list) {
		$this->totalCount = (int) $list->Count();

		if ($this->totalCount > 0 && $this->offset >= $this->totalCount) {
			throw new APIUserException('offsetOutOfBounds');
		}
	}

	private function setMetaData() {
		$this->formatter->addExtraData(array(
			'_metadata' => array(
				'totalCount' => $this->totalCount,
				'limit' => $this->limit,
				'offset' => $this->offset
			)
		));
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

		$this->setResource();

		$this->formatter->addExtraData(array(
			$this->getSingularName($this->resourceClassName) => $this->applyPartialResponse($this->resource->toMap())
		));

		return $this->formatter->format();
	}

	private function setResource() {
		$className = $this->resourceClassName;

		$this->resource = $className::get()->byID($this->resourceID);

		if (!$this->resource) {
			throw new APIUserException('recordNotFound');
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

		$this->setRelationClassNameFromRelationName($relationMethod);
		$list = $this->resource->$relationMethod();

		foreach ($list as $item) {
			$itemFieldValueMap = $item->toMap();

			$results[] = $this->applyPartialResponse($itemFieldValueMap);
		}

		return $this->outputList($list, $this->relationClassName);
	}

	private function setRelationClassNameFromRelationName($relationName) {
		$relationClassName = $this->resource->has_many($relationName);

		if ($relationClassName !== false) {
			$this->relationClassName = $relationClassName;
		}

		$relationClassName = $this->resource->many_many($relationName);

		if (!is_null($relationClassName) && isset($relationClassName[1])) {
			$this->relationClassName = $relationClassName[1];
		}
	}

}
