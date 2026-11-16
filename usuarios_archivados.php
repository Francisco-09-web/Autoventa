<?php
require_once 'funciones.php';
requerir_admin();

$usuarios = $pdo->query('SELECT id, nombre, apellido, dni, email, rol, fecha_archivado
                         FROM usuarios WHERE fecha_archivado IS NOT NULL
                         ORDER BY fecha_archivado DESC')->fetchAll();
cabecera('Usuarios archivados');
?>
<h1>Usuarios archivados</h1>
<?php if (!empty($_GET['ok'])): ?><p class="ok"><?= e($_GET['ok']) ?></p><?php endif; ?>
<?php if (!empty($_GET['error'])): ?><p class="error"><?= e($_GET['error']) ?></p><?php endif; ?>
<div class="scroll">
<table>
  <thead><tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>DNI</th><th>Correo</th><th>Rol</th><th>Archivado</th><th>Acción</th></tr></thead>
  <tbody>
  <?php if (!$usuarios): ?><tr><td colspan="8">No hay usuarios archivados.</td></tr><?php endif; ?>
  <?php foreach ($usuarios as $u): ?>
    <tr>
      <td><?= (int)$u['id'] ?></td>
      <td><?= e($u['nombre']) ?></td>
      <td><?= e($u['apellido']) ?></td>
      <td><?= e($u['dni']) ?></td>
      <td><?= e($u['email']) ?></td>
      <td><?= e($u['rol']) ?></td>
      <td><?= e($u['fecha_archivado']) ?></td>
      <td>
        <form method="post" action="reactivar_usuario.php">
          <?= csrf_campo() ?>
          <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
          <button type="submit" class="chico">Reactivar</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<p><a href="admin_usuarios.php">← Volver a usuarios activos</a></p>
<?php pie();
