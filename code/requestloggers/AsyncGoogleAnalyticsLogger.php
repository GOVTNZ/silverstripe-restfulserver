<?php

namespace RestfulServer;

class AsyncGoogleAnalyticsLogger implements RequestLogger {

	public function log(\SS_HTTPRequest $request) {
		$resourceName = $request->param('ResourceName') . ' endpoint';
		$url = $request->getURL(true);

		$queryString = escapeshellarg('dt=' . $resourceName . '&dp=' . urlencode($url));

		exec('php cli-script.php dev/tasks/RestfulServer-SendAPIAnalytics ' . $queryString . ' > /dev/null &');
	}

}
