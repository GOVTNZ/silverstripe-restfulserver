<?php

class XMLFormatter extends AbstractFormatter {

	protected $outputContentType = 'application/xml';

	public function format() {
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$xml .= '<root>';

		foreach ($this->resultsSets as $resultSet) {
			$xml .= '<' . $resultSet['pluralName'] . '>';

			foreach ($resultSet['set'] as $resultItem) {
				$xml .= $this->generateXML($resultSet['singularName'], $resultItem);
			}

			$xml .= '</' . $resultSet['pluralName'] . '>';
		}

		foreach ($this->extraData as $data) {
			foreach ($data as $key => $value) {
				$xml .= $this->generateXML($key, $value);
			}
		}

		return $xml . '</root>';

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
