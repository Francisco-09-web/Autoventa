<?php
require_once 'funciones.php';
if (!empty($_SESSION['usuario_id'])) { header('Location: panel.php'); exit; }
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $email = mb_strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';

    if ($email === '' || $pass === '') {
        $error = 'Completá el correo y la contraseña.';
    } else {
        $st = $pdo->prepare('SELECT * FROM usuarios WHERE email = :e LIMIT 1');
        $st->execute([':e' => $email]);
        $u = $st->fetch();

        if (!$u || !password_verify($pass, $u['password_hash'])) {
            $error = 'Correo o contraseña incorrectos.';
        } elseif ($u['fecha_archivado'] !== null) {
            $error = 'Tu cuenta está archivada. Contactá a un administrador.';
        } else {
            session_regenerate_id(true); // evita fijación de sesión
            $_SESSION['usuario_id'] = (int)$u['id'];
            $_SESSION['nombre']     = $u['nombre'];
            $_SESSION['rol']        = $u['rol'];
            header('Location: panel.php');
            exit;
        }
    }
}
cabecera('Iniciar sesión');
?>
<div class="tarjeta">
  <img src="img/logo.png" alt="AutoVenta" style="max-width:150px"><h2>Iniciar sesión</h2>
  <?php if (!empty($_GET['ok'])): ?><p class="ok"><?= e($_GET['ok']) ?></p><?php endif; ?>
  <?php if (!empty($_GET['msg'])): ?><p class="error"><?= e($_GET['msg']) ?></p><?php endif; ?>
  <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
  <form method="post">
    <?= csrf_campo() ?>
    <label>Correo electrónico <input type="email" name="email" value="<?= e($email) ?>" required></label>
    <label>Contraseña <input type="password" name="password" required></label>
    <button type="submit">Entrar</button>
  </form>
  <p>¿No tenés cuenta? <a href="registro.php">Registrate</a></p>
</div>
<?php pie();
