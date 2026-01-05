<?php
// pdf_generator.php - Generator PDF simplu folosind HTML/CSS

/**
 * Generează un PDF pentru comenzile de transport
 */
function generate_orders_pdf($orders) {
    // Setează headers pentru PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="raport_comenzi_' . date('Y-m-d') . '.pdf"');
    
    // În lipsa unei librării PDF (care ar necesita Composer), vom genera un HTML 
    // care poate fi convertit în PDF de browser sau poate fi salvat și printat ca PDF
    // Pentru un PDF adevărat, ar trebui folosit TCPDF sau dompdf
    
    // Alternativ, trimitem un HTML stilizat pentru printare ca PDF
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: inline; filename="raport_comenzi_' . date('Y-m-d') . '.html"');
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Raport Comenzi Transport</title>
        <style>
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                font-size: 11pt;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #333;
                padding-bottom: 15px;
            }
            .header h1 {
                margin: 0;
                color: #333;
            }
            .header p {
                margin: 5px 0;
                color: #666;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            th {
                background: #4CAF50;
                color: white;
                padding: 10px;
                text-align: left;
                border: 1px solid #ddd;
            }
            td {
                padding: 8px;
                border: 1px solid #ddd;
            }
            tr:nth-child(even) {
                background: #f9f9f9;
            }
            .footer {
                margin-top: 30px;
                text-align: center;
                color: #666;
                font-size: 10pt;
                border-top: 1px solid #ddd;
                padding-top: 15px;
            }
            .print-btn {
                background: #2196F3;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
                margin-bottom: 20px;
            }
            .print-btn:hover {
                background: #0b7dda;
            }
        </style>
    </head>
    <body>
        <button class="print-btn no-print" onclick="window.print()">🖨️ Printează / Salvează ca PDF</button>
        
        <div class="header">
            <h1>Black Shield Logistics</h1>
            <p>Raport Comenzi de Transport</p>
            <p>Generat: <?php echo date('d.m.Y H:i'); ?></p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Preluare</th>
                    <th>Livrare</th>
                    <th>Tip Marfă</th>
                    <th>Greutate</th>
                    <th>Securitate</th>
                    <th>Data</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                    <td><?php echo htmlspecialchars($order['pickup_location']); ?></td>
                    <td><?php echo htmlspecialchars($order['delivery_location']); ?></td>
                    <td><?php echo htmlspecialchars($order['cargo_type']); ?></td>
                    <td><?php echo htmlspecialchars($order['cargo_weight']); ?> kg</td>
                    <td><?php echo htmlspecialchars($order['security_level']); ?></td>
                    <td><?php echo htmlspecialchars($order['pickup_date']); ?></td>
                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="footer">
            <p><strong>Black Shield Logistics</strong> - Transport Securizat Professional</p>
            <p>Total comenzi: <?php echo count($orders); ?></p>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Generează un document Word pentru comenzi
 */
function generate_orders_word($orders) {
    header('Content-Type: application/vnd.ms-word; charset=UTF-8');
    header('Content-Disposition: attachment; filename="raport_comenzi_' . date('Y-m-d') . '.doc"');
    
    echo "\xEF\xBB\xBF"; // BOM pentru UTF-8
    ?>
    <html xmlns:o="urn:schemas-microsoft-com:office:office" 
          xmlns:w="urn:schemas-microsoft-com:office:word"
          xmlns="http://www.w3.org/TR/REC-html40">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style>
            body { font-family: Arial, sans-serif; }
            h1 { color: #333; text-align: center; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th { background: #4CAF50; color: white; padding: 10px; border: 1px solid #ddd; }
            td { padding: 8px; border: 1px solid #ddd; }
        </style>
    </head>
    <body>
        <h1>Black Shield Logistics</h1>
        <h2>Raport Comenzi de Transport</h2>
        <p>Generat: <?php echo date('d.m.Y H:i'); ?></p>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Preluare</th>
                    <th>Livrare</th>
                    <th>Tip Marfă</th>
                    <th>Greutate</th>
                    <th>Securitate</th>
                    <th>Data</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                    <td><?php echo htmlspecialchars($order['pickup_location']); ?></td>
                    <td><?php echo htmlspecialchars($order['delivery_location']); ?></td>
                    <td><?php echo htmlspecialchars($order['cargo_type']); ?></td>
                    <td><?php echo htmlspecialchars($order['cargo_weight']); ?> kg</td>
                    <td><?php echo htmlspecialchars($order['security_level']); ?></td>
                    <td><?php echo htmlspecialchars($order['pickup_date']); ?></td>
                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <p style="margin-top: 30px; text-align: center;">
            <strong>Total comenzi: <?php echo count($orders); ?></strong>
        </p>
    </body>
    </html>
    <?php
}
?>
