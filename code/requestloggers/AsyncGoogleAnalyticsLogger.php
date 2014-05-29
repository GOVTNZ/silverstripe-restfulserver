<?php

namespace RestfulServer;

class AsyncGoogleAnalyticsLogger implements RequestLogger {

	public function log(\SS_HTTPRequest $request) {
		$resourceName = escapeshellarg($request->param('ResourceName'));
		$url = escapeshellarg($request->getURL(true));

		exec('php cli-script.php dev/tasks/RestfulServer-SendAPIAnalytics dt=' . $resourceName . '\&dp=%2F' . $url . ' > /dev/null &');
	}

}
