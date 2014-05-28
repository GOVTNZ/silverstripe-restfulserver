<?php

namespace RestfulServer;

interface RequestLogger {

	public function log(\SS_HTTPRequest $request);

}
