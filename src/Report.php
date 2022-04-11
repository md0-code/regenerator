<?php

namespace MD0\ReGenerator;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use MD0\ReGenerator\Models\Reports as ReportModel;
use MD0\ReGenerator\Traits\CrudTrait;
use MD0\ReGenerator\Traits\ChartTrait;
use MD0\ReGenerator\Traits\PdfTrait;
use MD0\ReGenerator\Traits\CsvTrait;
use MD0\ReGenerator\Traits\HtmlTrait;

class Report
{
	use CrudTrait, HtmlTrait, PdfTrait, CsvTrait, ChartTrait;

	private $allowedProperties;
	private $jsonColumns;
	
	private $db;
	private $content;

	private $defaultReportType;
	private $defaultDbName;
	private $defaultThousandsSeparator;
	private $defaultDecimalsSeparator;
	private $defaultPdfPageSize;
	private $defaultPdfPageOrientation;
	private $defaultPdfFontSize;
	private $defaultPdfTemplate;
	private $defaultCsvDelimiter;
	private $defaultCsvQuotes;
	private $defaultChartType;
	private $defaultChartColors;
	private $defaultChartTemplate;

	function __construct()
	{
		$this->allowedProperties = ['name', 'database', 'query', 'title', 'reportType', 'tag', 'numericCols', 'countCols', 'sumCols', 'averageCols', 'thousandsSeparator', 'decimalsSeparator', 'pdfPageSize', 'pdfPageOrientation', 'pdfFontSize', 'pdfTemplate', 'csvQuotes', 'csvDelimiter', 'chartType', 'chartSeries', 'chartLabels', 'chartColors', 'chartTemplate', 'parameters'];

		$this->jsonColumns = [
			'numericCols' => ['formatting' => 'numeric'],
			'thousandsSeparator' => ['formatting' => 'thousands'],
			'decimalsSeparator' => ['formatting' => 'decimals'],
			'countCols' => ['aggregates' => 'count'],
			'sumCols' => ['aggregates' => 'sum'],
			'averageCols' => ['aggregates' => 'average'],
			'pdfPageSize' => ['pdf' => 'page_size'],
			'pdfPageOrientation' => ['pdf' => 'page_orientation'],
			'pdfFontSize' => ['pdf' => 'font_size'],
			'pdfTemplate' => ['pdf' => 'pdf_template'],
			'csvQuotes' => ['csv' => 'quotes'],
			'csvDelimiter' => ['csv' => 'delimiter'],
			'chartType' => ['chart' => 'type'],
			'chartSeries' => ['chart' => 'series'],
			'chartLabels' => ['chart' => 'labels'],
			'chartColors' => ['chart' => 'colors'],
			'chartTemplate' => ['chart' => 'chart_template'],
		];

		$this->defaultReportType = config('md0.regenerator.defaults.report_type') ? config('md0.regenerator.defaults.report_type') : 'vertical';
		$this->defaultDbName = config('md0.regenerator.defaults.db_name') ? config('md0.regenerator.defaults.db_name') : config('database.default');
		$this->defaultThousandsSeparator = config('md0.regenerator.defaults.thousands_separator') ? config('md0.regenerator.defaults.thousands_separator') : '.';
		$this->defaultDecimalsSeparator = config('md0.regenerator.defaults.decimals_separator') ? config('md0.regenerator.defaults.decimals_separator') : ',';
		$this->defaultPdfPageSize = config('md0.regenerator.defaults.pdf_page_size') ? config('md0.regenerator.defaults.pdf_page_size') : 'A4';
		$this->defaultPdfPageOrientation = config('md0.regenerator.defaults.pdf_page_orientation') ? config('md0.regenerator.defaults.pdf_page_orientation') : 'portrait';
		$this->defaultPdfFontSize = config('md0.regenerator.defaults.pdf_font_size') ? config('md0.regenerator.defaults.pdf_font_size') : 10;
		$this->defaultPdfTemplate = config('md0.regenerator.defaults.pdf_template') ? config('md0.regenerator.defaults.pdf_template') : 'md0.regenerator::pdf.report';
		$this->defaultCsvDelimiter = config('md0.regenerator.defaults.csv_delimiter') ? config('md0.regenerator.defaults.csv_delimiter') : ',';
		$this->defaultCsvQuotes = config('md0.regenerator.defaults.csv_quotes') ? config('md0.regenerator.defaults.csv_quotes') : '';
		$this->defaultChartType = config('md0.regenerator.defaults.chart_type') ? config('md0.regenerator.defaults.chart_type') : 'line';
		$this->defaultChartColors = config('md0.regenerator.defaults.chart_colors') ? config('md0.regenerator.defaults.chart_colors') : '#0c2d5c, #4a3771, #7f3e7b, #b0467a, #d9566f, #f5725d. #ff974a, #ffc03d';
		$this->defaultChartTemplate = config('md0.regenerator.defaults.chart_template') ? config('md0.regenerator.defaults.chart_template') : 'md0.regenerator::chart.report';
		$this->_instantiateDefaults();
	}

