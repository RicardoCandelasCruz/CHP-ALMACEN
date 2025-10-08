<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/EmailService.php';

echo "=== Test EmailService ===\n";

// Crear PDF de prueba
$pdfContent = "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n/Contents 4 0 R\n>>\nendobj\n4 0 obj\n<<\n/Length 44\n>>\nstream\nBT\n/F1 12 Tf\n100 700 Td\n(Test PDF) Tj\nET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000206 00000 n \ntrailer\n<<\n/Size 5\n/Root 1 0 R\n>>\nstartxref\n299\n%%EOF";

echo "Probando envío con cURL...\n";
$result1 = EmailService::enviarConCurl($pdfContent, 999, 'Test Usuario');
echo $result1 ? "✓ cURL: OK\n" : "✗ cURL: FALLO\n";

echo "\nProbando envío con SendGrid...\n";
$result2 = EmailService::enviarConSendGrid($pdfContent, 999, 'Test Usuario');
echo $result2 ? "✓ SendGrid: OK\n" : "✗ SendGrid: FALLO\n";

echo "\nProbando método principal...\n";
$result3 = EmailService::enviar($pdfContent, 999, 'Test Usuario');
echo $result3 ? "✓ Servicio principal: OK\n" : "✗ Servicio principal: FALLO\n";
?>