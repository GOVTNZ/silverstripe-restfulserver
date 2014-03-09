<?php

namespace RestfulServer;

abstract class Request {

	abstract public function outputResourceList();
	abstract public function outputResourceDetail();
	abstract public function outputRelationList();

}
