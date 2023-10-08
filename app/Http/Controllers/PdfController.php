<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf as DomPDF;
use League\Glide\Filesystem\FileNotFoundException;
use Spatie\Image\Exceptions\InvalidImageDriver;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\Image\Image;
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
            $image = Image::load($imageFile);
            try {
                $image->useImageDriver('imagick');
                $image->watermark(public_path('watermark.png')) // Replace with your watermark image
                ->watermarkOpacity(50) // Adjust the opacity of the watermark
                ->watermarkPosition('bottom-right')
                    ->watermarkPadding(10, 10);
                $image->save();
            } catch (InvalidManipulation|FileNotFoundException|InvalidImageDriver  $e) {
                dd($e->getMessage());
            }
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

        if (file_exists($outputFilePath)) {
            \File::deleteDirectory($outputFilePath);
        }
        return redirect('/uploads/'.$pdfFileName);
    }


}