	private function _instantiateDefaults()
	{
		$this->reportType = $this->defaultReportType;
		$this->title = '';
		$this->numericCols = null;
		$this->thousandsSeparator = $this->defaultThousandsSeparator;
		$this->decimalsSeparator =  $this->defaultDecimalsSeparator;
		$this->countCols = null;
		$this->sumCols = null;
		$this->averageCols = null;
		$this->pdfPageSize = $this->defaultPdfPageSize;
		$this->pdfPageOrientation = $this->defaultPdfPageOrientation;
		$this->pdfFontSize = $this->defaultPdfFontSize;
		$this->pdfTemplate = $this->defaultPdfTemplate;
		$this->csvQuotes = $this->defaultCsvQuotes;
		$this->csvDelimiter = $this->defaultCsvDelimiter;
		$this->chartType = $this->defaultChartType;
		$this->chartColors = $this->defaultChartColors;
		$this->chartTemplate = $this->defaultChartTemplate;
		$this->database = $this->defaultDbName;
		$this->content = '';
		$this->parameters = null;
	}

	private function _parseReport($name)
	{
		$this->db = ReportModel::where('name', '=', $name)->firstOrFail()->toArray();
		if (!isset($this->parameters)) foreach ($this->db['parameters'] as $parameter) $this->parameters[$parameter['name']] = $parameter['value'];
		$this->query = $this->_parseContent($this->db['sql_query'], $this->parameters);
		$this->title = $this->_parseContent($this->db['title'], $this->db['title']);
		$this->reportType = $this->db['report_type'] ? $this->db['report_type'] : $this->defaultReportType;
		$this->tag = isset($this->db['tag']) ? $this->db['tag'] : null;
		$this->numericCols = isset($this->db['formatting']['numeric']) ? explode(',', $this->db['formatting']['numeric']) : null;
		$this->thousandsSeparator = $this->db['formatting']['thousands'] ? $this->db['formatting']['thousands'] : $this->defaultThounsandsSeparator;
		$this->decimalsSeparator = $this->db['formatting']['decimals'] ? $this->db['formatting']['decimals'] : $this->defaultDecimalsSeparator;
		$this->countCols = isset($this->db['aggregates']['count']) ? explode(',', $this->db['aggregates']['count']) : null;
		$this->sumCols = isset($this->db['aggregates']['sum']) ? explode(',', $this->db['aggregates']['sum']) : null;
		$this->averageCols = isset($this->db['aggregates']['average']) ? explode(',', $this->db['aggregates']['average']) : null;
		$this->pdfPageSize = $this->db['pdf']['page_size'] ? $this->db['pdf']['page_size'] : $this->defaultPdfPageSize;
		$this->pdfPageOrientation = $this->db['pdf']['page_orientation'] ? $this->db['pdf']['page_orientation'] : $this->defaultPdfPageOrientation;
		$this->pdfFontSize = $this->db['pdf']['font_size'] ? $this->db['pdf']['font_size'] : $this->defaultPdfFontSize;
		$this->pdfTemplate = $this->db['pdf']['pdf_template'] ? $this->db['pdf']['pdf_template'] : $this->defaultPdfTemplate;
		$this->csvQuotes = $this->db['csv']['quotes'] ? $this->db['csv']['quotes'] : $this->defaultCsvQuotes;
		$this->csvDelimiter = $this->db['csv']['delimiter'] ? $this->db['csv']['delimiter'] : $this->defaultCsvDelimiter;
		$this->chartType = $this->db['chart']['type'] ? $this->db['chart']['type'] : $this->defaultChartType;
		$this->chartSeries = $this->db['chart']['series'] ? explode(',', $this->db['chart']['series']) : null;
		$this->chartLabels = $this->db['chart']['labels'] ? $this->db['chart']['labels'] : null;
		$this->chartColors = $this->db['chart']['colors'] ? $this->db['chart']['colors'] : $this->defaultChartColors;
		$this->chartTemplate = $this->db['chart']['chart_template'] ? $this->db['chart']['chart_template'] : $this->defaultChartTemplate;
		$this->database = $this->db['db_name'] ? $this->db['db_name'] : $this->defaultDbName;
		try {
			$this->content = json_decode(json_encode(DB::connection($this->database)->select($this->query)), true);
		} catch (\Illuminate\Database\QueryException $exception) {
			$this->content = (config('app.debug') == true) ? '<div class="alert alert-danger">' . $exception->getMessage() . '</div>' : '';
		}
		return $this;
	}

