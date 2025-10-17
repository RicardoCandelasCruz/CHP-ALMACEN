<?php
class EmailService {
    
    /**
     * Envía correo usando cURL directo con SMTP
     */
    public static function enviarConCurl(string $pdfContent, int $pedidoId, string $nombreUsuario): bool {
        // Siempre intentar enviar correo
        
        $boundary = md5(time());
        $headers = [
            "MIME-Version: 1.0",
            "Content-Type: multipart/mixed; boundary=\"{$boundary}\"",
            "From: " . SMTP_USER,
            "To: " . SMTP_FROM_EMAIL,
            "Subject: Nuevo Pedido #{$pedidoId} - Cheese Pizza Almacen"
        ];
        
        $body = "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $body .= "Se ha generado un nuevo pedido:<br><br>";
        $body .= "Número de Pedido: {$pedidoId}<br>";
        $body .= "Cliente: {$nombreUsuario}<br>";
        $body .= "Fecha: " . date('d/m/Y H:i:s') . "\r\n\r\n";
        
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: application/pdf; name=\"pedido_{$pedidoId}.pdf\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n";
        $body .= "Content-Disposition: attachment; filename=\"pedido_{$pedidoId}.pdf\"\r\n\r\n";
        $body .= chunk_split(base64_encode($pdfContent)) . "\r\n";
        $body .= "--{$boundary}--";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "smtps://" . SMTP_HOST . ":" . SMTP_PORT,
            CURLOPT_USE_SSL => CURLUSESSL_ALL,
            CURLOPT_USERNAME => SMTP_USER,
            CURLOPT_PASSWORD => SMTP_PASS,
            CURLOPT_MAIL_FROM => SMTP_USER,
            CURLOPT_MAIL_RCPT => [SMTP_FROM_EMAIL],
            CURLOPT_POSTFIELDS => implode("\r\n", $headers) . "\r\n\r\n" . $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("cURL SMTP error: " . $error);
            return false;
        }
        
        error_log("Correo enviado con cURL para pedido #{$pedidoId}");
        return true;
    }
    
    /**
     * Envía correo usando SendGrid API (alternativa profesional)
     */
    public static function enviarConSendGrid(string $pdfContent, int $pedidoId, string $nombreUsuario): bool {
        error_log("Debug SendGrid - USE_SENDGRID: " . (USE_SENDGRID ? 'true' : 'false'));
        error_log("Debug SendGrid - API Key presente: " . (SENDGRID_API_KEY ? 'SI' : 'NO'));
        
        if (!USE_SENDGRID || !SENDGRID_API_KEY) {
            error_log("SendGrid no configurado o API key faltante");
            return false;
        }
        
        $data = [
            'personalizations' => [[
                'to' => [['email' => SMTP_FROM_EMAIL]],
                'subject' => "Nuevo Pedido #{$pedidoId} - Cheese Pizza Almacen"
            ]],
            'from' => ['email' => SMTP_FROM_EMAIL, 'name' => 'Cheese Pizza Almacen'],
            'content' => [[
                'type' => 'text/html',
                'value' => "<h3>Nuevo Pedido Generado</h3><br>"
                         . "<strong>Número de Pedido:</strong> {$pedidoId}<br>"
                         . "<strong>Cliente:</strong> {$nombreUsuario}<br>"
                         . "<strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "<br><br>"
                         . "<p>El PDF del pedido se encuentra adjunto a este correo.</p>"
            ]],
            'attachments' => [[
                'content' => base64_encode($pdfContent),
                'type' => 'application/pdf',
                'filename' => "pedido_{$pedidoId}.pdf",
                'disposition' => 'attachment'
            ]]
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.sendgrid.com/v3/mail/send',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . SENDGRID_API_KEY,
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            error_log("SendGrid cURL error: " . $curlError);
            return false;
        }
        
        if ($httpCode === 202) {
            error_log("Correo enviado exitosamente con SendGrid para pedido #{$pedidoId}");
            return true;
        }
        
        error_log("SendGrid error (HTTP {$httpCode}): " . $response);
        return false;
    }
    
    /**
     * Método principal que prueba múltiples servicios
     */
    public static function enviar(string $pdfContent, int $pedidoId, string $nombreUsuario): bool {
        // Prioridad 1: SendGrid API (más confiable en Railway)
        if (USE_SENDGRID && self::enviarConSendGrid($pdfContent, $pedidoId, $nombreUsuario)) {
            return true;
        }
        
        // Fallback: cURL SMTP (probablemente fallará en Railway)
        error_log("SendGrid no disponible, intentando SMTP tradicional...");
        return self::enviarConCurl($pdfContent, $pedidoId, $nombreUsuario);
    }
}
?>