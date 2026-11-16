<?php
require_once 'funciones.php';
requerir_admin();

$errores = [];
$d = ['nombre'=>'','apellido'=>'','dni'=>'','email'=>'','password'=>'','rol'=>'usuario'];

// Alta de usuario desde el panel administrativo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $d = [
        'nombre'   => trim($_POST['nombre'] ?? ''),
        'apellido' => trim($_POST['apellido'] ?? ''),
        'dni'      => trim($_POST['dni'] ?? ''),
        'email'    => mb_strtolower(trim($_POST['email'] ?? '')),
        'password' => $_POST['password'] ?? '',
        'rol'      => $_POST['rol'] ?? 'usuario',
    ];
    if (!in_array($d['rol'], ['usuario','admin'], true)) $d['rol'] = 'usuario';
    $errores = validar_usuario($d, true);
    if (!$errores && ($dup = duplicado($pdo, $d['dni'], $d['email']))) $errores[] = $dup;
    if (!$errores) {
        $st = $pdo->prepare('INSERT INTO usuarios (nombre, apellido, dni, email, password_hash, rol)
                             VALUES (:n, :a, :d, :e, :p, :r)');
        $st->execute([':n'=>$d['nombre'], ':a'=>$d['apellido'], ':d'=>$d['dni'], ':e'=>$d['email'],
                      ':p'=>password_hash($d['password'], PASSWORD_DEFAULT), ':r'=>$d['rol']]);
        header('Location: admin_usuarios.php?ok=' . urlencode('Usuario registrado correctamente.'));
        exit;
    }
}

// Búsqueda (solo usuarios activos)
$q = trim($_GET['q'] ?? '');
$sql = 'SELECT id, nombre, apellido, dni, email, rol, fecha_creacion FROM usuarios WHERE fecha_archivado IS NULL';
$params = [];
if ($q !== '') {
    $sql .= ' AND (nombre LIKE :q1 OR apellido LIKE :q2 OR dni LIKE :q3 OR email LIKE :q4)';
    $like = '%' . $q . '%';
    $params = [':q1'=>$like, ':q2'=>$like, ':q3'=>$like, ':q4'=>$like];
}
$sql .= ' ORDER BY apellido, nombre';
$st = $pdo->prepare($sql);
$st->execute($params);
$usuarios = $st->fetchAll();

cabecera('Administrar usuarios');
?>
<h1>Usuarios activos</h1>
<?php if (!empty($_GET['ok'])): ?><p class="ok"><?= e($_GET['ok']) ?></p><?php endif; ?>
<?php if (!empty($_GET['error'])): ?><p class="error"><?= e($_GET['error']) ?></p><?php endif; ?>

<form method="get" class="buscador">
  <input type="text" name="q" value="<?= e($q) ?>" placeholder="Buscar por nombre, apellido, DNI o correo">
  <button type="submit">Buscar</button>
  <?php if ($q !== ''): ?><a href="admin_usuarios.php">Limpiar</a><?php endif; ?>
</form>

<div class="scroll">
<table>
  <thead><tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>DNI</th><th>Correo</th><th>Rol</th><th>Alta</th><th>Acciones</th></tr></thead>
  <tbody>
  <?php if (!$usuarios): ?><tr><td colspan="8">No se encontraron usuarios.</td></tr><?php endif; ?>
  <?php foreach ($usuarios as $u): ?>
    <tr>
      <td><?= (int)$u['id'] ?></td>
      <td><?= e($u['nombre']) ?></td>
      <td><?= e($u['apellido']) ?></td>
      <td><?= e($u['dni']) ?></td>
      <td><?= e($u['email']) ?></td>
      <td><?= e($u['rol']) ?></td>
      <td><?= e($u['fecha_creacion']) ?></td>
      <td class="acciones">
        <a class="btn chico" href="editar_usuario.php?id=<?= (int)$u['id'] ?>">Editar</a>
        <form method="post" action="archivar_usuario.php" onsubmit="return confirm('¿Archivar a este usuario?');">
          <?= csrf_campo() ?>
          <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
          <button type="submit" class="peligro chico">Archivar</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<div class="tarjeta">
  <h2>Registrar nuevo usuario</h2>
  <?php foreach ($errores as $m): ?><p class="error"><?= e($m) ?></p><?php endforeach; ?>
  <form method="post" novalidate>
    <?= csrf_campo() ?>
    <label>Nombre <input type="text" name="nombre" value="<?= e($d['nombre']) ?>" required></label>
    <label>Apellido <input type="text" name="apellido" value="<?= e($d['apellido']) ?>" required></label>
    <label>DNI <input type="text" name="dni" value="<?= e($d['dni']) ?>" required></label>
    <label>Correo electrónico <input type="email" name="email" value="<?= e($d['email']) ?>" required></label>
    <label>Contraseña (mín. 8) <input type="password" name="password" required></label>
    <label>Rol
      <select name="rol">
        <option value="usuario" <?= $d['rol']==='usuario'?'selected':'' ?>>Usuario</option>
        <option value="admin" <?= $d['rol']==='admin'?'selected':'' ?>>Administrador</option>
      </select>
    </label>
    <button type="submit">Registrar usuario</button>
  </form>
</div>
<?php pie();
