<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf as DomPDF;
use Intervention\Image\Facades\Image;
use Spatie\PdfToImage\Exceptions\InvalidFormat;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Pdf;

class PdfController extends Controller
{
    public function uploadForm()
    {
        return view('upload_form');
    }

    public function addWatermark()
    {
        request()->validate([
            'pdf_file' => 'required|mimes:pdf|max:2048',
        ]);
        $pdfFile = request()->file('pdf_file');
        $pdfFileName = time().'.'.$pdfFile->extension();
        $pdfFile->move(public_path('uploads'), $pdfFileName);
        $pdfFilePath = public_path('uploads/'.$pdfFileName);
        $outputFilePath = public_path('uploads/'.time());

        \File::makeDirectory($outputFilePath);
        try {
            $pdf = new Pdf($pdfFilePath);
            $pdf->setOutputFormat('png');
            $pdf->saveAllPagesAsImages($outputFilePath);
        } catch (PdfDoesNotExist|InvalidFormat$e) {
            dd($e->getMessage());
        }
        $images = \File::AllFiles($outputFilePath);
        foreach ($images as $imageFile) {
            $image = Image::make($imageFile);
            $centerX = $image->getWidth() / 2;
            $centerY = $image->getHeight() / 2;
            $image->text('UP State Archives', $centerX, $centerY, function ($font) {
                $font->file(public_path('fonts/Roboto/Roboto-Regular.ttf'));
                $font->size(150);
                $font->align('center');
                $font->angle(45);
                $font->valign('bottom');
                $font->color([0, 0, 0, 0.2]);
            });
            $image->save($imageFile);
        }

        $dompdf = DomPDF::loadView('image_to_pdf', compact('images'));
        $dompdf->setOption('enable_remote', true);
        $dompdf->setOption('isHtml5ParserEnabled', true);
        $dompdf->setPaper('A4',);
        $dompdf->setOption('dpi', 300);
        $dompdf->setOption('isPhpEnabled', true);
        $dompdf->setOption('isRemoteEnabled', true);
        $dompdf->setOption('isJavascriptEnabled', true);
        $dompdf->setOption('isHtml5ParserEnabled', true);
        $dompdf->save(public_path('uploads/'.$pdfFileName));

        \File::deleteDirectory($outputFilePath);
        return redirect('/uploads/'.$pdfFileName);
    }


}
