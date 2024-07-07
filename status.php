<?php
include('config.php');
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$hosts = $conn->query("SELECT * FROM hosts");
$statuses = [];

while ($row = $hosts->fetch_assoc()) {
    $ip = $row['ip'];
    $host_id = $row['id'];
    $status = exec("ping -c 1 $ip", $outcome, $status_code) == 0 ? 'online' : 'offline';
    
    // Verificar se o status mudou e registrar no histórico
    $last_status_query = $conn->query("SELECT status FROM history WHERE host_id = $host_id ORDER BY timestamp DESC LIMIT 1");
    $last_status = $last_status_query->fetch_assoc();
    
    if ($last_status['status'] != $status) {
        $stmt = $conn->prepare("INSERT INTO history (host_id, status) VALUES (?, ?)");
        $stmt->bind_param("is", $host_id, $status);
        $stmt->execute();
        $stmt->close();
    }
    
    $statuses[] = ['id' => $host_id, 'status' => $status];
}

header('Content-Type: application/json');
echo json_encode($statuses);

$conn->close();
?>
