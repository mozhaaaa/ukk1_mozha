<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != "admin") {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['simpan'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $level = $_POST['level'];

    mysqli_query(
        $koneksi,
        "INSERT INTO user (username, password, level)
         VALUES ('$username', '$password', '$level')"
    );

    header("Location: user.php");
    exit;
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    mysqli_query($koneksi, "DELETE FROM user WHERE user_id='$id'");

    header("Location: user.php");
    exit;
}

$data = mysqli_query($koneksi, "SELECT * FROM user");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data User</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="wrapper">
    <div class="sidebar">
        <div class="logo">
            <h2>Toko Sembako</h2>
            <p>Admin</p>
        </div>

        <a href="index.php">Dashboard</a>
        <a href="pelanggan.php">Pelanggan</a>
        <a href="produk.php">Produk</a>
        <a href="laporan.php">Laporan</a>
        <a href="user.php">User</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>

    <div class="content">
        <div class="header">
            <h1>Data User</h1>
            <p>Kelola akun admin dan kasir</p>
        </div>

        <div class="box">
            <h2>Tambah User</h2>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label>Level</label>
                        <select name="level" required>
                            <option value="admin">Admin</option>
                            <option value="kasir">Kasir</option>
                        </select>
                    </div>
                </div>

                <button class="btn btn-primary" type="submit" name="simpan">
                    Simpan User
                </button>
            </form>
        </div>

        <div class="box">
            <h2>Daftar User</h2>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Level</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($d = mysqli_fetch_assoc($data)) { ?>
                        <tr>
                            <td><?php echo $d['user_id']; ?></td>
                            <td><?php echo $d['username']; ?></td>
                            <td>
                                <?php if ($d['level'] == "admin") { ?>
                                    <span class="badge badge-admin">
                                        Admin
                                    </span>
                                <?php } else { ?>
                                    <span class="badge badge-kasir">
                                        Kasir
                                    </span>
                                <?php } ?>
                            </td>
                            <td>
                                <a
                                    class="btn btn-hapus"
                                    href="user.php?hapus=<?php echo $d['user_id']; ?>"
                                >
                                    Hapus
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>