<?php
include('config.php');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$hosts = $conn->query("SELECT * FROM hosts");

while ($row = $hosts->fetch_assoc()) {
    $ip = $row['ip'];
    $host_id = $row['id'];
    $status = exec("ping -c 1 $ip", $outcome, $status_code) == 0 ? 'online' : 'offline';
    
    // Insert status into history
    $stmt = $conn->prepare("INSERT INTO history (host_id, status, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $host_id, $status);
    $stmt->execute();
    $stmt->close();

    // Update host status
    if ($status == 'online') {
        $new_status = 'up';
    } else {
        $new_status = 'down';
    }

    // Check if status has changed
    $current_status = $row['status'];
    if ($current_status != $new_status) {
        $stmt = $conn->prepare("UPDATE hosts SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $host_id);
        $stmt->execute();
        $stmt->close();

        // Send Telegram message
        $message = "O equipamento {$row['name']} com IP {$row['ip']} está agora $new_status.";
        sendTelegramMessage($message);
    }
}

$conn->close();

function sendTelegramMessage($message) {
    global $conn;
    
    // Fetch messages from database
    $messages = $conn->query("SELECT * FROM messages")->fetch_assoc();

    // Send message to Telegram
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
