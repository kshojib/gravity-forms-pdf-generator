<?php
/**
 * Plugin Name: Gravity Forms PDF Generator
 * Description: Generates a PDF from Gravity Forms submissions using TCPDF.
 * Version: 1.0
 * Author: Shojib Khan
 */

 if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Enqueue TCPDF and FPDI Libraries
function gf_pdf_enqueue_libraries() {
    require_once plugin_dir_path( __FILE__ ) . 'tcpdf/tcpdf.php';
    require_once plugin_dir_path( __FILE__ ) . 'fpdi/src/autoload.php';
}
add_action( 'init', 'gf_pdf_enqueue_libraries' );

// Hook into Gravity Forms submission
add_action( 'gform_after_submission', 'gf_pdf_generate_from_form', 10, 2 );
function gf_pdf_generate_from_form( $entry, $form ) {
    // Path to your PDF template
    $template_path = plugin_dir_path( __FILE__ ) . 'templates/911.pdf';

    // Create new PDF document using TCPDF and FPDI
    $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your Site Name');
    $pdf->SetTitle('Generated PDF');
    $pdf->SetSubject('Form Submission');

    // Import the existing PDF template
    $pageCount = $pdf->setSourceFile($template_path);
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $templateId = $pdf->importPage($pageNo);
        $pdf->AddPage();
        $pdf->useTemplate($templateId);

        // Get the form data
        $field1 = rgar($entry, '1'); // Replace '1' with the actual field ID
        $field2 = rgar($entry, '3'); // Replace '2' with the actual field ID
        $field3 = rgar($entry, '5'); // Replace '2' with the actual field ID

        $fontpath = plugin_dir_path( __FILE__ ) . 'tcpdf/fonts/dejavusans.ttf';
        TCPDF_FONTS::addTTFfont($fontpath, 'TrueTypeUnicode', '', 32);
        $pdf->SetFont('dejavusans', '', 12);

        
        // Overlay form data on the template
        if ($pageNo == 1) {
            $pdf->SetXY(70, 30); // Adjust the position as needed
            $pdf->Write(0, "Name: $field1");
            $pdf->SetXY(70, 40); // Adjust the position as needed

            // $pdf->SetFont('helvetica', '', 10);
            $pdf->Write(0, "Email: $field2");
        } elseif ($pageNo == 2) {
            $pdf->SetXY(10, 20); // Adjust the position as needed
            $pdf->Write(0, "Message: $field3");
        }
    }

    // Output the PDF to a string
    $pdf_output = $pdf->Output('', 'S');

    // Save the PDF to the server (optional)
    $upload_dir = wp_upload_dir();
    $pdf_path = $upload_dir['path'] . '/generated_pdf_' . $entry['id'] . '.pdf';
    file_put_contents($pdf_path, $pdf_output);

    // Optionally, send the PDF as an email attachment
    $to = rgar($entry, '2'); // Replace with the email field ID
    $subject = 'Your Form Submission';
    $body = 'Please find the attached PDF.';
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $attachments = array($pdf_path);

    wp_mail($to, $subject, $body, $headers, $attachments);
}