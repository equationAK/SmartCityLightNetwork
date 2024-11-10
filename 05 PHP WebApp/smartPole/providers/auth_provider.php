<?php

session_start();

require_once 'db_config.php';


function login($username, $password)
{
    global $conn;

    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($query);

    if ($result->num_rows == 1) {
        // Fetch the user data
        $user_data = $result->fetch_assoc();

        // Store user data in session variables
        $_SESSION['user_id'] = $user_data['user_id'];
        $_SESSION['authenticated'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['role_id'] = $user_data['role_id'];
        return true;
    }
    else {
        return false;
    }
}

function logout()
{
    session_unset();
    session_destroy();
}

function isAuthenticated()
{
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

