<?php

session_start();

include "koneksi.php";

$username = $_POST['username'];
$password = md5($_POST['password']);
$level    = $_POST['level'];

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM user
     WHERE username='$username'
     AND password='$password'
     AND level='$level'"
);

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}

$data = mysqli_fetch_assoc($query);

if ($data) {

    $_SESSION['user_id']  = $data['user_id'];
    $_SESSION['username'] = $data['username'];
    $_SESSION['level']    = $data['level'];

    if ($data['level'] == "admin") {

        header("Location: admin/index.php");
        exit();

    } else if ($data['level'] == "kasir") {

        header("Location: kasir/index.php");
        exit();

    }

} else {

    header("Location: login.php?pesan=gagal");
    exit();

}

?>