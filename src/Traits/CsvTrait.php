<?php

namespace MD0\ReGenerator\Traits;

use Dompdf\Dompdf;

trait CsvTrait
{
	function getCSV()
	{
		if (is_array($this->content)) {
			if (sizeof($this->content) > 0) {
				$header = array_keys($this->content[0]);
				if ($this->csvQuotes) array_walk($header, function (&$x) {
					$x = "\"$x\"";
				});
				$csvFile = implode($this->csvDelimiter, $header) . "\n";
				foreach ($this->content as $line) {
					$line = array_values($line);
					if ($this->csvQuotes) array_walk($line, function (&$x) {
						$x = "\"$x\"";
					});
					$csvLine = implode($this->csvDelimiter, $line);
					$csvFile .= $csvLine . "\n";
				}
				return $csvFile;
			}
			return false;
		}
		return strip_tags($this->content);
	}
}
