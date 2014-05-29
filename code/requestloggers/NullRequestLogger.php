<?php

namespace RestfulServer;

class NullRequestLogger implements RequestLogger {

	public function log(\SS_HTTPRequest $request) {
		// do nothing
	}

}
