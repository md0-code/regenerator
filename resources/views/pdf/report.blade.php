<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style>
		@page {
			margin: 60px 25px;
		}

		header {
			position: fixed;
			top: -60px;
			left: 0px;
			right: 0px;
			padding: 10px;
			height: 50px;
		}

		footer {
			position: fixed;
			bottom: -60px;
			left: 0px;
			right: 0px;
			padding: 10px;
			height: 50px;
		}

		body {
			font-family: 'DejaVu Sans' !important;
			font-size: <?= $fontSize ?>px;
		}

		p {
			page-break-after: always;
		}

		p:last-child {
			page-break-after: never;
		}

		table {
			width: 100%;
		}

		tr {
			padding: 0px;
		}

		tr:nth-child(even) {
			background: #cccccc;
		}

		tr:nth-child(odd) {
			background: #cccccc;
		}

		td {
			padding: 5px;
		}

		.page:after {
			content: counter(page);
		}
	</style>
</head>

<body>
	<header></header>
	<footer>
		<p align="right" class="page"><small>{{ __('Page') }}</small></p>
	</footer>
	<div id="content">
		<h2 align="center">{{ $title }}</h2>
		{!! $report !!}
	</div>
	</div>
</body>

</html>