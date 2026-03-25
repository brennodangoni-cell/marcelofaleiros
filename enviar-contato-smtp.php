<?php
/**
 * Script para enviar emails do formulário de contato via SMTP HostGator
 * Versão melhorada com PHPMailer (opcional)
 */

// Configurações SMTP HostGator
$smtp_host = 'mail.marcelofaleirosterapeuta.com'; // ou smtp.hostgator.com.br
$smtp_port = 587; // Porta TLS (ou 465 para SSL)
$smtp_usuario = 'contato@marcelofaleirosterapeuta.com';
$smtp_senha = 'VVNxM$rjP3\'Ouqr';

// Configurações do email
$email_destino = 'Faleiros_aguiar@hotmail.com'; // Email que receberá as mensagens
$email_remetente = 'contato@marcelofaleirosterapeuta.com';
$nome_remetente = 'Site - Dr. Marcelo Faleiros';

// Headers para prevenir spam
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Receber dados do formulário
$nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';
$mensagem = isset($_POST['mensagem']) ? trim($_POST['mensagem']) : '';

// Validação básica
if (empty($nome) || empty($email) || empty($telefone) || empty($mensagem)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

// Preparar o email HTML com as cores do site
$assunto = "Nova mensagem de contato - " . htmlspecialchars($nome);

$cor_verde = '#7ed957';
$cor_preto = '#000000';
$cor_cinza_escuro = '#1a1a1a';
$cor_cinza = '#2d2d2d';
$cor_branco = '#ffffff';
$cor_cinza_claro = '#cccccc';

$corpo_email = '
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: \'Poppins\', \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; background-color: ' . $cor_cinza_escuro . ';">
    <table width="100%" cellpadding="0" cellspacing="0" style="background: linear-gradient(135deg, ' . $cor_preto . ' 0%, ' . $cor_cinza_escuro . ' 50%, ' . $cor_cinza . ' 100%); padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: rgba(26, 26, 26, 0.95); border-radius: 20px; border: 1px solid rgba(126, 217, 87, 0.2); overflow: hidden; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: rgba(126, 217, 87, 0.1); padding: 30px; text-align: center; border-bottom: 2px solid rgba(126, 217, 87, 0.3);">
                            <h1 style="color: ' . $cor_verde . '; font-family: \'Playfair Display\', \'Merriweather\', serif; font-size: 28px; font-weight: 700; margin: 0; letter-spacing: -0.02em;">
                                Nova Mensagem de Contato
                            </h1>
                            <p style="color: ' . $cor_cinza_claro . '; font-size: 14px; margin: 10px 0 0 0; opacity: 0.8;">
                                Dr. Marcelo Faleiros - Psicoterapeuta
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            
                            <!-- Greeting -->
                            <p style="color: ' . $cor_branco . '; font-size: 16px; line-height: 1.7; margin: 0 0 30px 0;">
                                Olá,<br><br>
                                Você recebeu uma nova mensagem através do formulário de contato do seu site:
                            </p>
                            
                            <!-- Info Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background: rgba(0, 0, 0, 0.4); border-left: 4px solid ' . $cor_verde . '; border-radius: 8px; margin: 30px 0; padding: 25px;">
                                <tr>
                                    <td>
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding: 12px 0; border-bottom: 1px solid rgba(126, 217, 87, 0.1);">
                                                    <span style="color: ' . $cor_verde . '; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.1em;">Nome:</span>
                                                    <p style="color: ' . $cor_branco . '; font-size: 16px; margin: 8px 0 0 0; font-weight: 500;">' . htmlspecialchars($nome) . '</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 12px 0; border-bottom: 1px solid rgba(126, 217, 87, 0.1);">
                                                    <span style="color: ' . $cor_verde . '; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.1em;">E-mail:</span>
                                                    <p style="color: ' . $cor_branco . '; font-size: 16px; margin: 8px 0 0 0;">
                                                        <a href="mailto:' . htmlspecialchars($email) . '" style="color: ' . $cor_verde . '; text-decoration: none;">' . htmlspecialchars($email) . '</a>
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 12px 0; border-bottom: 1px solid rgba(126, 217, 87, 0.1);">
                                                    <span style="color: ' . $cor_verde . '; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.1em;">Telefone/WhatsApp:</span>
                                                    <p style="color: ' . $cor_branco . '; font-size: 16px; margin: 8px 0 0 0;">
                                                        ' . htmlspecialchars($telefone) . '
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 12px 0;">
                                                    <span style="color: ' . $cor_verde . '; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.1em;">Mensagem:</span>
                                                    <p style="color: ' . $cor_cinza_claro . '; font-size: 15px; margin: 12px 0 0 0; line-height: 1.8; white-space: pre-wrap; font-style: italic;">' . nl2br(htmlspecialchars($mensagem)) . '</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Action Buttons -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <table cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="background-color: #5cb83d; border-radius: 50px; padding: 14px 35px;">
                                                    <a href="mailto:' . htmlspecialchars($email) . '?subject=Re: Sua mensagem de contato" style="color: ' . $cor_branco . '; text-decoration: none; font-weight: 600; font-size: 15px; display: inline-block;">
                                                        Responder por E-mail
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Footer -->
                            <p style="color: ' . $cor_cinza_claro . '; font-size: 12px; line-height: 1.6; margin: 40px 0 0 0; padding-top: 30px; border-top: 1px solid rgba(126, 217, 87, 0.1); text-align: center; opacity: 0.7;">
                                Esta mensagem foi enviada automaticamente através do formulário de contato do site.<br>
                                <span style="color: ' . $cor_verde . ';">Dr. Marcelo Faleiros - Psicoterapeuta</span>
                            </p>
                            
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

// Usar função mail() nativa (mais simples e funciona na maioria dos casos)
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: " . $nome_remetente . " <" . $email_remetente . ">\r\n";
$headers .= "Reply-To: " . htmlspecialchars($nome) . " <" . $email . ">\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

// Tentar enviar o email
$enviado = @mail($email_destino, $assunto, $corpo_email, $headers);

if ($enviado) {
    http_response_code(200);
    echo json_encode([
        'success' => true, 
        'message' => 'Mensagem enviada com sucesso! Entraremos em contato em breve.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao enviar mensagem. Por favor, tente novamente ou entre em contato pelo WhatsApp.'
    ]);
}
?>
