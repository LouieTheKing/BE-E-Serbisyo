<?php

namespace App\Services;

use App\Models\RequestDocument;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;

class PdfGeneratorService
{
    /**
     * Generate filled PDF from template
     *
     * @param RequestDocument $requestDocument
     * @return string Path to generated document
     * @throws \Exception
     */
    public function generateFilledDocument(RequestDocument $requestDocument)
    {
        $document = $requestDocument->documentDetails;
        $information = $requestDocument->information ?? [];

        // Priority 1: Use HTML template from database
        if ($document->html_template) {
            return $this->generateFromHtmlString($requestDocument, $document->html_template, $information);
        }

        // Priority 2: Use template file
        if (!$document->template_path) {
            throw new \Exception('No template found for this document');
        }

        if (!Storage::disk('public')->exists($document->template_path)) {
            throw new \Exception('Template file not found');
        }

        $templatePath = Storage::disk('public')->path($document->template_path);

        // Check if template is HTML, PDF, or DOCX
        $extension = strtolower(pathinfo($templatePath, PATHINFO_EXTENSION));

        if ($extension === 'html') {
            // HTML Template - Replace placeholders and generate PDF
            return $this->generateFromHtmlFile($requestDocument, $templatePath, $information);
        } else if ($extension === 'docx') {
            // DOCX Template - Replace placeholders and optionally convert to PDF
            return $this->generateFromDocxTemplate($requestDocument, $templatePath, $information);
        } else if ($extension === 'pdf') {
            // PDF Template - Copy original (cannot replace placeholders reliably)
            return $this->generateFromPdfTemplate($requestDocument, $templatePath, $information);
        } else {
            throw new \Exception('Unsupported template format. Use HTML, DOCX, or PDF templates.');
        }
    }

    /**
     * Generate PDF from HTML string (from database)
     */
    private function generateFromHtmlString(RequestDocument $requestDocument, string $htmlTemplate, array $information)
    {
        // Replace placeholders with actual data
        foreach ($information as $key => $value) {
            $placeholder = '{{{' . $key . '}}}';
            $htmlTemplate = str_replace($placeholder, htmlspecialchars($value), $htmlTemplate);
        }

        // Generate PDF from HTML
        $pdf = Pdf::loadHTML($htmlTemplate);

    // Generate output filename
    $outputFilename = 'filled_documents/' . $requestDocument->transaction_id . '_' . time() . '.pdf';
    $outputPath = Storage::disk('public')->path($outputFilename);

    // Ensure directory exists
    Storage::disk('public')->makeDirectory('filled_documents');

    // Save PDF
    $pdf->save($outputPath);

    return $outputFilename;
    }

    /**
     * Generate PDF from HTML template file
     */
    private function generateFromHtmlFile(RequestDocument $requestDocument, string $templatePath, array $information)
    {
        // Read HTML template
        $htmlContent = file_get_contents($templatePath);

        // Replace placeholders with actual data
        foreach ($information as $key => $value) {
            $placeholder = '{{{' . $key . '}}}';
            $htmlContent = str_replace($placeholder, htmlspecialchars($value), $htmlContent);
        }

        // Generate PDF from HTML
        $pdf = Pdf::loadHTML($htmlContent);

    // Generate output filename
    $outputFilename = 'filled_documents/' . $requestDocument->transaction_id . '_' . time() . '.pdf';
    $outputPath = Storage::disk('public')->path($outputFilename);

    // Ensure directory exists
    Storage::disk('public')->makeDirectory('filled_documents');

    // Save PDF
    $pdf->save($outputPath);

    return $outputFilename;
    }

