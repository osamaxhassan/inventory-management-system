<?php
// logic/generate_pdf.php
require '../config/db.php';
require '../libs/fpdf.php'; 

if (!isset($_GET['job_id'])) die("Error: No Job ID provided.");
$repair_id = (int)$_GET['job_id'];

try {
    $stmt = $pdo->prepare("
        SELECT ro.*, v.brand, v.model, v.registration_number, c.name, c.phone 
        FROM repair_orders ro
        JOIN vehicle v ON ro.vehicle_id = v.vehicle_id
        JOIN customer c ON v.customer_id = c.customer_id
        WHERE ro.repair_id = ?
    ");
    $stmt->execute([$repair_id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) die("Error: Job not found.");

    // ==========================================
    // START DRAWING THE MINIMALIST PDF
    // ==========================================
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Clean Minimalist Color Palette (RGB)
    $text_dark = [33, 37, 41];       // Almost black
    $text_muted = [108, 117, 125];   // Professional Gray
    $border_color = [222, 226, 230]; // Very light gray for lines

    // 1. TOP HEADER
    $pdf->SetFont('Arial', 'B', 28);
    $pdf->SetTextColor($text_dark[0], $text_dark[1], $text_dark[2]);
    $pdf->Cell(130, 12, 'AYAAN MOTORS', 0, 0, 'L');
    
    // Huge, light gray INVOICE text on the right
    $pdf->SetFont('Arial', 'B', 24);
    $pdf->SetTextColor(210, 210, 210); 
    $pdf->Cell(60, 12, 'INVOICE', 0, 1, 'R');

    // Shop Details & Invoice Number
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor($text_muted[0], $text_muted[1], $text_muted[2]);
    $pdf->Cell(130, 5, 'And Oil Lubricant', 0, 0, 'L');
    
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetTextColor($text_dark[0], $text_dark[1], $text_dark[2]);
    $pdf->Cell(60, 5, '#' . str_pad($job['repair_id'], 5, '0', STR_PAD_LEFT), 0, 1, 'R');

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor($text_muted[0], $text_muted[1], $text_muted[2]);
    $pdf->Cell(130, 5, '123 Main Tariq Road, Karachi', 0, 0, 'L');
    $pdf->Cell(60, 5, 'Date: ' . date('d M Y', strtotime($job['completed_at'] ?? $job['created_at'])), 0, 1, 'R');
    
    $pdf->Cell(190, 5, 'Phone: 0300-1234567', 0, 1, 'L');
    $pdf->Ln(12);

    // 2. CUSTOMER & VEHICLE GRID
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor($text_dark[0], $text_dark[1], $text_dark[2]);
    $pdf->Cell(95, 6, 'BILL TO', 0, 0, 'L');
    $pdf->Cell(95, 6, 'VEHICLE', 0, 1, 'L');

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor($text_muted[0], $text_muted[1], $text_muted[2]);
    $pdf->Cell(95, 5, $job['name'], 0, 0, 'L');
    $pdf->Cell(95, 5, $job['brand'] . ' ' . $job['model'], 0, 1, 'L');
    
    $pdf->Cell(95, 5, $job['phone'], 0, 0, 'L');
    $pdf->Cell(95, 5, 'Reg: ' . $job['registration_number'], 0, 1, 'L');
    $pdf->Ln(12);

    // 3. TABLE HEADER (Just a top and bottom line, no boxes)
    $pdf->SetDrawColor($border_color[0], $border_color[1], $border_color[2]);
    $pdf->SetLineWidth(0.4);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY()); // Top line
    $pdf->Ln(3);

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor($text_muted[0], $text_muted[1], $text_muted[2]);
    $pdf->Cell(90, 6, 'DESCRIPTION', 0, 0, 'L');
    $pdf->Cell(20, 6, 'QTY', 0, 0, 'C');
    $pdf->Cell(40, 6, 'PRICE', 0, 0, 'R');
    $pdf->Cell(40, 6, 'TOTAL', 0, 1, 'R');

    $pdf->Ln(2);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY()); // Bottom line of header
    $pdf->Ln(3);

    // 4. TABLE ITEMS
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor($text_dark[0], $text_dark[1], $text_dark[2]);

    // Services
    $stmt_serv = $pdo->prepare("SELECT s.service_name, rs.labor_cost FROM repair_services rs JOIN services s ON rs.service_id = s.service_id WHERE rs.repair_id = ?");
    $stmt_serv->execute([$repair_id]);
    while ($srv = $stmt_serv->fetch(PDO::FETCH_ASSOC)) {
        $pdf->Cell(90, 8, $srv['service_name'] . ' (Labor)', 0, 0, 'L');
        $pdf->Cell(20, 8, '1', 0, 0, 'C');
        $pdf->Cell(40, 8, number_format($srv['labor_cost']), 0, 0, 'R');
        $pdf->Cell(40, 8, number_format($srv['labor_cost']), 0, 1, 'R');
    }

    // Parts
    $stmt_parts = $pdo->prepare("SELECT p.part_name, rp.quantity, rp.selling_price FROM repair_parts rp JOIN parts p ON rp.part_id = p.part_id WHERE rp.repair_id = ?");
    $stmt_parts->execute([$repair_id]);
    while ($prt = $stmt_parts->fetch(PDO::FETCH_ASSOC)) {
        $pdf->Cell(90, 8, $prt['part_name'], 0, 0, 'L');
        $pdf->Cell(20, 8, $prt['quantity'], 0, 0, 'C');
        $pdf->Cell(40, 8, number_format($prt['selling_price']), 0, 0, 'R');
        $pdf->Cell(40, 8, number_format($prt['quantity'] * $prt['selling_price']), 0, 1, 'R');
    }

    $pdf->Ln(3);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY()); // Bottom line of all items
    $pdf->Ln(6);

    // 5. TOTALS ALIGNED RIGHT
    $pdf->SetFont('Arial', '', 10);
    
    $pdf->Cell(110, 6, '', 0, 0); // Spacer
    $pdf->Cell(40, 6, 'Subtotal', 0, 0, 'R');
    $pdf->Cell(40, 6, number_format($job['total_amount']), 0, 1, 'R');

    if ($job['discount'] > 0) {
        $pdf->Cell(110, 6, '', 0, 0);
        $pdf->Cell(40, 6, 'Discount', 0, 0, 'R');
        $pdf->Cell(40, 6, '-' . number_format($job['discount']), 0, 1, 'R');
    }

    $pdf->Cell(110, 6, '', 0, 0);
    $pdf->Cell(40, 6, 'Tax (' . $job['tax_rate'] . '%)', 0, 0, 'R');
    $pdf->Cell(40, 6, '+' . number_format($job['tax_amount']), 0, 1, 'R');

    $pdf->Ln(4);
    
    // Grand Total (Bold)
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(110, 8, '', 0, 0);
    $pdf->Cell(40, 8, 'TOTAL (PKR)', 0, 0, 'R');
    $pdf->Cell(40, 8, number_format($job['final_amount']), 0, 1, 'R');

    // 6. FOOTER
    $pdf->SetY(-30);
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor($text_muted[0], $text_muted[1], $text_muted[2]);
    $pdf->Cell(0, 5, 'Thank you for your business.', 0, 1, 'C');
    $pdf->Cell(0, 5, 'All repair work is guaranteed for 30 days. Parts warranties vary by manufacturer.', 0, 1, 'C');

    $pdf->Output('I', 'Invoice_AyaanMotors_' . str_pad($repair_id, 5, '0', STR_PAD_LEFT) . '.pdf');

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>