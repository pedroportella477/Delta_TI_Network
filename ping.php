<?php
include('config.php');

// Estabelece a conexão com o banco de dados
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verifica se houve erro na conexão
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define o tempo atual para inserir no histórico
$current_time = date('Y-m-d H:i:s');

// Seleciona todos os hosts registrados no banco de dados
$hosts_result = $conn->query("SELECT * FROM hosts");

// Obtém as mensagens configuradas
$messages_result = $conn->query("SELECT * FROM messages");
$messages = $messages_result->fetch_assoc();

// Função para verificar o status e atualizar o banco de dados
function updateHostStatus($conn, $host) {
    $ip = $host['ip'];
    $name = $host['name'];

    // Executa o comando ping
    $output = [];
    exec("ping -c 1 -W 5 $ip", $output, $status);

    // Define o status com base no resultado do ping
    $new_status = ($status == 0) ? 'up' : 'down';

    // Verifica o status atual no banco de dados
    if ($host['status'] != $new_status) {
        // Atualiza o status do host
        $conn->query("UPDATE hosts SET status = '$new_status' WHERE id = " . $host['id']);

        // Insere um registro no histórico
        $conn->query("INSERT INTO history (host_id, status, check_time) VALUES (" . $host['id'] . ", '$new_status', '$current_time')");

        // Envia mensagem para o Telegram
        sendTelegramMessage("O equipamento $name com IP $ip está $new_status. Mensagem: " . (($new_status == 'up') ? $messages['up_msg'] : $messages['down_msg']));
    }
}

// Itera sobre cada host para atualizar o status
while ($host = $hosts_result->fetch_assoc()) {
    updateHostStatus($conn, $host);
}

// Fecha a conexão com o banco de dados
$conn->close();

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
?>
