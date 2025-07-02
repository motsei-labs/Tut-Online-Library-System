<?php
require_once('../tcpdf/tcpdf.php'); // Include TCPDF library
include('../includes/config.php'); // Include database configuration

// Create new PDF document
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Library Management System');
$pdf->SetTitle('Issued Books Report');
$pdf->SetHeaderData(null, 0, 'Issued Books Report', 'Generated on: ' . date('Y-m-d')); // No logo

// Set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set font
$pdf->SetFont('helvetica', '', 10);

// Add a page
$pdf->AddPage();

// Fetch issued books data
$sql = "SELECT tblstudents.FullName, tblbooks.BookName, tblbooks.ISBNNumber, tblissuedbookdetails.IssuesDate, tblissuedbookdetails.ReturnDate 
        FROM tblissuedbookdetails 
        JOIN tblstudents ON tblstudents.StudentId = tblissuedbookdetails.StudentId 
        JOIN tblbooks ON tblbooks.id = tblissuedbookdetails.BookId 
        ORDER BY tblissuedbookdetails.id DESC";
$query = $dbh->prepare($sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

// Create HTML content for the PDF
$html = '<h2>Issued Books Report</h2>';
$html .= '<table border="1" cellspacing="3" cellpadding="4">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Book Name</th>
                    <th>ISBN</th>
                    <th>Issued Date</th>
                    <th>Return Date</th>
                </tr>
            </thead>
            <tbody>';

$cnt = 1;
foreach ($results as $result) {
    $returnDate = $result->ReturnDate ? htmlentities($result->ReturnDate) : 'Not Returned Yet';
    $html .= '<tr>
                <td>' . $cnt . '</td>
                <td>' . htmlentities($result->FullName) . '</td>
                <td>' . htmlentities($result->BookName) . '</td>
                <td>' . htmlentities($result->ISBNNumber) . '</td>
                <td>' . htmlentities($result->IssuesDate) . '</td>
                <td>' . $returnDate . '</td>
              </tr>';
    $cnt++;
}

$html .= '</tbody></table>';

// Output the HTML content to the PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('Issued_Books_Report.pdf', 'D');
?>