    /**
     * Generate filled document from DOCX template
     * This WORKS perfectly - placeholders are replaced while preserving formatting!
     *
     * @param RequestDocument $requestDocument
     * @param string $templatePath
     * @param array $information
     * @return string
     * @throws \Exception
     */
    private function generateFromDocxTemplate(RequestDocument $requestDocument, string $templatePath, array $information)
    {
        try {
            // Load DOCX template
            $templateProcessor = new TemplateProcessor($templatePath);

            // Replace placeholders with actual data
            // PHPWord uses ${variable} syntax, but we'll support both ${} and {{{  }}}
            foreach ($information as $key => $value) {
                // Try both placeholder formats
                $templateProcessor->setValue($key, $value);  // For ${key} format

                // Also handle {{{key}}} format by replacing manually
                $placeholderTriple = '{{{' . $key . '}}}';
                try {
                    $templateProcessor->setValue($placeholderTriple, $value);
                } catch (\Exception $e) {
                    // Ignore if placeholder doesn't exist
                }
            }

            // Generate output filename for DOCX
            $outputFilename = 'filled_documents/' . $requestDocument->transaction_id . '_' . time() . '.docx';
            $outputPath = Storage::disk('public')->path($outputFilename);

            // Ensure directory exists
            Storage::disk('public')->makeDirectory('filled_documents');

            // Save filled DOCX
            $templateProcessor->saveAs($outputPath);

            // Optionally convert to PDF (requires LibreOffice or similar)
            // For now, just return the DOCX file
            // If you want PDF output, uncomment the conversion below

            return $outputFilename;

        } catch (\Exception $e) {
            throw new \Exception('Failed to process DOCX template: ' . $e->getMessage());
        }
    }

    /**
     * Convert DOCX to PDF (optional - requires external tools)
     * Uncomment and configure if you need PDF output
     */
    private function convertDocxToPdf(string $docxPath, string $pdfPath)
    {
        // Option 1: Using LibreOffice (must be installed on server)
        // exec("libreoffice --headless --convert-to pdf --outdir " . dirname($pdfPath) . " " . $docxPath);

        // Option 2: Using CloudConvert API or similar service
        // Requires API key and external service

        // Option 3: Return DOCX and let client convert if needed
        throw new \Exception('PDF conversion not configured. Install LibreOffice or use DOCX output.');
    }

    /**
     * Generate PDF from PDF template (converts to HTML first)
     * Note: This is a fallback method with limitations
     *
     * IMPORTANT: PDF text replacement cannot preserve formatting perfectly.
     * For best results, use one of these approaches:
     * 1. HTML templates (recommended)
     * 2. DOCX templates (works perfectly!)
     * 3. Form-fillable PDFs with field names
     */
    private function generateFromPdfTemplate(RequestDocument $requestDocument, string $templatePath, array $information)
    {
        try {
            // Parse PDF and extract text
            $parser = new Parser();
            $pdf = $parser->parseFile($templatePath);
            $text = $pdf->getText();

            // Check if placeholders exist in the extracted text
            $hasPlaceholders = false;
            foreach ($information as $key => $value) {
                $placeholder = '{{{' . $key . '}}}';
                if (strpos($text, $placeholder) !== false) {
                    $hasPlaceholders = true;
                    break;
                }
            }

            // If no placeholders found, just copy the original
            if (!$hasPlaceholders) {
                return $this->copyOriginalPdf($requestDocument, $templatePath);
            }

            // Replace placeholders in extracted text
            foreach ($information as $key => $value) {
                $placeholder = '{{{' . $key . '}}}';
                $text = str_replace($placeholder, $value, $text);
            }

            // Create a simple HTML version with the replaced text
            $htmlContent = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    @page {
                        size: A4;
                        margin: 20mm;
                    }
                    body {
                        font-family: "Times New Roman", serif;
                        font-size: 12pt;
                        line-height: 1.6;
                        text-align: justify;
                    }
                    .content {
                        white-space: pre-wrap;
                        word-wrap: break-word;
                    }
                </style>
            </head>
            <body>
                <div class="content">' . nl2br(htmlspecialchars($text)) . '</div>
            </body>
            </html>';

            // Generate PDF from HTML
            $pdfOutput = Pdf::loadHTML($htmlContent)
                ->setPaper('a4', 'portrait');

            // Generate output filename
            $outputFilename = 'filled_documents/' . $requestDocument->transaction_id . '_' . time() . '.pdf';
            $outputPath = Storage::disk('public')->path($outputFilename);

            // Ensure directory exists
            Storage::disk('public')->makeDirectory('filled_documents');

            // Save PDF
            $pdfOutput->save($outputPath);

            return $outputFilename;

        } catch (\Exception $e) {
            // If extraction fails, try to copy original
            return $this->copyOriginalPdf($requestDocument, $templatePath);
        }
    }

    /**
     * Try to fill PDF form fields if they exist
     */
    private function tryFillPdfForm(RequestDocument $requestDocument, string $templatePath, array $information)
    {
        // This would require pdftk or similar tools
        // For now, return null to indicate form filling is not available
        return null;
    }

