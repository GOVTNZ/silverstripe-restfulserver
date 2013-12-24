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

	private static function get_class_name_from_cache($endPoint) {
		return self::$alias_cache->load($endPoint);
	}

	private static function get_class_name_from_exact_match($endPoint) {
		if (!class_exists($endPoint)) {
			return false;
		}

		$apiAccess = singleton($endPoint)->stat('api_access');

		if (!$apiAccess) {
			return false;
		}

		self::$alias_cache->save($endPoint, $endPoint);
		return $endPoint;
	}

	private static function get_class_name_from_class_info($endPoint) {
		$dataClasses = ClassInfo::subclassesFor('DataObject');

		foreach ($dataClasses as $dataClass) {
			$apiAccess = singleton($dataClass)->stat('api_access');

			if (is_array($apiAccess) && isset($apiAccess['end_point_alias']) && $apiAccess['end_point_alias'] == $endPoint) {
				self::$alias_cache->save($dataClass, $endPoint);
				return $dataClass;
			}
		}

		return false;
	}

}
