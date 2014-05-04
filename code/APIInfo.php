<?php

namespace RestfulServer;

class APIInfo {

	const RESOURCE_NAME_CACHE_KEY = 'restfulserver_api_resource_name_cache';

	private static $alias_cache = null;

	/**
	 * Returns the class name of a DataObject given an API end point
	 *
	 * @param $resourceName string The resource name (can be an alias or class name)
	 * @return string|boolean The class name for the end point or false if none was found.
	 */
	public static function get_class_name_by_resource_name($resourceName) {
		self::initialise_resource_name_cache();

		// first, try and retrieve class name from cache
		$className = self::get_class_name_from_cache($resourceName);

		if ($className !== false) {
			return $className;
		}

		// second, try and retrieve class name by exact match (end point == class name)
		$className = self::get_class_name_from_exact_match($resourceName);

		if ($className !== false) {
			return $className;
		}

		// third, try and look up end point alias using ClassInfo
		$className = self::get_class_name_from_class_info($resourceName);

		if ($className !== false) {
			return $className;
		}

		throw new UserException(
			'resourceNotFound',
			array(
				'resourceName' => $resourceName
			)
		);
	}

	private static function initialise_resource_name_cache() {
		self::$alias_cache = \SS_Cache::factory(self::RESOURCE_NAME_CACHE_KEY);
	}

	private static function get_class_name_from_cache($resourceName) {
		try {
			$cacheValue = self::$alias_cache->load($resourceName);
		} catch (\Zend_Cache_Exception $exception) {
			$cacheValue = false;
		}

		return $cacheValue;
	}

	private static function get_class_name_from_exact_match($resourceName) {
		if (!class_exists($resourceName)) {
			return false;
		}

		$apiAccess = singleton($resourceName)->stat('api_access');

		if (!$apiAccess) {
			return false;
		}

		try {
			self::$alias_cache->save($resourceName, $resourceName);
		} catch (\Zend_Cache_Exception $exception) {
			// typically caused by an invalid cache key, don't do anything here - it just means the class isn't cached
		}

		return $resourceName;
	}

	private static function get_class_name_from_class_info($resourceName) {
		$dataClasses = \ClassInfo::subclassesFor('DataObject');

		foreach ($dataClasses as $className) {
			$apiAccess = singleton($className)->stat('api_access');

			if (
				is_array($apiAccess) &&
				isset($apiAccess['end_point_alias']) &&
				$apiAccess['end_point_alias'] == $resourceName
			) {
				try {
					self::$alias_cache->save($className, $resourceName);
				} catch (\Zend_Cache_Exception $exception) {
					// typically caused by an invalid cache key, don't do anything here - it just means the class isn't cached
				}

				return $className;
			}
		}

		return false;
	}

	public static function get_all_end_points() {
		$endPoints = array();

		if (\SapphireTest::is_running_test()) {
			$classNames = self::get_classes_for_test();
		} else {
			$classNames = self::get_classes();
		}

		foreach ($classNames as $className) {
			$apiAccess = singleton($className)->uninherited('api_access');

			if ($apiAccess === true) {
				$endPoints[$className] = $className;
			} else if (is_array($apiAccess) && isset($apiAccess['end_point_alias'])) {
				$endPoints[$apiAccess['end_point_alias']] = $className;
			}
		}

		return array_flip($endPoints);
	}

	private static function get_classes_for_test() {
		$testClass = BaseRestfulServerTest::get_current_test_class();
		return singleton($testClass)->getExtraDataObjects();
	}

	private static function get_classes() {
		$allClassNames = \ClassInfo::subclassesFor('DataObject');
		$classNames = array();

		foreach ($allClassNames as $className) {
			$instance = singleton($className);

			if ($instance instanceof \TestOnly) {
				continue;
			}

			$classNames[] = $className;
		}

		return $classNames;
	}

	public static function class_can_be_filtered_by($className, $fieldName) {
		$validFields = array_keys(singleton($className)->inheritedDatabaseFields());
		$validFields['id'] = 'ID';

		if (in_array($fieldName, $validFields)) {
			return true;
		}

		return false;
	}

	public static function get_relation_method_from_name($className, $relationName) {
		$relationAliasMap = APIInfo::get_relation_alias_map_for($className);
		$availableRelations = APIInfo::get_available_relations_with_aliases_for($className);

		if (in_array($relationName, $availableRelations)) {
			if (isset($relationAliasMap[$relationName])) {
				return $relationAliasMap[$relationName];
			} else {
				return $relationName;
			}
		}

		throw new UserException(
			'relationNotFound',
			array(
				'relation' => $relationName
			)
		);
	}

	public static function get_database_fields_for($className) {
		$fields = array(
			'ID',
			'Created',
			'LastEdited',
			'ClassName',
			'RecordClassName'
		);

		return array_merge($fields, array_keys(singleton($className)->inheritedDatabaseFields()));
	}

