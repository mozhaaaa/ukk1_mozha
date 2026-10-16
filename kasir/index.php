
<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != "kasir") {
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";

$jumlah_pelanggan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pelanggan"))['total'];

$jumlah_produk = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM produk"))['total'];

$total_penjualan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COALESCE(SUM(total_harga), 0) AS total FROM penjualan WHERE DATE(tanggal_penjualan) = CURDATE()"))['total'];


/* =====================================================
   SEMBUNYIKAN RIWAYAT TRANSAKSI KASIR
   DATA DATABASE TIDAK DIHAPUS
===================================================== */

if (!isset($_SESSION['transaksi_tersembunyi'])) {
    $_SESSION['transaksi_tersembunyi'] = [];
}

if (isset($_GET['sembunyikan_semua'])) {

    $data_semua = mysqli_query(
        $koneksi,
        "SELECT penjualan_id FROM penjualan"
    );

    while ($p = mysqli_fetch_assoc($data_semua)) {
        if (!in_array($p['penjualan_id'], $_SESSION['transaksi_tersembunyi'])) {
            $_SESSION['transaksi_tersembunyi'][] = $p['penjualan_id'];
        }
    }

    header("Location: index.php");
    exit;
}


$data_pembelian = mysqli_query($koneksi, "
    SELECT penjualan.*, pelanggan.nama_pelanggan
    FROM penjualan
    LEFT JOIN pelanggan
    ON penjualan.pelanggan_id = pelanggan.pelanggan_id
    ORDER BY penjualan.penjualan_id DESC
");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        /* =====================================================
           PERBAIKAN POSISI 3 KOTAK DASHBOARD KASIR
           KODE LAIN TETAP
        ===================================================== */

        .card-container {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 18px !important;
            width: 100% !important;
        }

        .card-container .card {
            width: 100% !important;
            min-width: 0 !important;
            padding: 20px !important;
        }

        .card-container .card h3 {
            white-space: nowrap;
        }

        .card-container .card .angka {
            white-space: nowrap;
        }

        /* =====================================================
           AGAR TABEL TOTAL DAN KOLOM TETAP RAPI
        ===================================================== */

        .box {
            width: 100%;
            background: #ffffff;
            padding: 20px;
            margin-top: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .box h2 {
            margin-bottom: 18px;
            color: #1f2937;
            font-size: 20px;
        }

        .box .table-wrapper {
            width: 100%;
        }

        /* =====================================================
           PENCARIAN DI SEBELAH KIRI
        ===================================================== */

        .aksi-atas {
            width: 100%;
            display: flex;
            justify-content: flex-start;
            margin-bottom: 15px;
        }

        .aksi-atas input {
            width: 280px;
            max-width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            outline: none;
            font-size: 13px;
            box-sizing: border-box;
        }

        .aksi-atas input:focus {
            border-color: #64748b;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 800px) {
            .card-container {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        @media (max-width: 500px) {
            .card-container {
                grid-template-columns: 1fr !important;
            }

            .aksi-atas input {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="wrapper">

        <div class="sidebar">

            <div class="logo">
                <h2>Toko Sembako</h2>
                <p>Kasir</p>
            </div>

            <a href="index.php">Dashboard</a>
            <a href="transaksi.php">Transaksi</a>
            <a href="riwayat.php">Riwayat</a>

            <a href="../logout.php" class="logout">Logout</a>

        </div>

        <div class="content">

            <div class="header">

                <h1>Dashboard Kasir</h1>

                <p>
                    Selamat datang,
                    <?php echo $_SESSION['username']; ?>
                </p>

            </div>

            <div class="card-container">

                <div class="card">
                    <h3>Jumlah Pelanggan</h3>
                    <div class="angka">
                        <?php echo $jumlah_pelanggan; ?>
                    </div>
                </div>

                <div class="card">
                    <h3>Jumlah Produk</h3>
                    <div class="angka">
                        <?php echo $jumlah_produk; ?>
                    </div>
                </div>

                <div class="card">
                    <h3>Total Penjualan Hari Ini</h3>
                    <div class="angka">
                        Rp <?php echo number_format($total_penjualan, 0, ',', '.'); ?>
                    </div>
                </div>

            </div>


            <!-- =========================
                 DATA PEMBELIAN
            ========================= -->

            <div class="box">

                <h2>Data Pembelian</h2>

                <!-- PENCARIAN -->
                <div class="aksi-atas">
                    <input
                        type="text"
                        id="cariPembelian"
                        placeholder="Cari ID atau nama pelanggan..."
                        autocomplete="off"
                    >
                </div>

                <div class="table-wrapper">

                    <table>

                        <tr>
                            <th>ID Penjualan</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>

                        <?php while ($d = mysqli_fetch_assoc($data_pembelian)) { ?>

                            <?php
                            if (in_array($d['penjualan_id'], $_SESSION['transaksi_tersembunyi'])) {
                                continue;
                            }
                            ?>

                            <tr>

                                <td>
                                    #<?php echo $d['penjualan_id']; ?>
                                </td>

                                <td>
                                    <?php echo $d['tanggal_penjualan']; ?>
                                </td>

                                <td>
                                    <?php echo $d['nama_pelanggan']; ?>
                                </td>

                                <td>
                                    Rp <?php echo number_format($d['total_harga'], 0, ',', '.'); ?>
                                </td>

                                <td>
                                    <a
                                        href="invoice.php?id=<?php echo $d['penjualan_id']; ?>"
                                        class="btn btn-invoice"
                                    >
                                        Invoice
                                    </a>
                                </td>

                            </tr>

                        <?php } ?>

                    </table>

                </div>

            </div>

        </div>

    </div>

    <script>
    document.getElementById('cariPembelian').addEventListener('input', function () {
        const kata = this.value.toLowerCase().trim();
        const baris = document.querySelectorAll('.table-wrapper table tr');

        baris.forEach(function (tr, index) {
            if (index === 0) {
                return;
            }

            const id = tr.cells[0].textContent.toLowerCase();
            const pelanggan = tr.cells[2].textContent.toLowerCase();

            tr.style.display =
                id.includes(kata) || pelanggan.includes(kata)
                    ? ''
                    : 'none';
        });
    });
    </script>

</body>
</html>
