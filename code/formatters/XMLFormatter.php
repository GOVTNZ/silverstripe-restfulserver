<?php

class XMLFormatter extends AbstractFormatter {

	protected $outputContentType = 'text/xml';

	protected function generateOutput($response) {
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$xml .= '<root>';

		$xml .= '<' . $this->pluralItemName . '>';

		foreach ($response[$this->pluralItemName] as $singleItem) {
			$xml .= '<' . $this->singularItemName . '>';

			foreach ($singleItem as $fieldName => $value) {
				$xml .= '<' . $fieldName . '>';
				$xml .= Convert::raw2xml($value);
				$xml .= '</' . $fieldName . '>';
			}

			$xml .= '</' . $this->singularItemName . '>';
		}

		$xml .= '</' . $this->pluralItemName . '>';

		$xml .= '<_metadata>';

		if (isset($response['_metadata'])) {
			foreach ($response['_metadata'] as $fieldName => $value) {
				$xml .= '<' . $fieldName . '>';
				$xml .= Convert::raw2xml($value);
				$xml .= '</' . $fieldName . '>';
			}
		}

		$xml .= '</_metadata>';

		return $xml . '</root>';
	}

}
