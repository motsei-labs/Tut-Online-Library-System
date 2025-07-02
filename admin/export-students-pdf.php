<?php
// filepath: c:\xampp\htdocs\library\admin\export-students-pdf.php
require_once('../TCPDF/tcpdf.php');
include('includes/config.php');

// Create new PDF document
$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Library Management System');
$pdf->SetTitle('Registered Students');
$pdf->SetHeaderData('', 0, 'Registered Students', 'Generated on: ' . date('Y-m-d'));

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

// Fetch data from the database
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT * FROM tblstudents";
if ($search !== '') {
    $sql .= " WHERE (StudentId LIKE :search OR FullName LIKE :search OR EmailId LIKE :search OR MobileNumber LIKE :search OR RegDate LIKE :search)";
}

$query = $dbh->prepare($sql);
if ($search !== '') {
    $like = "%$search%";
    $query->bindParam(':search', $like, PDO::PARAM_STR);
}
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

// Create HTML content for the PDF
$html = '<h2>Registered Students</h2>';
$html .= '<table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Email ID</th>
                    <th>Mobile Number</th>
                    <th>Reg Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';

$cnt = 1;
foreach ($results as $result) {
    $status = $result->Status == 1 ? 'Active' : 'Blocked';
    $html .= '<tr>
                <td>' . $cnt . '</td>
                <td>' . htmlentities($result->StudentId) . '</td>
                <td>' . htmlentities($result->FullName) . '</td>
                <td>' . htmlentities($result->EmailId) . '</td>
                <td>' . htmlentities($result->MobileNumber) . '</td>
                <td>' . htmlentities($result->RegDate) . '</td>
                <td>' . $status . '</td>
              </tr>';
    $cnt++;
}

$html .= '</tbody></table>';

// Output the HTML content to the PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('Registered_Students.pdf', 'D');
?>