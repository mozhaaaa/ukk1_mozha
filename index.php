<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['level'] == "admin") {
        header("Location: admin/index.php");
    } else if ($_SESSION['level'] == "kasir") {
        header("Location: kasir/index.php");
    }
} else {
    header("Location: login.php");
}
?>