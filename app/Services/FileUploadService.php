<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\FileUpload;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToImage\Pdf;
use setasign\FpdiProtection\FpdiProtection;
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Facades\Log;



class FileUploadService
{
    public static function fileStore(Request $request)
    {
        $filecount = count($request->file('file'));
        $newname = "";
        for ($i = 0; $i <= ($filecount - 1); $i++) {
            $image = $request->file('file')[$i];

            $imageName = $image->getClientOriginalName();
            $oldfile = FileUpload::where('oldname', $imageName)->get();
            if (!is_array($oldfile)) {

                $newname = Str::random(40) . "_" . $imageName;
                $image->move(public_path('file_upload'), $newname);

                $fileType = $image->getClientMimeType();

                $imageUpload = new FileUpload();
                $imageUpload->filename = $newname;
                $imageUpload->oldname = $imageName;
                $imageUpload->save();
            }
        }
        return $newname;
        //return response()->json(['success' => $newname]);
    }

    public static function fileGetName(Request $request)
    {

        $filename =  $request->get('name');
        $oldfile = FileUpload::where('oldname', $filename)->get();
        if ($oldfile) {
            $path = public_path() . '/file_upload/' . $oldfile[0]->filename;
            if (file_exists($path)) {
                return $oldfile[0]->filename;
            }
        } else {
            return 'none';
        }
    }

    public static function fileDestroy(Request $request)
    {

        $filename =  $request->get('name');
        $oldfile = FileUpload::where('oldname', $filename)->get();
        if ($oldfile) {
            $path = public_path() . '/file_upload/' . $oldfile[0]->filename;
            if (file_exists($path)) {
                unlink($path);
                FileUpload::where('oldname', $filename)->delete();
            }
            $spath = public_path() . '/stamps/' . $oldfile[0]->filename;
            if (file_exists($spath)) {
                unlink($spath);
            }
            return $filename;
        } else {
            return 'none';
        }
    }


    public static function addTextAtCenterOfLine($image, $x1, $x2, $y, $text)
    {
        $fontPath = public_path('fonts/THSarabunNew Bold.ttf'); // Replace with the actual path to your TrueType font file
        $fontSize = 12; // Adjust the font size as needed
        $fontColor = [0, 0, 255]; // RGB color for blue font (0, 0, 255)

        // Calculate the width of the text
        $textWidth = imagettfbbox($fontSize, 0, $fontPath, $text);
        $textWidth = $textWidth[2] - $textWidth[0];

        // Calculate the X coordinate to center the text
        $textX = ($x1 + $x2) / 2 - $textWidth / 2;

        // Set the Y coordinate for the text (offset from the line)
        $textY = $y - 5; // Decrease the offset value to move the text closer to the dotted line

        // Load the image as a TrueType font
        imagettftext($image->getCore(), $fontSize, 0, $textX, $textY, imagecolorallocate($image->getCore(), $fontColor[0], $fontColor[1], $fontColor[2]), $fontPath, $text);
    }

    private static function addTextInFrontOfLine($image, $x, $y, $text)
    {
        $image->text($text, $x, $y, function ($font) {
            $font->file(public_path('fonts/THSarabunNew Bold.ttf')); // Replace 'path-to-your-font.ttf' with the actual path to your font file
            $font->size(14); // Adjust the font size as needed
            $font->color('#0000FF'); // Adjust the text color as needed
            $font->align('left'); // Align the text to the left
            $font->valign('top'); // Align the text to the top
        });
    }


