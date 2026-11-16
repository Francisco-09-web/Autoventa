<?php
// Funciones comunes: sesión, escape HTML, CSRF, control de acceso y validación
session_start();
require_once __DIR__ . '/conexion.php';

function e(?string $t): string {
    return htmlspecialchars($t ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_campo(): string {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}
function csrf_validar(): void {
    $t = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(403);
        exit('Solicitud no autorizada (token CSRF inválido).');
    }
}

function requerir_login(): void {
    if (empty($_SESSION['usuario_id'])) {
        header('Location: login.php?msg=' . urlencode('Debés iniciar sesión.'));
        exit;
    }
}
function requerir_admin(): void {
    requerir_login();
    if (($_SESSION['rol'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Acceso denegado: se requiere rol de administrador. <a href="panel.php">Volver</a>');
    }
}

// Valida datos de usuario. Devuelve array de errores.
function validar_usuario(array $d, bool $requiere_pass): array {
    $err = [];
    if ($d['nombre'] === '' || mb_strlen($d['nombre']) > 60)   $err[] = 'El nombre es obligatorio (máx. 60 caracteres).';
    if ($d['apellido'] === '' || mb_strlen($d['apellido']) > 60) $err[] = 'El apellido es obligatorio (máx. 60 caracteres).';
    if (!preg_match('/^\d{7,8}$/', $d['dni']))                 $err[] = 'El DNI debe tener 7 u 8 números.';
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($d['email']) > 120) $err[] = 'El correo electrónico no es válido.';
    if ($requiere_pass || $d['password'] !== '') {
        if (strlen($d['password']) < 8) $err[] = 'La contraseña debe tener al menos 8 caracteres.';
    }
    return $err;
}

// ¿Existe otro usuario con ese DNI o email? ($excluir_id para edición)
function duplicado(PDO $pdo, string $dni, string $email, int $excluir_id = 0): ?string {
    $st = $pdo->prepare('SELECT dni, email FROM usuarios WHERE (dni = :dni OR email = :email) AND id <> :id LIMIT 1');
    $st->execute([':dni' => $dni, ':email' => $email, ':id' => $excluir_id]);
    $f = $st->fetch();
    if (!$f) return null;
    return $f['dni'] === $dni ? 'Ya existe un usuario con ese DNI.' : 'Ya existe un usuario con ese correo electrónico.';
}

function cabecera(string $titulo): void {
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>' . e($titulo) . '</title><link rel="stylesheet" href="css/estilos.css"></head><body>';
    if (!empty($_SESSION['usuario_id'])) {
        echo '<nav><span class="marca"><img src="img/logo.png" alt="AutoVenta" style="height:32px;vertical-align:middle;background:#fff;padding:4px;border-radius:6px"> AutoVenta</span><div>'
           . '<a href="panel.php">Mi panel</a>';
        if ($_SESSION['rol'] === 'admin') {
            echo '<a href="admin_usuarios.php">Usuarios</a><a href="usuarios_archivados.php">Archivados</a>';
        }
        echo '<a href="cerrar_sesion.php">Cerrar sesión (' . e($_SESSION['nombre']) . ')</a></div></nav>';
    }
    echo '<main>';
}
function pie(): void { echo '</main></body></html>'; }
