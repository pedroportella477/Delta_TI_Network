<?php
// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '@Delta477');
define('DB_NAME', 'delta_ti_network');

// Configurações do Telegram
define('TELEGRAM_BOT_TOKEN', 'seu_token_do_bot');  // Token do seu bot do Telegram
define('TELEGRAM_CHAT_ID', 'seu_chat_id');         // ID do chat onde as mensagens serão enviadas

// Função para enviar mensagem para o Telegram
function sendTelegramMessage($message) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    $data = [
        'chat_id' => TELEGRAM_CHAT_ID,
        'text' => $message
    ];
    $options = [
        'http' => [
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];
    $context  = stream_context_create($options);
    file_get_contents($url, false, $context);
}

// Estabelece a conexão com o banco de dados
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ajusta a codificação da conexão
$conn->set_charset("utf8");

// Verifica se houve erro na conexão
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
