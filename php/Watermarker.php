<?php
class Watermarker {
    private $watermarkImage;
    private $margin = 10;
    
    public function __construct($watermarkPath = null) {
        if ($watermarkPath && file_exists($watermarkPath)) {
            $this->watermarkImage = imagecreatefrompng($watermarkPath);
        } else {
            // Create a default watermark if none exists
            $this->createDefaultWatermark();
        }
    }
    
    private function createDefaultWatermark() {
        // Create a simple watermark image
        $width = 800;
        $height = 200;
        $this->watermarkImage = imagecreatetruecolor($width, $height);
        
        // Make the background transparent
        imagesavealpha($this->watermarkImage, true);
        $trans_color = imagecolorallocatealpha($this->watermarkImage, 0, 0, 0, 127);
        imagefill($this->watermarkImage, 0, 0, $trans_color);
        
        // Add text - using grey color (128,128,128) with transparency
        $text = "CONFIDENTIAL";
        $grey = imagecolorallocatealpha($this->watermarkImage, 128, 128, 128, 30);
        $font_size = 5;
        
        // Make text larger and centered
        imagestring($this->watermarkImage, $font_size, 300, 80, $text, $grey);
        
        // Save the watermark for future use
        if (!file_exists(dirname(__FILE__) . '/assets')) {
            mkdir(dirname(__FILE__) . '/assets', 0755, true);
        }
        imagepng($this->watermarkImage, dirname(__FILE__) . '/assets/watermark.png');
    }
    
    public function applyToImage($sourcePath, $outputPath) {
        // Get image type
        $imageInfo = getimagesize($sourcePath);
        $imageType = $imageInfo[2];
        
        // Create image resource based on type
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }
        
        // Get dimensions
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Create a single large diagonal watermark
        $text = "CONFIDENTIAL";
        $greyColor = imagecolorallocatealpha($image, 128, 128, 128, 50); // Grey with transparency
        
        // Use a large font size for the watermark
        $fontSize = 5;
        
        // Calculate diagonal position and size based on image dimensions
        $diagonal = sqrt($width * $width + $height * $height);
        $textSize = 500; // Make text about 20% of diagonal length
        
        // Create a new image for the rotated text
        $textImage = imagecreatetruecolor($diagonal, $textSize);
        
        // Make the background transparent
        imagealphablending($textImage, false);
        imagesavealpha($textImage, true);
        $transparent = imagecolorallocatealpha($textImage, 0, 0, 0, 127);
        imagefilledrectangle($textImage, 0, 0, $diagonal, $textSize, $transparent);
        
        // Enable alpha blending for text
        imagealphablending($textImage, true);
        
        // Add text to the center of the image
        $grey = imagecolorallocatealpha($textImage, 128, 128, 128, 50);
        imagestring($textImage, $fontSize, ($diagonal - strlen($text) * imagefontwidth($fontSize)) / 2, 
                   ($textSize - imagefontheight($fontSize)) / 2, $text, $grey);
        
        // Rotate and position the text image
        $angle = atan2($height, $width) * 180 / M_PI;
        $rotated = imagerotate($textImage, -$angle, $transparent);
        
        // Copy the rotated text onto the original image
        imagecopy($image, $rotated, 
                 ($width - imagesx($rotated)) / 2, 
                 ($height - imagesy($rotated)) / 2, 
                 0, 0, imagesx($rotated), imagesy($rotated));
        
        // Save the watermarked image
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($image, $outputPath, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($image, $outputPath, 9);
                break;
            case IMAGETYPE_GIF:
                imagegif($image, $outputPath);
                break;
        }
        
