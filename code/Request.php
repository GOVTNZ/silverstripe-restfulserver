<?php

namespace RestfulServer;

use SS_HTTPRequest;

abstract class Request {

	protected $httpRequest = null;
	protected $formatter = null;

	public function __construct(SS_HTTPRequest $request, Formatter $formatter) {
		$this->httpRequest = $request;
		$this->formatter = $formatter;
	}

	abstract public function outputResourceList();
	abstract public function outputResourceDetail();
	abstract public function outputRelationList();

}
