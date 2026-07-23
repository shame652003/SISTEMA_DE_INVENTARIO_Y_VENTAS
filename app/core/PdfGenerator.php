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
            .header { background: linear-gradient(135deg, #ea580c, #c2410c); color: #fff; padding: 20px 24px; border-radius: 0 0 8px 8px; margin-bottom: 16px; }
            .header h1 { font-size: 20px; margin-bottom: 4px; }
            .header p { font-size: 11px; opacity: 0.9; }
            .info-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 11px; color: #555; }
            .info-row span { background: #fff3e0; padding: 4px 10px; border-radius: 4px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10px; }
            table thead th { background: #1a1a1a; color: #fff; padding: 8px 6px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
            table thead th.right, td.right { text-align: right; }
            table thead th.center, td.center { text-align: center; }
            table tbody td { padding: 6px; border-bottom: 1px solid #e0e0e0; }
            table tbody tr:nth-child(even) td { background: #fff8f0; }
            .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
            .badge-success { background: #16a34a; color: #fff; }
            .badge-danger { background: #dc2626; color: #fff; }
            .badge-warning { background: #ea580c; color: #fff; }
            .badge-info { background: #0284c7; color: #fff; }
            .badge-dark { background: #374151; color: #fff; }
            .summary { background: #fff3e0; border-left: 4px solid #ea580c; padding: 10px 14px; border-radius: 4px; margin-top: 12px; font-size: 11px; }
            .summary strong { color: #ea580c; }
            .footer { text-align: center; font-size: 9px; color: #999; margin-top: 20px; padding-top: 10px; border-top: 1px solid #e0e0e0; }
            .total-row td { font-weight: bold; background: #333 !important; color: #fff !important; }
            .section-title { font-size: 13px; font-weight: bold; color: #ea580c; margin: 14px 0 6px 0; padding-bottom: 4px; border-bottom: 2px solid #ea580c; }
        </style>';
    }
}
