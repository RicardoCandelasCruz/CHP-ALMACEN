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
        $apiKey = getenv('SENDGRID_API_KEY');
        if (!$apiKey) return false;
        
        $data = [
            'personalizations' => [[
                'to' => [['email' => SMTP_FROM_EMAIL]],
                'subject' => "Nuevo Pedido #{$pedidoId} - Cheese Pizza Almacen"
            ]],
            'from' => ['email' => SMTP_USER, 'name' => 'Cheese Pizza Almacen'],
            'content' => [[
                'type' => 'text/html',
                'value' => "Se ha generado un nuevo pedido:<br><br>Número de Pedido: {$pedidoId}<br>Cliente: {$nombreUsuario}<br>Fecha: " . date('d/m/Y H:i:s')
            ]],
            'attachments' => [[
                'content' => base64_encode($pdfContent),
                'type' => 'application/pdf',
                'filename' => "pedido_{$pedidoId}.pdf"
            ]]
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.sendgrid.com/v3/mail/send',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 202) {
            error_log("Correo enviado con SendGrid para pedido #{$pedidoId}");
            return true;
        }
        
        error_log("SendGrid error: " . $response);
        return false;
    }
    
    /**
     * Método principal que prueba múltiples servicios
     */
    public static function enviar(string $pdfContent, int $pedidoId, string $nombreUsuario): bool {
        // Intentar con SendGrid primero (más confiable)
        if (self::enviarConSendGrid($pdfContent, $pedidoId, $nombreUsuario)) {
            return true;
        }
        
        // Fallback a cURL SMTP
        return self::enviarConCurl($pdfContent, $pedidoId, $nombreUsuario);
    }
}
?>