	public static function get_aliased_fields_for($className) {
		$fields = self::get_database_fields_for($className);

		$aliasMap = array_flip(self::get_alias_field_map_for($className));

		$fields = array_map(function ($item) use ($aliasMap) {
			if (isset($aliasMap[$item])) {
				return $aliasMap[$item];
			} else {
				return $item;
			}
		}, $fields);

		return $fields;
	}

	public static function get_field_alias_map_for($className) {
		return array_flip(self::get_alias_field_map_for($className));
	}

	public static function get_alias_field_map_for($className) {
		$apiAccess = singleton($className)->stat('api_access');

		if (!$apiAccess || !isset($apiAccess['field_aliases']) || !is_array($apiAccess['field_aliases'])) {
			return array();
		}

		return $apiAccess['field_aliases'];
	}

	public static function get_relations_for($className) {
		$instance = singleton($className);

		$hasMany = $instance->has_many();
		$manyMany = $instance->many_many();

		$hasManyManyMany = array_merge($hasMany, $manyMany);
		$relations = array();

		foreach ($hasManyManyMany as $relationName => $relationClassName) {
			$relations[] = $relationName;
		}

		$aliasMap = array_flip(self::get_relation_alias_map_for($className));

		$relations = array_map(function ($item) use ($aliasMap) {
			if (isset($aliasMap[$item])) {
				return $aliasMap[$item];
			} else {
				return $item;
			}
		}, $relations);

		return $relations;
	}

	private static function get_relation_alias_map_for($className) {
		$apiAccess = singleton($className)->stat('api_access');

		if (!$apiAccess || !isset($apiAccess['relation_aliases']) || !is_array($apiAccess['relation_aliases'])) {
			return array();
		}

		return $apiAccess['relation_aliases'];
	}

	public static function get_class_name_by_relation($resourceClassName, $relationMethod) {
		$instance = singleton($resourceClassName);

		$hasMany = $instance->has_many($relationMethod);

		if ($hasMany !== false && class_exists($hasMany)) {
			return $hasMany;
		}

		$manyMany = $instance->many_many($relationMethod);

		if (!is_null($manyMany) && class_exists($manyMany[1])) {
			return $manyMany[1];
		}

		throw new UserException(
			'relationNotFound',
			array(
				'relation' => $relationMethod
			)
		);
	}

	public static function has_api_access($className) {
		return (bool) singleton($className)->stat('api_access');
	}

	/**
	 * Get the viewable fields for a class
	 *
	 * @param $className The name of the class to get the viewable fields for
	 * @return array An array of viewable fields
	 */
	private static function get_view_array_for($className) {
		$apiAccess = singleton($className)->stat('api_access');

		if (!isset($apiAccess['view'])) {
			return array();
		}

		return $apiAccess['view'];
	}

	public static function get_viewable_fields_for($className) {
		$viewableFields = self::get_view_array_for($className);
		$allFields = self::get_database_fields_for($className);

		if (count($viewableFields) === 0) {
			return $allFields;
		} else {
			return $viewableFields;
		}
	}

	public static function get_viewable_fields_with_aliases_for($className) {
		$availableFields = self::get_viewable_fields_for($className);
		$fieldAliasMap = self::get_field_alias_map_for($className);

		foreach ($availableFields as $key => $fieldName) {
			if (isset($fieldAliasMap[$fieldName])) {
				$availableFields[$key] = $fieldAliasMap[$fieldName];
			}
		}

		return $availableFields;
	}

	public static function get_available_relations_for($className) {
		$instance = singleton($className);

		$hasMany = $instance->has_many();
		$manyMany = $instance->many_many();
		$dynamicRelations = self::get_dynamic_relations_for($className);

		$allRelations = array_merge($hasMany, $manyMany, $dynamicRelations);
		$relations = array();

		foreach ($allRelations as $relationName => $relationClassName) {
			if (singleton($relationClassName)->stat('api_access')) {
				$relations[] = $relationName;
			}
		}

		return $relations;
	}

	private static function get_dynamic_relations_for($className) {
		$apiAccess = singleton($className)->stat('api_access');

		if (!$apiAccess || !isset($apiAccess['dynamic_relations'])) {
			return array();
		}

		return $apiAccess['dynamic_relations'];
	}

	public static function get_available_relations_with_aliases_for($className) {
		$availableRelations = self::get_available_relations_for($className);
		$relationAliases = array_flip(self::get_relation_alias_map_for($className));

		foreach ($availableRelations as $key => $relation) {
			if (isset($relationAliases[$relation])) {
				$availableRelations[$key] = $relationAliases[$relation];
			}
		}

		return $availableRelations;
	}

}