    public static function createTransparentRectangleImageWithText($width, $height, $borderWidth, $borderColor, $filename, $borderMargin, $lineSpacing, $lineMarginLeft, $lineMarginRight, $stampText1, $stampText2, $stampText3)
    {
        // Calculate the dimensions for the frame inside the image with the margin
        $frameX1 = $borderWidth + $borderMargin; // X coordinate of top-left corner
        $frameY1 = $borderWidth + $borderMargin; // Y coordinate of top-left corner
        $frameX2 = $width - $borderWidth - $borderMargin; // X coordinate of bottom-right corner
        $frameY2 = $height - $borderWidth - $borderMargin; // Y coordinate of bottom-right corner

        // Create a new canvas with a transparent background
        $image = Image::canvas($width, $height, null);

        // Draw a rectangle with a transparent fill and border
        $image->rectangle($frameX1, $frameY1, $frameX2, $frameY2, function ($draw) use ($borderWidth, $borderColor) {
            //$draw->background('transparent'); // Set the rectangle fill to transparent
            $draw->border($borderWidth, $borderColor); // Set the border width and color
        });



        //top text
        $topSpaceText = 'เทศบาลตำบลหนองโดน';
        $image->text($topSpaceText, $frameX1 + 20, $frameY1 + 15, function ($font) {
            // Set font properties if needed
            $font->file(public_path('fonts/THSarabunNew Bold.ttf'));
            $font->size(18);
            $font->color('#0000FF');
        });



        // Calculate the y-coordinate for the top, center, and bottom lines
        $yTop = ($frameY1 + $lineSpacing) + 15;
        $yCenter = (($frameY1 + $frameY2) / 2) + 15;
        $yBottom = ($frameY2 - $lineSpacing) + 15;

        // Draw the three dotted lines inside the frame with adjustable thickness
        $lineThickness = 0.5; // Adjust the line thickness as needed
        $dotLength = 2; // Adjust the length of each dotted segment as needed

        // Top dotted line
        $lineX1Top = $frameX1 + $lineMarginLeft;
        $lineX2Top = $frameX2 - $lineMarginRight;
        $x = $lineX1Top;
        $xPositions = [];
        while ($x < $lineX2Top) {
            $x2 = min($x + $dotLength, $lineX2Top);
            $image->rectangle($x, $yTop, $x2, $yTop + $lineThickness, function ($draw) use ($borderColor) {
                $draw->background($borderColor);
            });
            $xPositions[] = [$x, $x2];
            $x += 2 * $dotLength; // Adjust the spacing between each dotted segment as needed
        }

        // Center dotted line
        $lineX1Center = $frameX1 + $lineMarginLeft;
        $lineX2Center = $frameX2 - $lineMarginRight;
        $x = $lineX1Center;
        while ($x < $lineX2Center) {
            $x2 = min($x + $dotLength, $lineX2Center);
            $image->rectangle($x, $yCenter, $x2, $yCenter + $lineThickness, function ($draw) use ($borderColor) {
                $draw->background($borderColor);
            });
            $x += 2 * $dotLength; // Adjust the spacing between each dotted segment as needed
        }

        // Bottom dotted line
        $lineX1Bottom = $frameX1 + $lineMarginLeft;
        $lineX2Bottom = $frameX2 - $lineMarginRight;
        $x = $lineX1Bottom;
        while ($x < $lineX2Bottom) {
            $x2 = min($x + $dotLength, $lineX2Bottom);
            $image->rectangle($x, $yBottom, $x2, $yBottom + $lineThickness, function ($draw) use ($borderColor) {
                $draw->background($borderColor);
            });
            $x += 2 * $dotLength; // Adjust the spacing between each dotted segment as needed
        }

        //text before line
        $textX1Top = $lineX1Top - 35; // Adjust the value as needed to position the text in front of the line
        $textYTop = $yTop - 10; // Adjust the value as needed to position the text in front of the line
        self::addTextInFrontOfLine($image, $textX1Top, $textYTop, 'เลขที่รับ');

        $textX1Center = $lineX1Center - 23; // Adjust the value as needed to position the text in front of the line
        $textYCenter = $yCenter - 10; // Adjust the value as needed to position the text in front of the line
        self::addTextInFrontOfLine($image, $textX1Center, $textYCenter, 'วันที่');

        $textX1Bottom = $lineX1Bottom - 23; // Adjust the value as needed to position the text in front of the line
        $textYBottom = $yBottom - 8; // Adjust the value as needed to position the text in front of the line
        self::addTextInFrontOfLine($image, $textX1Bottom, $textYBottom, 'เวลา');
        //text before line

        //text on line
        $textForTopLine = $stampText1;
        self::addTextAtCenterOfLine($image, $lineX1Top, $lineX2Top, $yTop, $textForTopLine);
        $textForCenterLine = $stampText2;
        $textForBottomLine = $stampText3;
        self::addTextAtCenterOfLine($image, $lineX1Center, $lineX2Center, $yCenter, $textForCenterLine);
        self::addTextAtCenterOfLine($image, $lineX1Bottom, $lineX2Bottom, $yBottom, $textForBottomLine);
        //text on line


        // Check if the file already exists, and delete it if it does
        $filePath = public_path('stamps/' . $filename . '.png'); // Modify the file extension to PNG
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        // Save the manipulated image as a PNG file with a transparent background
        $image->save($filePath, 100, 'png');

        return $filePath; // Return the path to the saved image
    }

    private static function addStampToPDF($filePath, $imagePath, $x, $y)
    {
        // Load the contents of the PDF file
        $pdfContent = file_get_contents($filePath);

        // Convert the image to an Intervention Image object
        $imageData = file_get_contents($imagePath);
        $interventionImage = Image::make($imageData);

        // Encode the Intervention Image as base64 data
        $dataUrl = $interventionImage->encode('data-url')->encoded;

        // Generate the HTML for the image stamp with absolute positioning
        $html = '<div style="position: absolute; top: ' . $y . 'px; left: ' . $x . 'px;"><img src="' . $dataUrl . '" /></div>';

        // Include the dompdf library
        require_once base_path('vendor/dompdf/dompdf/autoload.inc.php');

        // Create a new Dompdf instance
        $dompdf = new \Dompdf\Dompdf();

        // Load the PDF content with the image stamp HTML
        $dompdf->loadHtml($pdfContent . $html);

        // (Optional) Set additional configuration options for dompdf if needed
        $dompdf->setPaper('A4', 'portrait');

        // Render the PDF
        $dompdf->render();

        // Get the PDF content
        $pdfWithStamp = $dompdf->output();

        return $pdfWithStamp;
    }


