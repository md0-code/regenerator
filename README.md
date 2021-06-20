# ReGenerator for Laravel

**A quick report generator for Laravel based on SQL queries.**

This is a tool intended for site/app admins that need to quickly create decent looking reports for display or export using only SQL select statements and a few parameters. Uses Bootstrap 3/4 classes for formatting, but can also output to an array if you need to customize the final look.

This package does not include a GUI for managing the reports. Please check out [ReGenerator for Backpack](https://github.com/md0-code/backpack-regenerator) if you need to edit reports from the admin interface.

Should work on any version of Laravel above 5.6, but was only tested on v8.

- [ReGenerator for Laravel](#regenerator-for-laravel)
	- [Installation](#installation)
		- [Optional](#optional)
	- [Usage](#usage)
	- [Getting started](#getting-started)
	- [Reference](#reference)
		- [Properties](#properties)
	- [Overwriting](#overwriting)
		- [Changinng the default parameters](#changinng-the-default-parameters)
		- [Customizing the PDF layout](#customizing-the-pdf-layout)
		- [Customizing the chart options](#customizing-the-chart-options)
	- [Errors & Suggestions](#errors--suggestions)
	- [License](#license)
	- [Acknowledgements](#acknowledgements)

## Installation
Require the package via Composer:
```bash
composer require md0/reportgenerator 
```
Run the database migrations:
```bash
php artisan migrate
```
### Optional
Publish the assets (only needed for charts):
```bash
php artisan vendor:publish --provider="MD0\ReGenerator\ReGeneratorServiceProvider" --tag="assets"
```
Publish the configuration file (to modify the default settings for new reports):
```bash
php artisan vendor:publish --provider="MD0\ReGenerator\ReGeneratorServiceProvider" --tag="config"
```
Publish the views (to modify the chart options and/or PDF headers):
```bash
php artisan vendor:publish --provider="MD0\ReGenerator\ReGeneratorServiceProvider" --tag="views"
```
Publish the language files. There are only three translatable strings (for the aggregate functions) so you can either edit them inside *resources/lang/vendor/regenerator* folder or just add them to your main translation file.
```bash
provider="MD0\ReGenerator\ReGeneratorServiceProvider" --tag="lang"
```
## Usage
To use ReGenerator you can either:
1. use the class dinamically
```php
use MD0\ReGenerator\Report;
class MyClass {
function report() {
	$report = new Report;
	$myReport = $report->setReport('my_report')->getHtml();
	}
}
```
2. use the **Report** facade
```php
use MD0\ReGenerator\Facades\Report;
class MyClass {
	function report() {
		Report::setReport('my_report')->getHtml();
	}
}
```
## Getting started
To get started, all you need is access to the table/tables from which you need to extract the raports and your actual select statement:
```php
Report::set('query', 'select name, email from users')->getHtml();
```
This will generate an ad-hoc (on the fly) report. To create a permanent report using a default set of options use the **create** method:
```php
Report::create('my_report', 'select name, email from users')->getHtml();
```
This will add a new report to the database and generate it in HTML form. You may then include it as a variable anywhere on your view.

To access a previously created report you need to use the **setReport** method and (optionally) **set** any custom preferences before generating it:
```php
Report::setReport('my_report')
		->set('pdfPageSize', 'letter')
		->set('pdfPageOrientation', 'landscape')
		->getPdf();
```
To make any customization permanent use the **update** method:
```php
Report::setReport('my_report')->update('PdfPageSize', 'letter');
```
Check the next section for a complete list of methods and properties that you can use with ReGenerator.
## Reference
### Properties
Each report can have one or more associated properties that can be set at runtime or commited to the database:
 - **name** *[mandatory for saving]*: a uniqe name for the report. Only alphanumeric characters, dashes or underscores.
 - **query** *[mandatory]*: SQL query used to generate the report. Use standard blade markup (*{{ }}*) to include parameters if neccessary.
 - **database**: database name as defined in Laravel. Leave empty for default. Make sure to use the Laravel defined name and not the actual database name.
 - **title**: A descriptive title for the report. Can also have substitutable content (parameters).
 - **reportType**: the type of report to generate. Defaults to *vertical* (classic), use *horizontal* for pivoted reports (columns on rows), or *slices* for individual records.
 - **tag**: tag for grouping reports into collections. Leave empty if not needed.
 - **numericCols**: comma separated list of column numbers that contain numeric values.
 - **thousandsSeparator**: character used as thousands separator. Defaults to *dot*.
 - **decimalsSeparator**: character used as decimals separator. Defaults to *comma*.
 - **countCols**: comma separated list of column numbers that will feature a count total.
 - **sumCols**: comma separated list of column numbers that will feature a sum aggregate.
 - **averageCols**: comma separated list of column numbers that will feature a average aggregate.
 - **pdfPageSize**: as used by DomPdf, defaults to A4.
 - **pdfPageOrientation**: pdf page orientation, portrait or landscape.
 - **pdfFontSize**: pdf font size. In points, defaults to 10.
 - **pdfTemplate**: custom view to be used for wrapping the PDF report. Leave empty to use the default.
 - **csvQuotes**: type of quotes for enclosing CSV fields. Defaults to none.
 - **csvDelimiter**: CSV field delimiter. Usually comma or semicolon, defaults to comma.
 - **chartType**: the type of chart that will be generated. Choose between *lines*, *bars* and *pie*. Defaults to *lines*.
 - **chartSeries**: comma separated list of columns that hold the chart series.
 - **chartLabels**: column number that holds the chart labels.
 - **chartColors**: comma separated list of hex color codes to be used in chart.
 - **chartTemplate**: custom view to be used for the generated chart. Leave empty to use the default.
 - **parameters**: strings to be substituted in SQL queries. In the example below the {{condition1}} and {{condition2}} strings will be replaced in the SQL query.
 ```php
 $validParameters = [
	 'condition1' => 'where active=1',
	 'condition2' => 'and verified=1',
];
 ````

### Methods
```php
setReport(string $name): object | bool
```
Sets the active report.
- **name** - the name of the report.
***
```php
set(string $property, string $value): object
```
Sets a property for the current report. Can be used with stored reports (after **setReport()**) or chained with itself to generate ad-hoc reports.
- **property** - report property to match;
- **value** - value to assign to the property.
***
```php
getValue(int $line, int $column): string | bool
```
Returns a specific value from a report, useful when you have fixed size (x/y) reports.
- **line** - the line in the report;
- **column** - the column in the report.
***
```php
getArray(): string
```
Returns the current report as associative array. Must be called after **setReport()** or **set()**.
***
```php
getHtml(): string
```
Returns the current report as HTML formatted using Bootstrap classes. Must be called after **setReport()** or **set()**.
***
```php
getPdf(): string
```
Returns the current report as a PDF file within a variable. Must be called after **setReport()** or **set()**. To display or download the PDF file set the *Content-Type* and *Content-Disposition* header directives:
```php
return response()->make($pdfReport, 200, [
	'Content-Type' => 'application/pdf',
	'Content-Disposition' => 'inline; filename="test.pdf"',
]);
```
***
```php
getChart(): string
```
Returns the current report as canvas Chart, ready to be inserted into any view.  Must be called after **setReport()** or **set()**.
***
```php
getReports([string $property], [string $value]): array | bool
```
Returns an array containing all the reports matching a set criteria. If no property is provided the method returns a list of all available reports.
- **property** - property to match;
- **value** - specific value for the property to filter reports on.
***
```php
create(string $name, string $query, [string $database], [array $parameters]): object | bool
```
Creates a new report entry in the database. 
 - **name** - a unique name for the report;
 - **query** - the complete SQL query to be executed; if parameters are present the 4th argument must also be supplied;
 - **database** - the Laravel name of an alternative database to run the query against;
 - **parameters** - array containing a list of parameter names and values to substituted inside the SQL query prior to execution.
***
```php
 update(string | array $data, [string $value]): object | bool
```
Updates a report. Must be called after the **setReport()** method.
- **data** - can be either a single property of the report or an array containing property - value pairs;
- **value** - the value associated with a single property. Not needed for bulk updates.
***
```php
delete(): bool
```
Deletes a report. Must be called after the **setReport()** method.
## Overwriting
### Changinng the default parameters
To change the default parameters for new reports publish the config file as described above and adjust the **defaults** array to your liking.
### Customizing the PDF layout
To overwrite the default PDF page layout (to add a logo, etc.), publish the views and edit *views/vendor/md0/regenerator/pdf/report.blade.php* to suit your needs. If you need multiple templates create your own views and point to them with the *pdfTemplate* property. 
### Customizing the chart options
Use if you need to tweak Chart.js\'s settings publish the views and edit *views/vendor/md0/regenerator/chart/report.blade.php*. If you need multiple templates create your own views and point to them with the *chartTemplate* property. 
## Errors & Suggestions
Please send your improvement suggestions or report bugs / errors in the `Issues` section.
## License
Distributed under the GPL-3.0 License. See `LICENSE` for more information.
## Acknowledgements
ReGenerator uses the following open source libraries to generate raports. Please consult their respective pages for more information on usage and available customizations:
- [Dompdf](https://github.com/dompdf/dompdf)
- [Chart.js](https://github.com/chartjs/Chart.js)