<?php
session_start();
header('Location: ' . (empty($_SESSION['usuario_id']) ? 'login.php' : 'panel.php'));
