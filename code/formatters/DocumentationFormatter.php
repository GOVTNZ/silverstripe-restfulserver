<?php

namespace RestfulServer;

class DocumentationFormatter extends AbstractFormatter implements Formatter {

	protected $outputContentType = 'text/html';

	public function format() {
		$output = $this->extraData[0]['userMessage'] . '<br>';
		$output .= $this->extraData[0]['developerMessage'] . '<br>';
		$output .= '<a href="' . $this->extraData[0]['moreInfo'] . '">More information</a><br>';

		return $output;
	}

}
