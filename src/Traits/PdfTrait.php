<?php

namespace MD0\ReGenerator\Traits;

use Dompdf\Dompdf;
use Dompdf\Options;

trait PdfTrait
{
	function getPdf()
	{
		$options = new Options();
		$options->set('isRemoteEnabled', true);
		$pdf = new Dompdf($options);
		$pdf->setPaper($this->pdfPageSize, $this->pdfPageOrientation);
		$data['fontSize'] = $this->pdfFontSize;
		$data['title'] = $this->title;
		$data['report'] = $this->getHtml();
		$report = view($this->pdfTemplate, $data)->render();
		$pdf->loadHtml($report);
		$pdf->render();
		return $pdf->output();
	}
}
