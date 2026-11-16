<?php
require_once 'funciones.php';
requerir_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: usuarios_archivados.php'); exit; }
csrf_validar();

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) { header('Location: usuarios_archivados.php?error=' . urlencode('ID inválido.')); exit; }

$st = $pdo->prepare('UPDATE usuarios SET fecha_archivado = NULL WHERE id = :id AND fecha_archivado IS NOT NULL');
$st->execute([':id' => $id]);
$msg = $st->rowCount() ? 'ok=' . urlencode('Usuario reactivado.') : 'error=' . urlencode('No se pudo reactivar el usuario.');
header('Location: usuarios_archivados.php?' . $msg);
exit;
