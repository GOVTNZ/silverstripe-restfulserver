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
		$this->setResourceClassNameFromResourceName($this->httpRequest->param('ResourceName'));

		$className = $this->resourceClassName;

		$this->setPagination();
		$this->setSorting($className);

		$list = $className::get();
		$list = $this->applyFilters($list);
		$this->setTotalCount($list);
		$this->setMetaData();
		$list = $list->sort($this->sort, $this->order);
		$list = $list->limit($this->limit, $this->offset);

		$this->setFormatterItemNames($className);
		$this->setResponseFields($className);

		$this->formatter->setResultsList($list);

		return $this->formatter->format();
	}

	private function setResourceClassNameFromResourceName($resourceName) {
		$resourceClassName = APIInfo::get_class_name_by_resource_name($resourceName);

		if ($resourceClassName === false) {
			return APIError::throw_formatted_error(
				$this->formatter,
				400,
				'resourceNotFound',
				array(
					'resourceName' => $resourceName
				)
			);
		}

		$this->resourceClassName = $resourceClassName;
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

		if ($filterArray === false) {
			$invalidFilterFields = $filter->getInvalidFields();

			return APIError::throw_formatted_error(
				$this->formatter,
				400,
				'invalidFilterFields',
				array(
					'fields' => implode(', ', $invalidFilterFields)
				)
			);
		}

		if (count($filterArray) > 0) {
			$list = $list->filter($filterArray);
		}

		return $list;
	}

	private function setTotalCount(DataList $list) {
		$this->totalCount = (int) $list->Count();

		if ($this->totalCount > 0 && $this->offset >= $this->totalCount) {
			return APIError::throw_formatted_error($this->formatter, 400, 'offsetOutOfBounds');
		}
	}

	private function setMetaData() {
		$this->formatter->setExtraData(array(
			'_metadata' => array(
				'totalCount' => $this->totalCount,
				'limit' => $this->limit,
				'offset' => $this->offset
			)
		));
	}

	private function setFormatterItemNames($className) {
		$apiAccess = singleton($className)->stat('api_access');

		if (isset($apiAccess['singular_name'])) {
			$this->formatter->setSingularItemName($apiAccess['singular_name']);
		}

		if (isset($apiAccess['plural_name'])) {
			$this->formatter->setPluralItemName($apiAccess['plural_name']);
		}
	}

	private function setResponseFields($className) {
		$actualFields = array_keys(singleton($className)->inheritedDatabaseFields());
		$fields = $this->httpRequest->getVar('fields');

		if ($fields) {
			$fields = explode(',', $fields);

			$invalidFields = array();

			foreach ($fields as $fieldName) {
				if (!in_array($fieldName, $actualFields)) {
					$invalidFields[] = $fieldName;
				}
			}

			if (count($invalidFields) > 0) {
				return APIError::throw_formatted_error($this->formatter, 400, 'invalidField', array(
					'fields' => implode(', ', $invalidFields)
				));
			}
		} else {
			$fields = $actualFields;
		}

		$this->formatter->setResultsFields($fields);
	}

	public function outputResourceDetail() {
		$this->setResourceClassNameFromResourceName($this->httpRequest->param('ResourceName'));
		$this->setResourceID((int) $this->httpRequest->param('ResourceID'));
		$this->setResource();

		$className = $this->resourceClassName;
		$resource = $className::get()->byID((int) $this->httpRequest->param('ResourceID'));

		if (is_null($resource)) {
			return APIError::throw_formatted_error($this->formatter, 400, 'recordNotFound');
		}

		$this->setFormatterItemNames($className);
		$this->setResponseFields($className);
		
		$this->formatter->setResultsItem($resource);

		return $this->formatter->format();
	}

	private function setResourceID($resourceID) {
		$this->resourceID = $resourceID;
	}

	private function setResource() {
		$className = $this->resourceClassName;

		$this->resource = $className::get()->byID($this->resourceID);

		if (!$this->resource) {
			return APIError::throw_formatted_error($this->formatter, 400, 'recordNotFound');
		}
	}

	public function outputRelationList() {
		$this->setResourceClassNameFromResourceName($this->httpRequest->param('ResourceName'));
		$this->setResourceID((int) $this->httpRequest->param('ResourceID'));
		$this->setResource();

		$className = $this->resourceClassName;

		$resource = $className::get()->byID((int) $this->httpRequest->param('ResourceID'));

		if (is_null($resource)) {
			return APIError::throw_formatted_error($this->formatter, 400, 'recordNotFound');
		}

		$relationMethod = APIInfo::get_relation_method_from_name(
			$className,
			$this->httpRequest->param('RelationName')
		);

		if (is_null($relationMethod)) {
			return APIError::throw_formatted_error(
				$this->formatter,
				400,
				'relationNotFound',
				array(
					'relation' => $this->httpRequest->param('RelationName')
				)
			);
		}

		$this->setRelationClassNameFromRelationName($relationMethod);

		$this->setPagination();
		$this->setSorting($this->relationClassName);

		$list = $resource->$relationMethod();
		$list = $this->applyFilters($list);

		$this->setTotalCount($list);
		$this->setMetaData();

		$list = $list->sort($this->sort, $this->order);
		$list = $list->limit($this->limit, $this->offset);

		$this->setFormatterItemNames($this->relationClassName);

		$this->formatter->setResultsList($list);

		return $this->formatter->format();
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
