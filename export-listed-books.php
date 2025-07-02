<?php
require_once('tcpdf/tcpdf.php'); // Include TCPDF library
include('includes/config.php');

// Create new PDF document
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Library Management System');
$pdf->SetTitle('Issued Books');
$pdf->SetHeaderData('', 0, 'Issued Books Report', '');
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(15, 27, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage();

// Fetch data from the database
$sql = "SELECT tblbooks.BookName, tblcategory.CategoryName, tblauthors.AuthorName, tblbooks.ISBNNumber, tblbooks.bookQty,  
               COUNT(tblissuedbookdetails.id) AS issuedBooks,
               COUNT(tblissuedbookdetails.RetrunStatus) AS returnedbook
        FROM tblbooks
        LEFT JOIN tblissuedbookdetails ON tblissuedbookdetails.BookId = tblbooks.id
        LEFT JOIN tblauthors ON tblauthors.id = tblbooks.AuthorId
        LEFT JOIN tblcategory ON tblcategory.id = tblbooks.CatId
        GROUP BY tblbooks.id";
$query = $dbh->prepare($sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

// Generate HTML content for the PDF
$html = '<h3>Issued Books Report</h3>';
$html .= '<table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Book Name</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Quantity</th>
                    <th>Issued</th>
                    <th>Available</th>
                </tr>
            </thead>
            <tbody>';

$cnt = 1;
foreach ($results as $result) {
    $availableQty = $result->bookQty - ($result->issuedBooks - $result->returnedbook);
    $html .= '<tr>
                <td>' . $cnt . '</td>
                <td>' . htmlentities($result->BookName) . '</td>
                <td>' . htmlentities($result->AuthorName) . '</td>
                <td>' . htmlentities($result->ISBNNumber) . '</td>
                <td>' . htmlentities($result->bookQty) . '</td>
                <td>' . htmlentities($result->issuedBooks) . '</td>
                <td>' . htmlentities($availableQty) . '</td>
              </tr>';
    $cnt++;
}

$html .= '</tbody></table>';

// Add content to the PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Output the PDF
$pdf->Output('Listed_Books_Report.pdf', 'D');
?>