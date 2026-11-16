<?php
require_once 'funciones.php';
requerir_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: admin_usuarios.php'); exit; }
csrf_validar();

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) { header('Location: admin_usuarios.php?error=' . urlencode('ID inválido.')); exit; }
if ($id === (int)$_SESSION['usuario_id']) {
    header('Location: admin_usuarios.php?error=' . urlencode('No podés archivar tu propia cuenta.')); exit;
}

$st = $pdo->prepare('UPDATE usuarios SET fecha_archivado = NOW() WHERE id = :id AND fecha_archivado IS NULL');
$st->execute([':id' => $id]);
$msg = $st->rowCount() ? 'ok=' . urlencode('Usuario archivado.') : 'error=' . urlencode('No se pudo archivar el usuario.');
header('Location: admin_usuarios.php?' . $msg);
exit;