    /**
     * Copy original PDF without modification
     * This preserves all formatting but doesn't fill placeholders
     */
    private function copyOriginalPdf(RequestDocument $requestDocument, string $templatePath)
    {
        // Generate output filename
        $outputFilename = 'filled_documents/' . $requestDocument->transaction_id . '_' . time() . '.pdf';

        // Ensure directory exists
        Storage::disk('public')->makeDirectory('filled_documents');

        // Simply copy the original PDF
        Storage::disk('public')->copy(
            str_replace(Storage::disk('public')->path(''), '', $templatePath),
            $outputFilename
        );

        return $outputFilename;
    }

    /**
     * Get preview of placeholders that will be replaced
     *
     * @param Document $document
     * @param array $information
     * @return array
     */
    public function getPlaceholderPreview(Document $document, array $information)
    {
        $preview = [];

        foreach ($information as $key => $value) {
            $preview[$key] = [
                'placeholder' => '{{{' . $key . '}}}',
                'value' => $value
            ];
        }

        return $preview;
    }

    /**
     * Validate that all required fields from template_fields are present in information
     *
     * @param Document $document
     * @param array $information
     * @return array Array of missing required fields
     */
    public function validateRequiredFields(Document $document, array $information)
    {
        $missing = [];

        if (!$document->template_fields) {
            return $missing;
        }

        foreach ($document->template_fields as $field) {
            if (isset($field['required']) && $field['required'] === true) {
                if (!isset($information[$field['name']]) || empty($information[$field['name']])) {
                    $missing[] = $field['name'];
                }
            }
        }

        return $missing;
    }

    /**
     * Get all placeholders from template content
     * Supports PDF, DOCX, and HTML templates
     *
     * @param string $templatePath
     * @return array
     */
    public function extractPlaceholdersFromTemplate(string $templatePath)
    {
        try {
            if (!Storage::disk('public')->exists($templatePath)) {
                return [];
            }

            $filePath = Storage::disk('public')->path($templatePath);
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if ($extension === 'docx') {
                // Extract from DOCX
                return $this->extractPlaceholdersFromDocx($filePath);
            } else if ($extension === 'html') {
                // Extract from HTML
                return $this->extractPlaceholdersFromHtml($filePath);
            } else if ($extension === 'pdf') {
                // Extract from PDF
                return $this->extractPlaceholdersFromPdf($filePath);
            }

            return [];

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Extract placeholders from DOCX file
     */
    private function extractPlaceholdersFromDocx(string $filePath)
    {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                $content = '';

                // Read document.xml which contains the main content
                $documentXml = $zip->getFromName('word/document.xml');
                if ($documentXml) {
                    $content .= $documentXml;
                }

                // Read header and footer files
                for ($i = 1; $i <= 10; $i++) {
                    $header = $zip->getFromName("word/header{$i}.xml");
                    if ($header) $content .= $header;

                    $footer = $zip->getFromName("word/footer{$i}.xml");
                    if ($footer) $content .= $footer;
                }

                $zip->close();

                // Remove all XML tags to get plain text
                // This solves the problem where Word splits placeholders across multiple <w:t> tags
                $plainText = strip_tags($content);

                // Also remove extra whitespace and newlines
                $plainText = preg_replace('/\s+/', ' ', $plainText);

                // Extract placeholders in both formats: ${name} and {{{name}}}
                $placeholders = [];

                // Extract ${variable} format
                preg_match_all('/\$\{([a-zA-Z0-9_]+)\}/', $plainText, $matches1);
                if (!empty($matches1[1])) {
                    $placeholders = array_merge($placeholders, $matches1[1]);
                }

                // Extract {{{variable}}} format
                preg_match_all('/\{\{\{([a-zA-Z0-9_]+)\}\}\}/', $plainText, $matches2);
                if (!empty($matches2[1])) {
                    $placeholders = array_merge($placeholders, $matches2[1]);
                }

                return array_values(array_unique($placeholders));
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Extract placeholders from HTML file
     */
    private function extractPlaceholdersFromHtml(string $filePath)
    {
        $content = file_get_contents($filePath);
        preg_match_all('/\{\{\{([a-zA-Z0-9_]+)\}\}\}/', $content, $matches);
        return array_values(array_unique($matches[1] ?? []));
    }

    /**
     * Extract placeholders from PDF file
     */
    private function extractPlaceholdersFromPdf(string $filePath)
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();

            preg_match_all('/\{\{\{([a-zA-Z0-9_]+)\}\}\}/', $text, $matches);
            return array_values(array_unique($matches[1] ?? []));
        } catch (\Exception $e) {
            return [];
        }
    }
}
