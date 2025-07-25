<?php

// Redirect to login if not logged in
if(!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

// Role checking functions
function isAdmin() {
    return ($_SESSION['roleName'] === 'Admin');
}

function isEditor() {
    return ($_SESSION['roleName'] === 'Editor');
}

function isHallOwner() {
    return ($_SESSION['roleName'] === 'Hall Owner');
}

// Redirect unauthorized users
function adminOnly() {
    if(!isAdmin()) {
        header("Location: unauthorized.php");
        exit();
    }
}

function editorOnly() {
    if(!isEditor() && !isAdmin()) {  // Admins can access editor pages
        header("Location: unauthorized.php");
        exit();
    }
}

function hallOwnerOnly() {
    if(!isHallOwner() && !isAdmin()) {  // Admins can access hall owner pages
        header("Location: unauthorized.php");
        exit();
    }
}
?>