	private function _parseContent($content, $parameters)
	{
		$content = htmlspecialchars_decode($content, ENT_QUOTES);
		preg_match_all("~\{\{\s*(.*?)\s*\}\}~", $content, $fields);
		if (is_array($parameters) && !empty($parameters)) {
			foreach ($fields[0] as $n => $field) {
				$content = str_replace($field, $parameters[$fields[1][$n]], $content);
			}
		}
		return $content;
	}

	private function _getAggregate($array, $columns, $type)
	{
		$array = json_decode(json_encode($array), true);
		$numCols = count($array[0]);
		$numRows = count($array);
		foreach ($columns as $column) {
			if (!is_numeric($column) || $column - 1 > $numCols) $columns = array_diff($columns, [$column]);
		}
		$cols = array();
		if (is_array($columns)) {
			for ($c = 0; $c < count($columns); $c++) {
				$currentCol = $columns[$c] - 1;
				for ($x = 0; $x < $numRows; $x++) {
					for ($y = 0; $y < $numCols; $y++) {
						$rows = array_values($array[$x]);
						$selectedCols[$x] = $rows[$currentCol];
					}
				}
				switch ($type) {
					case 'count':
						$cols[$currentCol] = $numRows;
						break;
					case 'sum':
						$cols[$currentCol] = array_sum($selectedCols);
						break;
					case 'average':
						$cols[$currentCol] = array_sum($selectedCols) / $numRows;
						break;
				}
			}
		}
		return $cols;
	}

	private function _numberFormat($number, $thousands, $decimals)
	{
		if (Str::contains($number, '.') && Str::contains($number, ',')) return $number;
		$pos = strpos($number, '.') > 0 ? strpos($number, '.') : null;
		if (null == $pos) $pos = strpos($number, ',') > 0 ? strpos($number, ',') : null;
		if ($pos > 0) {
			try {
				$formattedNumber = number_format($number, 2, $decimals, $thousands);
			} catch (\ErrorException $e) {
				$formattedNum = $number;
			}
		}
		try {
			$formattedNum = number_format($number, 0, $decimals, $thousands);
		} catch (\ErrorException $e) {
			$formattedNum = $number;
		}
		return $formattedNum;
	}

	public function setReport($name)
	{
		$this->name = $name;
		if (!$this->_parseReport($name)) return false;
		return $this;
	}

	public function set($property, $value)
	{
		if (in_array($property, $this->allowedProperties)) {
			if ($property !== 'parameters') $this->{$property} = $value;
			if ($property == 'parameters' && isset($this->name)) {
				$this->parameters = array_merge($this->parameters, $value);
				$this->query = $this->_parseContent($this->db['sql_query'], $this->parameters);
			}
			if (substr($property, -4, 4) == 'Cols' || $property == 'chartSeries') $this->{$property} = explode(',', $value);
			if ($property == 'query' || $property == 'parameters') {
				$database = isset($this->database) ? $this->database : config('database.default');
				try {
					$this->content = json_decode(json_encode(DB::connection($database)->select($this->query)), true);
				} catch (\Illuminate\Database\QueryException $exception) {
					$this->content = (config('app.debug') == true) ? '<div class="alert alert-danger">' . $exception->getMessage() . '</div>' : '';
				}
			}
		}
		return $this;
	}

	function getValue($x, $y)
	{
		if (is_array($this->content)) {
			if (in_array($y - 1, array_keys($this->content)) and array_key_exists($x - 1, array_keys($this->content[$y - 1]))) return array_values($this->content[$y - 1])[$x - 1];
			return false;
		}
		return false;
	}

	function getArray()
	{
		if (is_array($this->content)) {
			return $this->content;
		}
		return [0 => $this->content];
	}
}
