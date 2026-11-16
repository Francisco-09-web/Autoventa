<?php
require_once 'funciones.php';
requerir_login();

$st = $pdo->prepare('SELECT nombre, apellido, dni, email, rol, fecha_creacion FROM usuarios WHERE id = :id AND fecha_archivado IS NULL');
$st->execute([':id' => $_SESSION['usuario_id']]);
$u = $st->fetch();
if (!$u) { // cuenta archivada o eliminada mientras tenía sesión
    session_destroy();
    header('Location: login.php?msg=' . urlencode('Tu cuenta ya no está activa.'));
    exit;
}
cabecera('Mi panel');
?>
<div class="tarjeta">
  <h1>Bienvenido/a, <?= e($u['nombre']) ?></h1>
  <table>
    <tr><th>Nombre</th><td><?= e($u['nombre']) ?></td></tr>
    <tr><th>Apellido</th><td><?= e($u['apellido']) ?></td></tr>
    <tr><th>DNI</th><td><?= e($u['dni']) ?></td></tr>
    <tr><th>Correo</th><td><?= e($u['email']) ?></td></tr>
    <tr><th>Rol</th><td><?= e($u['rol']) ?></td></tr>
    <tr><th>Alta</th><td><?= e($u['fecha_creacion']) ?></td></tr>
  </table>
  <?php if ($u['rol'] === 'admin'): ?><p><a class="btn" href="admin_usuarios.php">Ir al panel administrativo</a></p><?php endif; ?>
</div>
<?php pie();
