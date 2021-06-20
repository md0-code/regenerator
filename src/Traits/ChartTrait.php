<?php

namespace MD0\ReGenerator\Traits;

trait ChartTrait
{
	function getChart()
	{
		$chart = app()->make('stdClass');
		$chart->series = isset($this->chartSeries) ? $this->chartSeries : null;
		$chart->labels = isset($this->chartLabels) ? $this->chartLabels : null;
		$chart->type = $this->chartType ? $this->chartType : null;
		$content = $this->content;
		if (!is_array($content)) return $content;
		if ($chart->series && $chart->labels && $chart->type && $content) {
			foreach ($content as $line) {
				$lineKeys = array_keys($line);
				$lineValues = array_values($line);
				foreach ($chart->series as $pos => $series) {
					$chart->seriesData[$pos][] = $lineValues[$chart->series[$pos] - 1];
					$chart->yLegend[$pos] = $lineKeys[$chart->series[$pos] - 1];
				}
				if (isset($lineValues[$chart->labels - 1])) {
					$chart->labelsData[] = $lineValues[$chart->labels - 1];
					$chart->xLegend = $lineKeys[$chart->labels - 1];
				}
			}
			$chart->borders = explode(',', str_replace(' ', '', $this->chartColors));
			$datasets = '';
			$datasets .= 'var datasets = [';
			foreach ($chart->seriesData as $item => $row) {
				list($r, $g, $b) = sscanf($chart->borders[$item], "#%02x%02x%02x");
				$backgroundColor = ($chart->type == 'pie') ? json_encode($chart->borders) : '"rgba(' . $r . ',' . $g . ',' . $b . ',0.9)"';
				$datasets .= '
					{
						label: ' . json_encode($chart->yLegend[$item]) . ',
						data: ' . json_encode($row, JSON_NUMERIC_CHECK) . ',
						backgroundColor: ' . $backgroundColor . ',
						hoverBackgroundColor: ' . $backgroundColor . ',
						borderColor: "' . $chart->borders[$item] . '",
						borderWidth: 1
					},
					';
			}
			$datasets .= ']';
			$data['name'] = isset($this->name) ? $this->name : rand(10000, 99999);
			$data['chartType'] = $chart->type;
			$data['xLegend'] = $chart->xLegend;
			$data['labels'] = json_encode($chart->labelsData);
			$data['datasets'] = $datasets;
			return view($this->chartTemplate, $data)->render();
		}
		return false;
	}
}
