<?php

namespace MD0\ReGenerator\Traits;

use MD0\ReGenerator\Models\Reports as ReportModel;

trait CrudTrait
{
	public function getReports($property = null, $value = null)
	{
		if (in_array($property, $this->allowedProperties) && $value) {
			if (array_key_exists($property, $this->jsonColumns)) {
				$results = ReportModel::where(key($this->jsonColumns[$property]) . '->' . array_values($this->jsonColumns[$property])[0], 'like', '%' . $value . '%')->get()->toArray();
			} else {
				$results = ReportModel::where($property, 'like', '%' . $value . '%')->get()->toArray();
			}
			if (sizeof($results) > 0) {
				foreach ($results as $n => $result) {
					$reports[$n]['name'] = $result['name'];
					$reports[$n]['database'] = $result['db_name'];
					$reports[$n]['query'] = $result['sql_query'];
					$reports[$n]['title'] = $result['title'];
					$reports[$n]['reportType'] = $result['report_type'];
					$reports[$n]['numericCols'] = $result['formatting']['numeric'];
					$reports[$n]['thousandsSeparator'] = $result['formatting']['thousands'];
					$reports[$n]['decimalsSeparator'] = $result['formatting']['decimals'];
					$reports[$n]['countCols'] = isset($result['aggregates']['count']) ? $result['aggregates']['count'] : null;
					$reports[$n]['sumCols'] = isset($result['aggregates']['sum']) ? $result['aggregates']['sum'] : null;
					$reports[$n]['averageCols'] = isset($result['aggregates']['average']) ? $result['aggregates']['average'] : null;
					$reports[$n]['pdfPageSize'] = $result['pdf']['page_size'];
					$reports[$n]['pdfPageOrientation'] = $result['pdf']['page_orientation'];
					$reports[$n]['pdfFontSize'] = $result['pdf']['font_size'];
					$reports[$n]['pdfTemplate'] = $result['pdf']['pdf_template'];
					$reports[$n]['csvQuotes'] = $result['csv']['quotes'];
					$reports[$n]['csvDelimiter'] = $result['csv']['delimiter'];
					$reports[$n]['chartSeries'] = isset($result['chart']['series']) ? $result['chart']['series'] : null;
					$reports[$n]['chartLabels'] = isset($result['chart']['labels']) ? $result['chart']['labels'] : null;
					$reports[$n]['chartColors'] = $result['chart']['colors'];
					$reports[$n]['chartTemplate'] = $result['chart']['chart_template'];
					$reports[$n]['parameters'] = $result['parameters'];
				}
				return $reports;
			}
		} elseif ($results = ReportModel::all()->toArray()) return $results;
		return false;
	}

	function create($name, $query, $database = null, $parameters = null)
	{
		$dbParams = [];
		if (is_array($parameters)) {
			foreach ($parameters as $pName => $pValue) $dbParams[] = ['name' => $pName, 'value' => $pValue];
		}
		$this->db = [
			'name' => $name,
			'report_type' => $this->defaultReportType,
			'db_name' => isset($database) ? $database : $this->defaultDbName,
			'sql_query' => $query,
			'formatting' => [
				'numeric' => null,
				'thousands' => $this->defaultThousandsSeparator,
				'decimals' => $this->defaultDecimalsSeparator
			],
			'pdf' => [
				'page_size' => $this->defaultPdfPageSize,
				'page_orientation' => $this->defaultPdfPageOrientation,
				'font_size' => $this->defaultPdfFontSize,
				'pdf_template' => $this->defaultPdfTemplate,
			],
			'csv' => [
				'delimiter' => $this->defaultCsvDelimiter,
				'quotes' => $this->defaultCsvQuotes,
			],
			'chart' => [
				'series' => null,
				'labels' => null,
				'type' => $this->defaultChartType,
				'colors' => $this->defaultChartColors,
				'chart_template' => $this->defaultChartTemplate,
			],
			'parameters' => $dbParams
		];
		if (ReportModel::create($this->db)) {
			$this->setReport($name);
			return $this;
		}
		return false;
	}

	function update($data, $singleValue = null)
	{
		if (!$this->name) return false;

		if (is_string($data) && is_string($singleValue)) {
			if (array_key_exists($data, $this->db)) {
				$this->db[$data] = $singleValue;
			} elseif (array_key_exists($data, $this->jsonColumns)) {
				$this->db[key($this->jsonColumns[$data])][array_values($this->jsonColumns[$data])[0]] = $singleValue;
			}
		}
		if (is_array($data)) {
			foreach ($data as $property => $value) {
				if (array_key_exists($property, $this->db)) {
					$this->db[$property] = $value;
				} elseif (array_key_exists($property, $this->jsonColumns)) {
					$this->db[key($this->jsonColumns[$property])][array_values($this->jsonColumns[$property])[0]] = $value;
				}
			}
		}
		unset($this->db['created_at']);
		unset($this->db['updated_at']);
		if (ReportModel::where('name', '=', $this->name)->update($this->db)) {
			$this->setReport($this->name);
			return $this;
		}
		return false;
	}

	function delete()
	{
		if (!isset($this->name)) return false;
		if (ReportModel::where('name', '=', $this->name)->delete()) return true;
		return false;
	}
}
