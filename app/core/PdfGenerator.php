<?php

namespace App\Core;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    private Dompdf $dompdf;

    public function __construct()
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', dirname(__DIR__, 2));

        $this->dompdf = new Dompdf($options);
        $this->dompdf->setPaper('A4', 'portrait');
    }

    public function setPaper(string $size, string $orientation = 'portrait'): void
    {
        $this->dompdf->setPaper($size, $orientation);
    }

    public function generar(string $html, string $nombreArchivo = 'documento.pdf'): void
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $nombreArchivo . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        echo $this->dompdf->output();
        exit;
    }

    public function cssComun(): string
    {
        return '
        <style>
            @page { margin: 20mm 12mm 20mm 12mm; }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: Helvetica, Arial, sans-serif; color: #1a1a1a; line-height: 1.4; padding: 0 4mm; }
            .header { background: #ea580c; color: #fff; padding: 20px 24px; margin-bottom: 16px; }
            .header h1 { font-size: 20px; margin-bottom: 4px; }
            .header p { font-size: 11px; opacity: 0.9; }

            .dash-table { width: 100%; border-collapse: separate; border-spacing: 8px; margin-bottom: 14px; }
            .dash-table td { width: 33.33%; vertical-align: top; }
            .dash-card { padding: 12px 14px; border-radius: 6px; border: 1px solid #e5e5e5; background: #fff; page-break-inside: avoid; }
            .dash-card .label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; color: #888; margin-bottom: 6px; }
            .dash-card .value { font-size: 13px; font-weight: 700; }
            .dash-card .sub { font-size: 9px; color: #666; margin-top: 2px; }
            .dash-card .ves-big { font-size: 12px; font-weight: 700; color: #1a1a1a; }
            .dash-card.orange { border-left: 4px solid #ea580c; }
            .dash-card.green { border-left: 4px solid #16a34a; }
            .dash-card.red { border-left: 4px solid #dc2626; }

            table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10px; }
            table thead { display: table-header-group; }
            table thead th { background: #1a1a1a; color: #fff; padding: 7px 6px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
            table thead th.right, td.right { text-align: right; }
            table tbody td { padding: 6px; border-bottom: 1px solid #e0e0e0; }
            table tbody tr:nth-child(even) td { background: #fafafa; }
            .td-cliente { font-weight: 700; }
            .td-metodo { font-size: 9px; color: #666; }
            .td-vendedor { font-size: 9px; color: #999; }

            .badge { display: inline-block; padding: 3px 7px; border-radius: 3px; font-size: 7px; font-weight: 700; text-transform: uppercase; }
            .badge-pagado { background: #d1fae5; color: #065f46; }
            .badge-pendiente { background: #fee2e2; color: #991b1b; }
            .badge-info { background: #dbeafe; color: #1e40af; }

            .section-title { font-size: 12px; font-weight: 700; color: #ea580c; margin: 16px 0 6px 0; padding-bottom: 4px; border-bottom: 2px solid #ea580c; }
            .footer { text-align: center; font-size: 9px; color: #999; margin-top: 20px; padding-top: 10px; border-top: 1px solid #e0e0e0; }
        </style>';
    }
}
