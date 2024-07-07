<?php
include('config.php');
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ajustar a codificação da conexão
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $ip = $conn->real_escape_string($_POST['ip']);
        $conn->query("INSERT INTO hosts (name, ip) VALUES ('$name', '$ip')");
    } elseif (isset($_POST['delete'])) {
        $id = $conn->real_escape_string($_POST['id']);
        $conn->query("DELETE FROM hosts WHERE id = $id");
    } elseif (isset($_POST['update'])) {
        $down_msg = $conn->real_escape_string($_POST['down_msg']);
        $up_msg = $conn->real_escape_string($_POST['up_msg']);
        $conn->query("UPDATE messages SET down_msg = '$down_msg', up_msg = '$up_msg' WHERE id = 1");
    }
}

$hosts = $conn->query("SELECT * FROM hosts");
$messages = $conn->query("SELECT * FROM messages")->fetch_assoc();

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
        h1 {
            color: #007BFF;
            text-align: center;
        }
        form {
            margin-bottom: 20px;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin: 5px 0 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #007BFF;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: block;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
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
        <h1>Delta TI Network</h1>
        <form method="POST">
            <h2>Adicionar IP</h2>
            <input type="text" name="name" placeholder="Nome do Equipamento" required>
            <input type="text" name="ip" placeholder="IP" required>
            <button type="submit" name="add">Adicionar</button>
        </form>

        <h2>IPs Monitorados</h2>
        <table>
            <tr>
                <th>Nome</th>
                <th>IP</th>
                <th>Ação</th>
            </tr>
            <?php while ($row = $hosts->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['ip'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="delete">Remover</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

        <form method="POST">
            <h2>Mensagens de Notificação</h2>
            <textarea name="down_msg" placeholder="Mensagem quando cai" required><?php echo htmlspecialchars($messages['down_msg'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            <textarea name="up_msg" placeholder="Mensagem quando volta" required><?php echo htmlspecialchars($messages['up_msg'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            <button type="submit" name="update">Atualizar Mensagens</button>
        </form>

        <div class="footer">
            Desenvolvido por <a href="http://www.deltatelecomti.com.br">Delta TI</a> © <?php echo date('Y'); ?>
        </div>
    </div>
</body>
</html>
