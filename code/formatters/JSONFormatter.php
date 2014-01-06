<?php

class JSONFormatter extends AbstractFormatter {

	protected $outputContentType = 'application/json';

	protected function generateOutput($response) {
		if (phpversion() && phpversion() >= 5.4) {
			return json_encode($response, JSON_PRETTY_PRINT);
		} else {
			return $this->jsonFormat($response);
		}
	}

	// taken from: https://github.com/GerHobbelt/nicejson-php
	private function jsonFormat($json) {
		$json = json_encode($json);

		$result = '';
		$pos = 0; // indentation level
		$strLen = strlen($json);
		$indentStr = "    ";
		$newLine = "\n";
		$prevChar = '';
		$outOfQuotes = true;

		for ($i = 0; $i < $strLen; $i++) {
			// Grab the next character in the string
			$char = substr($json, $i, 1);

			// Are we inside a quoted string?
			if ($char == '"' && $prevChar != '\\') {
				$outOfQuotes = !$outOfQuotes;
			} // If this character is the end of an element,
			// output a new line and indent the next line
			else if (($char == '}' || $char == ']') && $outOfQuotes) {
				$result .= $newLine;
				$pos--;
				for ($j = 0; $j < $pos; $j++) {
					$result .= $indentStr;
				}
			} else if ($outOfQuotes && false !== strpos(" \t\r\n", $char)) {
				// eat all non-essential whitespace in the input as we do our own
				// here and it would only mess up our process
				continue;
			}

			// Add the character to the result string
			$result .= $char;
			// always add a space after a field colon:
			if ($char == ':' && $outOfQuotes) {
				$result .= ' ';
			}

			// If the last character was the beginning of an element,
			// output a new line and indent the next line
			if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
				$result .= $newLine;
				if ($char == '{' || $char == '[') {
					$pos++;
				}
				for ($j = 0; $j < $pos; $j++) {
					$result .= $indentStr;
				}
			}
			$prevChar = $char;
		}

		return $result;
	}

}