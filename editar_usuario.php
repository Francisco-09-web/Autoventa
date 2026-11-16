<?php
require_once 'funciones.php';
requerir_admin();

$id = filter_var($_GET['id'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) { header('Location: admin_usuarios.php?error=' . urlencode('ID inválido.')); exit; }

$st = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
$st->execute([':id' => $id]);
$u = $st->fetch();
if (!$u) { header('Location: admin_usuarios.php?error=' . urlencode('El usuario no existe.')); exit; }

$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $d = [
        'nombre'   => trim($_POST['nombre'] ?? ''),
        'apellido' => trim($_POST['apellido'] ?? ''),
        'dni'      => trim($_POST['dni'] ?? ''),
        'email'    => mb_strtolower(trim($_POST['email'] ?? '')),
        'password' => $_POST['password'] ?? '', // vacía = no cambiar
        'rol'      => $_POST['rol'] ?? 'usuario',
    ];
    if (!in_array($d['rol'], ['usuario','admin'], true)) $d['rol'] = 'usuario';
    // un admin no puede quitarse a sí mismo el rol (evita quedarse sin administradores)
    if ($id === (int)$_SESSION['usuario_id']) $d['rol'] = 'admin';

    $errores = validar_usuario($d, false);
    if (!$errores && ($dup = duplicado($pdo, $d['dni'], $d['email'], $id))) $errores[] = $dup;

    if (!$errores) {
        $sql = 'UPDATE usuarios SET nombre=:n, apellido=:a, dni=:d, email=:e, rol=:r';
        $p = [':n'=>$d['nombre'], ':a'=>$d['apellido'], ':d'=>$d['dni'], ':e'=>$d['email'], ':r'=>$d['rol'], ':id'=>$id];
        if ($d['password'] !== '') {
            $sql .= ', password_hash=:p';
            $p[':p'] = password_hash($d['password'], PASSWORD_DEFAULT);
        }
        $pdo->prepare($sql . ' WHERE id=:id')->execute($p);
        if ($id === (int)$_SESSION['usuario_id']) $_SESSION['nombre'] = $d['nombre'];
        header('Location: admin_usuarios.php?ok=' . urlencode('Usuario actualizado correctamente.'));
        exit;
    }
    $u = array_merge($u, $d); // mantener lo que escribió
}
cabecera('Editar usuario');
?>
<div class="tarjeta">
  <h1>Editar usuario #<?= (int)$u['id'] ?></h1>
  <?php foreach ($errores as $m): ?><p class="error"><?= e($m) ?></p><?php endforeach; ?>
  <form method="post" novalidate>
    <?= csrf_campo() ?>
    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
    <label>Nombre <input type="text" name="nombre" value="<?= e($u['nombre']) ?>" required></label>
    <label>Apellido <input type="text" name="apellido" value="<?= e($u['apellido']) ?>" required></label>
    <label>DNI <input type="text" name="dni" value="<?= e($u['dni']) ?>" required></label>
    <label>Correo electrónico <input type="email" name="email" value="<?= e($u['email']) ?>" required></label>
    <label>Nueva contraseña (dejar vacío para no cambiarla) <input type="password" name="password"></label>
    <label>Rol
      <select name="rol">
        <option value="usuario" <?= $u['rol']==='usuario'?'selected':'' ?>>Usuario</option>
        <option value="admin" <?= $u['rol']==='admin'?'selected':'' ?>>Administrador</option>
      </select>
    </label>
    <button type="submit">Guardar cambios</button>
    <a href="admin_usuarios.php">Cancelar</a>
  </form>
</div>
<?php pie();