        // Clean up
        imagedestroy($image);
        imagedestroy($textImage);
        imagedestroy($rotated);
        return true;
    }
    
    public function applyToPdf($sourcePath, $outputPath) {
        // Check if file exists and is a PDF
        if (!file_exists($sourcePath) || pathinfo($sourcePath, PATHINFO_EXTENSION) != 'pdf') {
            return false;
        }
        
        // Check if FPDI is available
        if (file_exists('vendor/autoload.php')) {
            return $this->fpdiPdfWatermark($sourcePath, $outputPath);
        } else {
            return $this->simplePdfWatermark($sourcePath, $outputPath);
        }
    }
    
    private function simplePdfWatermark($sourcePath, $outputPath) {
        // Create a simple PDF watermarking solution using basic PHP
        // This approach doesn't require external libraries but has limitations
        
        // Read the PDF file
        $pdfContent = file_get_contents($sourcePath);
        
        // Simple watermark text
        $watermarkText = "CONFIDENTIAL";
        
        // Encode the watermark text for PDF
        $watermarkTextEncoded = $this->encodePdfString($watermarkText);
        
        // Create a simple watermark object to inject into the PDF
        // Using grey color (#808080) for the watermark
        $watermarkObj = "
        /Watermark
        <<
        /Type /Annot
        /Subtype /FreeText
        /Contents ($watermarkTextEncoded)
        /DS (font: Helvetica 150pt; text-align: center; color: #808080; opacity: 0.5)
        /C [0.5 0.5 0.5]
        /Border [0 0 0]
        /AP <<
            /N <<
                /Type /XObject
                /Subtype /Form
                /BBox [0 0 612 792]
                /Matrix [1 0 0 1 0 0]
                /Length 0
            >>
        >>
        /Rect [50 400 550 500]
        /F 4
        /DA (/Helvetica 120 Tf 0.8 0.8 0.8 rg)
        /Q 1
        >>
        ";
        
        // Find a suitable position to inject the watermark
        // This is a simplified approach - a proper implementation would parse the PDF structure
        $pattern = '/endobj\s+\d+\s+0\s+obj/';
        $replacement = "endobj\n" . rand(1000, 9999) . " 0 obj\n" . $watermarkObj . "\nendobj\n$0";
        
        // Inject the watermark object
        $modifiedPdf = preg_replace($pattern, $replacement, $pdfContent, 1);
        
        // Write the modified PDF
        file_put_contents($outputPath, $modifiedPdf);
        
        return "Basic PDF watermark applied. For better results, install FPDI/FPDF.";
    }
    
    private function fpdiPdfWatermark($sourcePath, $outputPath) {
        try {
            // Include FPDI
            require_once 'vendor/autoload.php';
            
            // Create instance of FPDI
            $pdf = new \setasign\Fpdi\Fpdi();
            
            // Get the number of pages
            $pageCount = $pdf->setSourceFile($sourcePath);
            
            // Watermark text
            $text = 'CONFIDENTIAL';
            
            // Loop through all pages
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                // Import page
                $templateId = $pdf->importPage($pageNo);
                
                // Get the size of the imported page
                $size = $pdf->getTemplateSize($templateId);
                
                // Add a page with the same orientation and size
                $pdf->AddPage(
                    $size['width'] > $size['height'] ? 'L' : 'P', 
                    [$size['width'], $size['height']]
                );
                
                // Use the imported page
                $pdf->useTemplate($templateId);
                
                // Set font - large size for the watermark
                $pdf->SetFont('Helvetica', 'B', 150);
                // Set grey color (128,128,128) for PDF
                $pdf->SetTextColor(200, 200, 200);
                
                // Calculate the center position
                $width = $pdf->GetStringWidth($text);
                $x = ($size['width'] - $width) / 2;
                $y = $size['height'] / 2;
                
                // Set transparency if available
                if (method_exists($pdf, 'SetAlpha')) {
                    $pdf->SetAlpha(0.3);
                }
                
                // Add a single large rotated text watermark
                $this->addRotatedText($pdf, $x, $y, $text, 45);
            }
            
            // Output the new PDF
            $pdf->Output($outputPath, 'F');
            return "PDF watermarked successfully using FPDI.";
        } catch (Exception $e) {
            // If FPDI fails, fall back to simple method
            return $this->simplePdfWatermark($sourcePath, $outputPath);
        }
    }
    
    private function addRotatedText($pdf, $x, $y, $text, $angle) {
        // Save the current state
        if (method_exists($pdf, 'StartTransform')) {
            $pdf->StartTransform();
            $pdf->Rotate($angle, $x, $y);
            $pdf->Text($x, $y, $text);
            $pdf->StopTransform();
        } else {
            // For older FPDF versions without transform methods,
            // just place the text without rotation
            $pdf->Text($x, $y, $text);
        }
    }
    
    private function encodePdfString($string) {
        // Simple encoding for PDF strings
        $encoded = str_replace(
            array('\\', '(', ')', "\r"),
            array('\\\\', '\\(', '\\)', '\\r'),
            $string
        );
        return $encoded;
    }
    
    public function __destruct() {
        if ($this->watermarkImage) {
            imagedestroy($this->watermarkImage);
        }
    }
}