    public static function stampPDFWithImage($filePath, $x, $y, $stampText1, $stampText2, $stampText3, $signPath, $x2, $y2, $delete_sign = 0)
    {
        // Check if the file exists
        if (!file_exists($filePath)) {
            return false; // Return false or handle the error if the file doesn't exist
        }


        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $isPdf = strtolower($extension) === 'pdf';
        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);

        if ($isPdf) {
            // Create an instance of FPDI with TCPDF and FPDI Protection
            //$pdf = new FpdiProtection();
            $pdf = new Fpdi();
            $pdf->SetPrintHeader(false);
            $pdf->SetPrintFooter(false);
            // Set the protection key if the PDF is password-protected (optional)
            // $pdf->setProtectionKey('your_password_here');

            // Add a page to the PDF
            //$pdf->AddPage();

            // Set the source file (the PDF to be stamped)
            $pagecount = $pdf->setSourceFile($filePath);

            // Import the first page from the source PDF
            //$importedPage = $pdf->importPage(1);

            // Use the imported page as a template
            //$pdf->useTemplate($importedPage, 0, 0);

            // Add the image stamp to the PDF
            $stampImagePath = self::createTransparentRectangleImageWithText(
                200,
                120,
                1,
                [0, 0, 255],
                'stamp_image',
                10,
                25,
                45,
                10,
                $stampText1,
                $stampText2,
                $stampText3
            );

            //$stampImage = file_get_contents($stampImagePath);
            //$pdf->Image('@' . $stampImage, $x, $y, 0, 0, '', '', '', false, 300);



            // Add watermark to PDF pages
            for ($i = 1; $i <= $pagecount; $i++) {
                $tpl = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->addPage();
                $pdf->useTemplate($tpl, 1, 1, $size['width'], $size['height'], TRUE);

                if ($i === 1) {
                    //Put the watermark
                    $stampImage = file_get_contents($stampImagePath);
                    $pdf->Image('@' . $stampImage, $x, $y, 0, 0, '', '', '', false, 300);

                    //put signature
                    $stampsImage = file_get_contents($signPath);
                    $pdf->Image('@' . $stampsImage, $x2, $y2, 0, 0, '', '', '', false, 300);
                }
            }

            @unlink($stampImagePath);
            // Optionally, you can save the stamped PDF to a file
            $stampedFileName = basename($filePath);
            $stampedFilePath = public_path() . '/stamps/' . $stampedFileName;
            $stampedURL = url('/') . '/stamps/' . $stampedFileName;

            // Check if the file already exists
            if (file_exists($stampedFilePath)) {
                // If the file exists, unlink (delete) the old file
                unlink($stampedFilePath);
            }


            // Save the stamped PDF
            $pdf->Output($stampedFilePath, 'F');
            //unlink($outp);

            // Output the stamped PDF as a response
            //$pdf->Output();
            //return $stampedURL;
        }

        if ($isImage) {
            // Implement the image stamping logic here
            // Create the stamp image using createTransparentRectangleImageWithText method
            $stampImagePath = self::createTransparentRectangleImageWithText(
                200,
                120,
                1,
                [0, 0, 255],
                'stamp_image',
                10,
                25,
                45,
                10,
                $stampText1,
                $stampText2,
                $stampText3
            );

            // Apply the stamp image to the original image
            // You can use GD or any other library of your choice to do this
            // For demonstration, let's assume you are using GD
            $originalImage = imagecreatefromstring(file_get_contents($filePath));
            $stampImage = imagecreatefrompng($stampImagePath);
            $signatureImage = imagecreatefromstring(file_get_contents($signPath));

            // Set the position (x, y) where the stamp should be placed on the original image
            $stampX = $x * 2;
            $stampY = $y * 2;
            $signatureX = $x2 * 2;
            $signatureY = $y2 * 2;

            // Apply the stamp image to the image
            imagecopy($originalImage, $stampImage, $stampX, $stampY, 0, 0, imagesx($stampImage), imagesy($stampImage));
            imagecopy($originalImage, $signatureImage, $signatureX, $signatureY, 0, 0, imagesx($signatureImage), imagesy($signatureImage));

            // Save the stamped image to a new file (you can also return the image directly or save it to a different location)
            $stampedFileName = basename($filePath);
            $stampedFilePath = public_path() . '/stamps/' . $stampedFileName;
            $stampedURL = url('/') . '/stamps/' . $stampedFileName;


            if (file_exists($stampedFilePath)) {
                // If the file exists, unlink (delete) the old file
                unlink($stampedFilePath);
            }



            imagepng($originalImage, $stampedFilePath);
            //imagedestroy($originalImage);
            imagedestroy($stampImage);
            //imagedestroy($signatureImage);
            @unlink($stampImagePath);
        }

        if ($delete_sign == 1) {
            //unlink($signPath);
        }

        return $stampedURL;
    }
}
