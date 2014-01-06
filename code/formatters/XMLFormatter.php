<?php

class XMLFormatter extends AbstractFormatter {

	protected $outputContentType = 'application/xml';

	protected function generateOutput($response) {
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$xml .= '<root>';

		if (isset($response[$this->pluralItemName])) {
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
		}

		foreach ($response as $key => $value) {
			if ($key === $this->pluralItemName) {
				continue;
			}

			$xml .= $this->generateXML($key, $value);
		}

		return $xml . '</root>';
	}

	private function generateXML($key, $value) {
		$xml = '<' . $key . '>';

		if (is_array($value)) {
			foreach ($value as $otherKey => $otherValue) {
				$xml .= $this->generateXML($otherKey, $otherValue);
			}
		} else {
			$xml .= Convert::raw2xml($value);
		}

		$xml .= '</' . $key . '>';

		return $xml;
	}

}
