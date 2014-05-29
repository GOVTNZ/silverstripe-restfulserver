<?php

namespace RestfulServer;

class SyncGoogleAnalyticsLogger implements RequestLogger {

	public function log(\SS_HTTPRequest $request) {
		$task = new SendAPIAnalytics();

		$getVars = $request->getVars();

		$getVars['dp'] = $request->getURL(true);
		$getVars['dt'] = $request->param('ResourceName');

		$newRequest = new \SS_HTTPRequest(
			$request->httpMethod(),
			$request->getURL(true),
			$getVars,
			$request->postVars(),
			$request->getBody()
		);

		$task->run($newRequest);
	}

}
