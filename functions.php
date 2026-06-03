<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function showError($field, $errors) {
    if (isset($errors[$field])) {
        echo '<span class="error-msg">' . $errors[$field] . '</span>';
    }
}
?>