<?php

interface Formatter {

	public function formatList(SS_List $list, $fields = null);
	public function setMetaData($metaData);
	public function getOutputContentType();
	public function setSingularItemName($name);
	public function setPluralItemName($name);

}