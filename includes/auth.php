<?php
session_start();
require_once 'db.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function login($username, $password) {
    $username = escape_string($username);
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = query($sql);
    
    if (num_rows($result) == 1) {
        $user = fetch_assoc($result);
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: ../admin/login.php');
    exit;
}
?>