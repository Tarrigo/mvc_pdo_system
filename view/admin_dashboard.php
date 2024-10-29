<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
  header('Location: index.php?action=login');
  exit();
}

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "login_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin UNSO</title>
  <link rel="stylesheet" href="dashboard_styles.css">
  <style>
    .search-icon, .unlock-icon, .delete-icon {
      display: block;
      margin: 0 auto;
    }
    .search-form {
      text-align: center;
    }
    .unlock-button, .delete-button {
      display: inline-block;
      margin: 0 5px;
    }
    .no-logs {
      color: red;
    }
    .logs-title {
      font-size: 24px;
      font-weight: bold;
      color: #00698f;
    }
  </style>
</head>
<body class="admin-dashboard">
<header>
  <div class="content">
    <div class="menu container">
      <a href="#" class="logo" >
        <img src="assets/logo_unso.png" alt="Logo UNSO">
      </a>
      <input type=checkbox id="menu">
      <label for="menu">
        <img src="assets/menu.png" class="menu-icon" alt="">
      </label>
      <nav class= navbar>
        <ul>
          <li><a href="admin_dashboard.php">Logs</a></li>
          <li><a href="logout.php">Cerrar sesión</a></li>
        </ul>
      </div>
    </div>
  </header>

  <!-- Form de búsqueda -->
  <form action="admin_dashboard.php" method="get" class="search-form">
    <input type="email" id="email" name="email" placeholder="Ingresar email">
    <button type="submit">
      <img src="assets/buscar.png" class="search-icon">
    </button>
  </form>

  <?php

  if (isset($_GET['email'])) {
    $email = $_GET['email'];


    $sql = "SELECT al.id, al.access_time, u.email, al.user_id 
      FROM access_logs al 
      INNER JOIN users u ON al.user_id = u.id 
      WHERE u.email = '$email'";
    $result = $conn->query($sql);


    if ($result->num_rows > 0) {
      echo "<h2 class='logs-title'>Logs del usuario $email</h2>";
      echo "<table border='1'>";
      echo "<tr><th>ID</th><th>Email</th><th>User ID</th><th>Fecha de acceso</th><th>Eliminar/Desbloquear</th></tr>";

      while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["id"]. "</td><td>" . $row["email"]. "</td><td>" . $row["user_id"]. "</td><td>" . $row["access_time"]. "</td>";
        echo "<td>
          <form action='admin_dashboard.php' method='post'>
            <input type='hidden' name='unlock_user_id' value='" . $row["id"] . "'>
            <button type='submit' class='unlock-button'>
              <img src='assets/desbloquear.png' class='unlock-icon'>
            </button>
          </form>
          <form action='admin_dashboard.php' method='post'>
            <input type='hidden' name='delete_user_id' value='" . $row["id"] . "'>
            <button type='submit' class='delete-button'>
              <img src='assets/eliminar.png' class='delete-icon'>
            </button>
          </form>
        </td>";
        echo "</tr>";
      }

      echo "</table>";
    } else {
      echo "<p class='no-logs'>No se encontraron logs para el usuario $email</p>";
    }
  } else {

  $sql = "SELECT * FROM users";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>DNI</th><th>Nombre</th><th>Apellido</th><th>Fecha de nacimiento</th><th>Email</th><th>Rol</th><th>Bloqueado</th><th>Intentos fallidos</th><th>Eliminar/Desbloquear</th></tr>";

    while($row = $result->fetch_assoc()) {
      echo "<tr><td>" . $row["id"]. "</td><td>" . $row["dni"]. "</td><td>" . $row["nombre"]. "</td><td>" . $row["apellido"]. "</td><td>" . $row["fecha_nacimiento"]. "</td><td>" . $row["email"]. "</td><td>" . $row["role"]. "</td><td>" . $row["locked"]. "</td><td>" . $row["intentosfallidos"]. "</td>";
      echo "<td>
        <form action='admin_dashboard.php' method='post'>
          <input type='hidden' name='unlock_user_id' value='" . $row["id"] . "'>
          <button type='submit' class='unlock-button'>
            <img src='assets/desbloquear.png' class='unlock-icon'>
          </button>
        </form>
        <form action='admin_dashboard.php' method='post'>
          <input type='hidden' name='delete_user_id' value='" . $row["id"] . "'>
          <button type='submit' class='delete-button'>
            <img src='assets/eliminar.png' class='delete-icon'>
          </button>
        </form>
      </td>";
      echo "</tr>";
    }

    echo "</table>";
  } else {
    echo "<p class='no-logs'>No se encontraron usuarios</p>";
  }
}

if (isset($_POST['delete_user'])) {
  $user_id = $_POST['delete_user_id'];
  $sql = "DELETE FROM users WHERE id = '$user_id'";
  if ($conn->query($sql) === TRUE) {
    echo "Usuario eliminado correctamente";
  } else {
    echo "Error al eliminar el usuario: " . $conn->error;
  }
}

if (isset($_POST['unlock_user'])) {
  $user_id = $_POST['unlock_user_id'];
  $sql = "UPDATE users SET locked = 0 WHERE id = '$user_id'";
  if ($conn->query($sql) === TRUE) {
    echo "Usuario desbloqueado correctamente";
  } else {
    echo "Error al desbloquear el usuario: " . $conn->error;
  }
}
?>

</body>
</html>

<?php
$conn->close();
?>