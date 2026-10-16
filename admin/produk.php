<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != "admin") {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['simpan'])) {
    $nama = $_POST['nama_produk'];
    $jenis = $_POST['jenis_barang'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    $query = mysqli_query(
        $koneksi,
        "INSERT INTO produk (nama_produk, jenis_barang, harga, stok)
         VALUES ('$nama', '$jenis', '$harga', '$stok')"
    );

    if (!$query) {
        die("Produk gagal disimpan: " . mysqli_error($koneksi));
    }

    header("Location: produk.php");
    exit;
}

/* =====================================================
   EDIT PRODUK
===================================================== */

if (isset($_POST['update'])) {
    $id = $_POST['produk_id'];
    $nama = $_POST['nama_produk'];
    $jenis = $_POST['jenis_barang'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    $query = mysqli_query(
        $koneksi,
        "UPDATE produk SET
            nama_produk='$nama',
            jenis_barang='$jenis',
            harga='$harga',
            stok='$stok'
            WHERE produk_id='$id'"
    );

    if (!$query) {
        die("Produk gagal diubah: " . mysqli_error($koneksi));
    }

    header("Location: produk.php");
    exit;
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    mysqli_query($koneksi, "DELETE FROM produk WHERE produk_id='$id'");

    header("Location: produk.php");
    exit;
}

$data = mysqli_query($koneksi, "SELECT * FROM produk");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Produk</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .btn-edit {
            background: #ffc107;
            color: #212529;
        }

        .btn-edit:hover {
            background: #e0a800;
        }
    </style>
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
            <h1>Data Produk</h1>
            <p>Kelola produk dan stok barang</p>
        </div>

        <div class="box">
            <h2>Tambah Produk</h2>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Produk</label>
                        <input type="text" name="nama_produk" required>
                    </div>

                    <div class="form-group">
                        <label>Jenis Barang</label>
                        <input type="text" name="jenis_barang" required>
                    </div>

                    <div class="form-group">
                        <label>Harga</label>
                        <input type="number" name="harga" required>
                    </div>

                    <div class="form-group">
                        <label>Stok</label>
                        <input type="number" name="stok" required>
                    </div>
                </div>

                <button class="btn btn-primary" type="submit" name="simpan">
                    Simpan Produk
                </button>
            </form>
        </div>

        <?php
        if (isset($_GET['edit'])) {
            $id_edit = $_GET['edit'];

            $edit = mysqli_query(
                $koneksi,
                "SELECT * FROM produk WHERE produk_id='$id_edit'"
            );

            $produk_edit = mysqli_fetch_assoc($edit);

            if ($produk_edit) {
        ?>

        <div class="box">
            <h2>Edit Produk</h2>

            <form method="POST">
                <input
                    type="hidden"
                    name="produk_id"
                    value="<?php echo $produk_edit['produk_id']; ?>"
                >

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Produk</label>
                        <input
                            type="text"
                            name="nama_produk"
                            value="<?php echo htmlspecialchars($produk_edit['nama_produk']); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Jenis Barang</label>
                        <input
                            type="text"
                            name="jenis_barang"
                            value="<?php echo htmlspecialchars($produk_edit['jenis_barang']); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Harga</label>
                        <input
                            type="number"
                            name="harga"
                            value="<?php echo $produk_edit['harga']; ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Stok</label>
                        <input
                            type="number"
                            name="stok"
                            value="<?php echo $produk_edit['stok']; ?>"
                            required
                        >
                    </div>
                </div>

                <button class="btn btn-primary" type="submit" name="update">
                    Update Produk
                </button>

                <a
                    class="btn"
                    href="produk.php"
                    style="text-decoration:none;"
                >
                    Batal
                </a>
            </form>
        </div>

        <?php
            }
        }
        ?>

        <div class="box">
            <h2>Daftar Produk</h2>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Produk</th>
                            <th>Jenis</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($d = mysqli_fetch_assoc($data)) { ?>
                        <tr>
                            <td><?php echo $d['produk_id']; ?></td>
                            <td><?php echo $d['nama_produk']; ?></td>
                            <td><?php echo $d['jenis_barang']; ?></td>
                            <td>
                                Rp <?php echo number_format($d['harga'], 0, ',', '.'); ?>
                            </td>
                            <td><?php echo $d['stok']; ?></td>
                            <td>
                                <a
                                    class="btn btn-edit"
                                    href="produk.php?edit=<?php echo $d['produk_id']; ?>"
                                >
                                    Edit
                                </a>

                                <a
                                    class="btn btn-hapus"
                                    href="produk.php?hapus=<?php echo $d['produk_id']; ?>"
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