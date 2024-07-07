<?php
include('config.php');
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ajustar a codificação da conexão
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$hosts = $conn->query("SELECT * FROM hosts");

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Delta TI Network</title>
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
            border-radius: 50%; /* Deixa a logo arredondada */
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
        .status {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
        }
        .online {
            background-color: green;
        }
        .offline {
            background-color: red;
        }
    </style>
    <script>
        function getStatus(ip) {
            return new Promise((resolve, reject) => {
                fetch(`status.php?ip=${ip}`)
                    .then(response => response.json())
                    .then(data => resolve(data.status))
                    .catch(error => reject(error));
            });
        }

        async function refreshStatus() {
            const statusPromises = [];
            const statusElements = document.querySelectorAll('.status');
            
            statusElements.forEach(element => {
                const ip = element.dataset.ip;
                statusPromises.push(getStatus(ip));
            });

            Promise.all(statusPromises)
                .then(results => {
                    results.forEach((status, index) => {
                        statusElements[index].className = 'status ' + status;
                        statusElements[index].title = status.charAt(0).toUpperCase() + status.slice(1);
                    });
                })
                .catch(error => console.error('Error updating status:', error));
        }

        setInterval(refreshStatus, 20000); // Atualiza a cada 20 segundos
        window.onload = refreshStatus;
    </script>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="https://deltatelecomti.com.br/assets/images/delta1-206x206.jpg" alt="Delta TI Network">
        </div>
        <h1>Delta TI Network</h1>
        <h2>Dispositivos Monitorados</h2>
        <table>
            <tr>
                <th>Nome</th>
                <th>IP</th>
                <th>Status</th>
                <th>Histórico</th>
            </tr>
            <?php while ($row = $hosts->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['ip'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <div class="status" id="status-<?php echo $row['id']; ?>" data-ip="<?php echo $row['ip']; ?>"></div>
                </td>
                <td><a href="history.php?id=<?php echo $row['id']; ?>">Ver Histórico</a></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <div class="footer">
            Desenvolvido por <a href="http://www.deltatelecomti.com.br">Delta TI</a> © <?php echo date('Y'); ?>
        </div>
    </div>
</body>
</html>
