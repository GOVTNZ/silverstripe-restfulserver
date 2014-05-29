<?php

namespace RestfulServer;

use RestfulService, Config;

class SendAPIAnalytics extends \BuildTask {

	protected $title = 'Send a page view to Google Analytics';
	protected $description = 'Used by the Sync & AsyncGoogleAnalyticsLogger in RestfulServer';

	public function run($request) {
		if (defined('SS_OUTBOUND_PROXY')) {
			Config::inst()->update('RestfulService', 'default_curl_options', array(
				CURLOPT_PROXY => SS_OUTBOUND_PROXY,
				CURLOPT_PROXYPORT => SS_OUTBOUND_PROXY_PORT
			));
		}

		$restfulService = new RestfulService('https://ssl.google-analytics.com/collect?payload_data', 0);
		$webPropertyId = Config::inst()->get('RestfulServer\ControllerV2', 'google_analytics_web_property_id');

		$data = array(
			'v' => '1',
			'tid' => $webPropertyId,
			'cid' => '555',
			't' => 'pageview',
			'dt' => $request->getVar('dt'),
			'dp' => $request->getVar('dp')
		);

		$restfulService->setQueryString($data);
		$restfulService->request();
	}

}
