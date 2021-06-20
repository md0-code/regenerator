<?php

namespace MD0\ReGenerator\Traits;

trait HtmlTrait
{
	function getHtml()
	{
		if (is_array($this->content)) {
			if (sizeof($this->content) > 0) {
				if ($this->reportType == 'vertical') {
					$t = '<div class="table-responsive">' . PHP_EOL;
					$t .= '<table cellspacing="0" class="table table-hover table-striped">' . PHP_EOL;
					$t .= '<thead><tr>' . PHP_EOL;
					foreach ($this->content[0] as $label => $column) {
						$t .= '<th><b>' . $label . '</b></th>';
					}
					$t .= '</tr></thead>' . PHP_EOL;
					$t .= '<tbody>' . PHP_EOL;
					for ($y = 0; $y < count($this->content); $y++) {
						$stripeClass = ($y & 1) ? 'odd' : 'even';
						$t .= '<tr class="' . $stripeClass . '">';
						for ($x = 0; $x < count($this->content[0]); $x++) {
							if ($this->numericCols && in_array($x + 1, $this->numericCols)) $t .= '<td align="right" class="text-right">' . $this->_numberFormat($this->content[$y][array_keys($this->content[$y])[$x]], $this->thousandsSeparator, $this->decimalsSeparator) . '</td>';
							else
								$t .= '<td>' . $this->content[$y][array_keys($this->content[$y])[$x]] . '</td>';
						}
						$t .= '</tr>' . PHP_EOL;
					}
					$t .= '</tbody>' . PHP_EOL;

					$counts = $this->countCols ? $this->_getAggregate($this->content, $this->countCols, 'count') : null;
					$sums = $this->sumCols ? $this->_getAggregate($this->content, $this->sumCols, 'sum') : null;
					$averages = $this->averageCols ? $this->_getAggregate($this->content, $this->averageCols, 'average') : null;

					if ((isset($counts) && count($counts) > 0) || (isset($sums) && count($sums) > 0) || (isset($averages) && count($averages) > 0)) {
						$t .= '<tfoot><tr>' . PHP_EOL;
						for ($x = 0; $x < count($this->content[0]); $x++) {
							$t .= '<td align="right" class="text-right"><strong>';
							if (isset($counts[$x])) $t .= __('Records') . ': ' . $this->_numberFormat($counts[$x], $this->thousandsSeparator, $this->decimalsSeparator) . '<br>';
							if (isset($sums[$x])) $t .= __('Total') . ': ' . $this->_numberFormat($sums[$x], $this->thousandsSeparator, $this->decimalsSeparator) . '<br>';
							if (isset($averages[$x])) $t .= __('Average') . ': ' . $this->_numberFormat($averages[$x], $this->thousandsSeparator, $this->decimalsSeparator);
							$t .= '</strong></td>' . PHP_EOL;
						}
						$t .= '</tr></tfoot>' . PHP_EOL;
					}
					$t .= '</table>' . PHP_EOL;
					$t .= '</div>' . PHP_EOL;
				} elseif ($this->reportType == 'horizontal') {
					$t = '<div class="table-responsive">' . PHP_EOL;
					$t .= '<table class="table table-hover table-striped">' . PHP_EOL;
					$t .= '<tbody>' . PHP_EOL;
					for ($x = 0; $x < count($this->content[0]); $x++) {
						$stripeClass = ($x & 1) ? 'odd' : 'even';
						$t .= '<tr class="' . $stripeClass . '">' . PHP_EOL;
						$t .= '<td width="20%" class="header"><strong>' . array_keys($this->content[0])[$x] . '</strong></td>';
						for ($y = 0; $y < count($this->content); $y++) {
							if ($numericCols && in_array($x + 1, $numericCols)) $t .= '<td align="right" class="text-right">' . $this->_numberFormat($this->content[$y][array_keys($this->content[$y])[$x]], $this->thousandsSeparator, $this->decimalsSeparator) . '</td>';
							else $t .= '<td class="' . $stripeClass . '">' . $this->content[$y][array_keys($this->content[$y])[$x]] . '</td>';
						}
						$t .= '</tr>' . PHP_EOL;
					}
					$t .= '</tbody>' . PHP_EOL;
					$t .= '</table>' . PHP_EOL;
					$t .= '</div>' . PHP_EOL;
				} elseif ($this->reportType == 'slices') {
					$t = '';
					for ($y = 0; $y < count($this->content); $y++) {
						$t .= '<div class="table-responsive">' . PHP_EOL;
						$t .= '<table class="table table-hover table-striped">' . PHP_EOL;
						$t .= '<tbody>' . PHP_EOL;
						for ($x = 0; $x < count($this->content[0]); $x++) {
							$stripeClass = ($x & 1) ? 'odd' : 'even';
							$t .= '<tr class="' . $stripeClass . '">' . PHP_EOL;
							$t .= '<td width="20%" class="header"><strong>' . array_keys($this->content[$y])[$x] . '</strong></td>';
							$t .= '<td width="80%">' . $this->content[$y][array_keys($this->content[$y])[$x]] . '</td>';
							$t .= '</tr>' . PHP_EOL;
						}
						$t .= '</tbody>' . PHP_EOL;
						$t .= '</table>' . PHP_EOL;
						$t .= '</div>' . PHP_EOL;
						if ($y !== count($this->content) - 1) $t .= '<hr style="margin-bottom: 30px">' . PHP_EOL;
					}
				}
				if (isset($t)) return $t;
			} else return false;
		} else return $this->content;
	}
}
