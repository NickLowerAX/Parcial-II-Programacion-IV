<?php
// ============================================
// logout.php - Cerrar sesión
// Destruye la sesión activa y redirige al login.
// ============================================

session_start();
session_unset();
session_destroy();
header('Location: index.php');
exit;
