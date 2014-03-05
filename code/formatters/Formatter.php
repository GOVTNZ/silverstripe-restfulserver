<?php

namespace RestfulServer;

interface Formatter {

	public function format();
	public function getOutputContentType();
	public function addResultsSet($set, $singularName, $pluralName);
	public function addExtraData($data);
	public function clearData();

}