<?php

class RestfulServerV2 extends RestfulServer {

	const ALIAS_CACHE_KEY = 'restfulserver_api_end_point_cache';

	private $aliasCache = null;

	public function index() {
		$endPoint = $this->request->param('ClassName');

		if (!$endPoint) {
			echo 'Base documentation not yet implemented.';
			exit();
		}

		$className = $this->getDataClass($endPoint);

		if ($className === false) {
			$this->httpError(500, 'Something went wrong');
		}

		$this->processResults($className);
	}

	private function getDataClass($endPoint) {
		$this->initialiseEndPointCache();

		// first try and retrieve class name from cache
		$dataClass = $this->getDataClassFromCache($endPoint);

		if ($dataClass !== false) {
			return $dataClass;
		}

		// second try and retrieve class name by exact match (end point == class name)
		$dataClass = $this->getDataClassFromExactMatch($endPoint);

		if ($dataClass !== false) {
			return $dataClass;
		}

		// third try and look up end point alias using ClassInfo
		$dataClass = $this->getDataClassFromClassInfo($endPoint);

		if ($dataClass !== false) {
			return $dataClass;
		}

		// couldn't find end point
		return false;
	}

	private function initialiseEndPointCache() {
		$this->aliasCache = SS_Cache::factory(self::ALIAS_CACHE_KEY);
	}

	private function getDataClassFromCache($endPoint) {
		return $this->aliasCache->load($endPoint);;
	}

	private function getDataClassFromExactMatch($endPoint) {
		if (!class_exists($endPoint)) {
			return false;
		}

		$apiAccess = singleton($endPoint)->stat('api_access');

		if (!$apiAccess) {
			return false;
		}

		$this->aliasCache->save($endPoint, $endPoint);
		return $endPoint;
	}

	private function getDataClassFromClassInfo($endPoint) {
		$dataClasses = ClassInfo::subclassesFor('DataObject');

		foreach ($dataClasses as $dataClass) {
			$apiAccess = singleton($dataClass)->stat('api_access');

			if (is_array($apiAccess) && isset($apiAccess['end_point_alias']) && $apiAccess['end_point_alias'] == $endPoint) {
				$this->aliasCache->save($dataClass, $endPoint);
				return $dataClass;
			}
		}

		return false;
	}

	private function processResults($className) {
		return 'End point found in ' . $className;
	}

}
