<?php
include('config.php');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$host_id = $_GET['id'];
$host = $conn->query("SELECT name FROM hosts WHERE id = $host_id")->fetch_assoc();
$history = $conn->query("SELECT status, created_at FROM history WHERE host_id = $host_id AND created_at >= NOW() - INTERVAL 72 HOUR ORDER BY created_at DESC");

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Histórico de <?php echo htmlspecialchars($host['name'], ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
            border-radius: 8px;
        }
        .logo {
            text-align: left;
        }
        .logo img {
            height: 50px;
            border-radius: 50%;
        }
        h1 {
            color: #007BFF;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        .footer a {
            color: #007BFF;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="https://deltatelecomti.com.br/assets/images/delta1-206x206.jpg" alt="Delta TI Network">
        </div>
        <h1>Histórico de <?php echo htmlspecialchars($host['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <table>
            <tr>
                <th>Status</th>
                <th>Data e Hora</th>
            </tr>
            <?php while ($row = $history->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <div class="footer">
            Desenvolvido por <a href="http://www.deltatelecomti.com.br">Delta TI</a> © <?php echo date('Y'); ?>
        </div>
    </div>
</body>
</html>
