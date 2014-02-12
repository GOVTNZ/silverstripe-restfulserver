<?php

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

		// couldn't find class name for end point
		return false;
	}

	private static function initialise_resource_name_cache() {
		self::$alias_cache = SS_Cache::factory(self::RESOURCE_NAME_CACHE_KEY);
	}

	private static function get_class_name_from_cache($resourceName) {
		try {
			$cacheValue = self::$alias_cache->load($resourceName);
		} catch (Zend_Cache_Exception $exception) {
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
		} catch (Zend_Cache_Exception $exception) {
			// not sure if we should do this or just allow uncached results...
			user_error('The ' . $resourceName . ' DataObject has an invalid class name. Must only use: [a-zA-Z0-9_]');
		}

		return $resourceName;
	}

	private static function get_class_name_from_class_info($resourceName) {
		$dataClasses = ClassInfo::subclassesFor('DataObject');

		foreach ($dataClasses as $className) {
			$apiAccess = singleton($className)->stat('api_access');

			if (
				is_array($apiAccess) &&
				isset($apiAccess['end_point_alias']) &&
				$apiAccess['end_point_alias'] == $resourceName
			) {
				try {
					self::$alias_cache->save($className, $resourceName);
				} catch (Zend_Cache_Exception $exception) {
					$message  = 'The ' . $className;
					$message .=' DataObject has an invalid end_point_alias value. Must only use: [a-zA-Z0-9_]';
					user_error($message);
				}

				return $className;
			}
		}

		return false;
	}

	public static function get_dataobject_field_alias_map($className) {
		$fields = array();

		$fields['id'] = 'ID';

		foreach (DataObject::custom_database_fields($className) as $fieldName => $fieldType) {
			$fields[strtolower($fieldName)] = $fieldName;
		}

		return $fields;
	}

	public static function class_can_be_filtered_by($className, $fieldName) {
		$validFields = array_keys(singleton($className)->inheritedDatabaseFields());

		if (in_array($fieldName, $validFields)) {
			return true;
		}

		return false;
	}

	public static function get_relation_method_from_name($className, $relationName) {
		$apiAccess = singleton($className)->stat('api_access');

		if (!self::class_has_relation_aliases($className) && self::class_has_relation($className, $relationName)) {
			return $relationName;
		}

		if (isset($apiAccess['relation_aliases'][$relationName])) {
			return $apiAccess['relation_aliases'][$relationName];
		}

		return null;
	}

	private static function class_has_relation_aliases($className) {
		$apiAccess = singleton($className)->stat('api_access');

		return is_array($apiAccess) && isset($apiAccess['relation_aliases']) && is_array($apiAccess['relation_aliases']);
	}

	private static function class_has_relation($className, $relationName) {
		$instance = singleton($className);

		return (($instance->has_many($relationName) !== false) || (!is_null($instance->many_many($relationName))));
	}

}
