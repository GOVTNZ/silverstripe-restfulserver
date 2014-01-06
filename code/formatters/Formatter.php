<?php

interface Formatter {

	public function setResultsList(SS_List $list, $fields = null);
	public function format();
	public function setExtraData($extraData);
	public function getOutputContentType();
	public function setSingularItemName($name);
	public function setPluralItemName($name);

}