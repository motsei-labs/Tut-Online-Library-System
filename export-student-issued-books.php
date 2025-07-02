<?php
session_start();
error_reporting(0);
require_once('tcpdf/tcpdf.php');
include('includes/config.php');

if(strlen($_SESSION['login'])==0) {   
    header('location:index.php');
    exit();
}

$sid = $_SESSION['stdid'];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch issued books for the logged-in student
$sql = "SELECT tblbooks.BookName, tblbooks.ISBNNumber, tblissuedbookdetails.IssuesDate, tblissuedbookdetails.ReturnDate, tblissuedbookdetails.fine
        FROM tblissuedbookdetails
        JOIN tblstudents ON tblstudents.StudentId = tblissuedbookdetails.StudentId
        JOIN tblbooks ON tblbooks.id = tblissuedbookdetails.BookId
        WHERE tblstudents.StudentId = :sid";

if ($search !== '') {
    $sql .= " AND (tblbooks.BookName LIKE :search OR tblbooks.ISBNNumber LIKE :search OR tblissuedbookdetails.IssuesDate LIKE :search OR tblissuedbookdetails.ReturnDate LIKE :search OR tblissuedbookdetails.fine LIKE :search)";
}
$sql .= " ORDER BY tblissuedbookdetails.id DESC";

$query = $dbh->prepare($sql);
$query->bindParam(':sid', $sid, PDO::PARAM_STR);
if ($search !== '') {
    $like = "%$search%";
    $query->bindParam(':search', $like, PDO::PARAM_STR);
}
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

// Create new PDF document
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Library Management System');
$pdf->SetTitle('My Issued Books');
$pdf->SetHeaderData(null, 0, 'My Issued Books', 'Generated on: ' . date('Y-m-d'));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage();

// Build HTML table
$html = '<h2>My Issued Books</h2>';
$html .= '<table border="1" cellspacing="3" cellpadding="4">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Book Name</th>
                    <th>ISBN</th>
                    <th>Issued Date</th>
                    <th>Return Date</th>
                    <th>Fine (USD)</th>
                </tr>
            </thead>
            <tbody>';

$cnt = 1;
foreach ($results as $result) {
    $returnDate = $result->ReturnDate ? htmlentities($result->ReturnDate) : 'Not Return Yet';
    $html .= '<tr>
                <td>' . $cnt . '</td>
                <td>' . htmlentities($result->BookName) . '</td>
                <td>' . htmlentities($result->ISBNNumber) . '</td>
                <td>' . htmlentities($result->IssuesDate) . '</td>
                <td>' . $returnDate . '</td>
                <td>' . htmlentities($result->fine) . '</td>
              </tr>';
    $cnt++;
}
$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('My_Issued_Books.pdf', 'D');
?>
<a href="#" id="exportPDF" class="btn btn-success pull-right" style="margin-top: -5px;">Export to PDF</a>