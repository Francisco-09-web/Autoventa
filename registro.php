<?php
require_once 'funciones.php';
$errores = [];
$d = ['nombre'=>'','apellido'=>'','dni'=>'','email'=>'','password'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $d = [
        'nombre'   => trim($_POST['nombre'] ?? ''),
        'apellido' => trim($_POST['apellido'] ?? ''),
        'dni'      => trim($_POST['dni'] ?? ''),
        'email'    => mb_strtolower(trim($_POST['email'] ?? '')),
        'password' => $_POST['password'] ?? '',
    ];
    $errores = validar_usuario($d, true);
    if (!$errores && ($dup = duplicado($pdo, $d['dni'], $d['email']))) $errores[] = $dup;

    if (!$errores) {
        // El registro público SIEMPRE crea usuarios con rol 'usuario'
        $st = $pdo->prepare('INSERT INTO usuarios (nombre, apellido, dni, email, password_hash, rol)
                             VALUES (:n, :a, :d, :e, :p, "usuario")');
        $st->execute([':n'=>$d['nombre'], ':a'=>$d['apellido'], ':d'=>$d['dni'], ':e'=>$d['email'],
                      ':p'=>password_hash($d['password'], PASSWORD_DEFAULT)]);
        header('Location: login.php?ok=' . urlencode('Cuenta creada. Ya podés iniciar sesión.'));
        exit;
    }
}
cabecera('Registro');
?>
<div class="tarjeta">
  <img src="img/logo.png" alt="AutoVenta" style="max-width:150px"><h2>Crear cuenta</h2>
  <?php foreach ($errores as $m): ?><p class="error"><?= e($m) ?></p><?php endforeach; ?>
  <form method="post" novalidate>
    <?= csrf_campo() ?>
    <label>Nombre <input type="text" name="nombre" value="<?= e($d['nombre']) ?>" required></label>
    <label>Apellido <input type="text" name="apellido" value="<?= e($d['apellido']) ?>" required></label>
    <label>DNI <input type="text" name="dni" value="<?= e($d['dni']) ?>" inputmode="numeric" required></label>
    <label>Correo electrónico <input type="email" name="email" value="<?= e($d['email']) ?>" required></label>
    <label>Contraseña (mín. 8) <input type="password" name="password" required></label>
    <button type="submit">Registrarme</button>
  </form>
  <p>¿Ya tenés cuenta? <a href="login.php">Iniciar sesión</a></p>
</div>
<?php pie();
