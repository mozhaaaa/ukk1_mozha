<?php       

session_start();       
include "../koneksi.php";       

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != "admin") {       
    header("Location: ../login.php");       
    exit;       
}       

$tanggal = "";       

if (isset($_GET['tanggal'])) {       
    $tanggal = $_GET['tanggal'];       
}       

$data_laporan = null;       

if ($tanggal != "") {       

    $data_laporan = mysqli_query($koneksi, "       
        SELECT       
            penjualan.penjualan_id,       
            penjualan.tanggal_penjualan,       
            penjualan.total_harga,       
            pelanggan.nama_pelanggan,       
            transaksi.metode_pembayaran,       
            transaksi.status_transaksi       
        FROM penjualan       
        LEFT JOIN pelanggan       
            ON penjualan.pelanggan_id = pelanggan.pelanggan_id       
        LEFT JOIN transaksi       
            ON penjualan.penjualan_id = transaksi.penjualan_id       
        WHERE DATE(penjualan.tanggal_penjualan) = '$tanggal'       
        ORDER BY penjualan.penjualan_id DESC       
    ");       
}       

?>       

<!DOCTYPE html>       
<html>       

<head>       

    <title>Laporan Penjualan</title>       

    <link rel="stylesheet" href="../assets/css/style.css">       

    <style>       

        .laporan-box {       
            background: white;       
            padding: 20px;       
            margin-top: 25px;       
            border-radius: 10px;       
        }       

        .laporan-box h2 {       
            margin-bottom: 5px;       
        }       

        .laporan-box p {       
            color: #777;       
            margin-bottom: 20px;       
        }       

        .laporan-form {       
            display: flex;       
            align-items: end;       
            gap: 10px;       
            flex-wrap: wrap;       
        }       

        .laporan-form label {       
            display: block;       
            margin-bottom: 6px;       
            font-weight: bold;       
        }       

        .laporan-form input {       
            padding: 9px;       
            border: 1px solid #ddd;       
            border-radius: 6px;       
        }       

        .btn-laporan,       
        .btn-hapus {       
            padding: 9px 16px;       
            min-width: 150px;       
            height: 38px;       
            border-radius: 6px;       
            box-sizing: border-box;       
            text-align: center;       
            display: inline-flex;       
            align-items: center;       
            justify-content: center;       
        }       

        .btn-laporan {       
            border: none;       
            background: #007bff;       
            color: white;       
            cursor: pointer;       
        }       

        .btn-laporan:hover {       
            background: #0056b3;       
        }       

        .btn-hapus {       
            background: #dc3545;       
            color: white;       
            text-decoration: none;       
        }       

        .btn-hapus:hover {       
            background: #b02a37;       
        }   

        .btn-cetak {   
            padding: 9px 16px;   
            min-width: 150px;   
            height: 38px;   
            border-radius: 6px;   
            box-sizing: border-box;   
            text-align: center;   
            display: inline-flex;   
            align-items: center;   
            justify-content: center;   
            background: #28a745;   
            color: white;   
            border: none;   
            cursor: pointer;   
            text-decoration: none;   
        }   

        .btn-cetak:hover {   
            background: #218838;   
        }   

        .hasil-laporan {       
            margin-top: 25px;       
        }       

        .total-laporan {       
            margin-top: 15px;       
            font-size: 18px;       
            font-weight: bold;       
        }   

        .detail-produk {   
            line-height: 1.7;   
        }   

        .detail-produk div {   
            margin-bottom: 4px;   
        }   

        .detail-produk div:last-child {   
            margin-bottom: 0;   
        }   

        .status-laporan {   
            display: inline-block;   
            padding: 5px 9px;   
            border-radius: 5px;   
            font-size: 12px;   
            font-weight: 600;   
            background: #e5e7eb;   
            color: #374151;   
        }   

        .area-cetak {   
            margin-top: 15px;   
        }   

        /* TAMBAHAN UNTUK MENYAMAKAN POSISI KEDUA TOMBOL */    
        .laporan-form > div {    
            display: flex !important;    
            flex-direction: column !important;    
        }    

        .laporan-form > div input[type="date"] {    
            width: auto !important;    
            height: 38px !important;    
            margin: 0 !important;    
            box-sizing: border-box !important;    
        }    

        .laporan-form > button.btn-laporan,    
        .laporan-form > a.btn-hapus {    
            width: 150px !important;    
            min-width: 150px !important;    
            height: 38px !important;    
            min-height: 38px !important;    
            margin: 0 !important;    
            padding: 0 16px !important;    
            box-sizing: border-box !important;    
            display: inline-flex !important;    
            align-items: center !important;    
            justify-content: center !important;    
            align-self: flex-end !important;    
            position: static !important;    
            top: auto !important;    
            transform: none !important;    
        }    

        .laporan-form > button.btn-laporan {    
            background: #007bff !important;    
            color: white !important;    
            border: none !important;    
            cursor: pointer !important;    
        }    

        .laporan-form > a.btn-hapus {    
            background: #dc3545 !important;    
            color: white !important;    
            text-decoration: none !important;    
        }   

        /* =====================================================   
           CETAK LAPORAN   
        ===================================================== */   

        @media print {   

            @page {   
                size: A4 landscape;   
                margin: 12mm;   
            }   

            * {   
                box-sizing: border-box;   
            }   

            body {   
                background: white !important;   
                color: #000 !important;   
                font-family: Arial, sans-serif !important;   
                font-size: 13px !important;   
            }   

            .sidebar,   
            .header,   
            .laporan-form,   
            .laporan-box > h2,   
            .laporan-box > p,   
            .area-cetak {   
                display: none !important;   
            }   

            .wrapper {   
                display: block !important;   
                width: 100% !important;   
            }   

            .content {   
                width: 100% !important;   
                margin: 0 !important;   
                padding: 0 !important;   
            }   

            .laporan-box {   
                width: 100% !important;   
                margin: 0 !important;   
                padding: 0 !important;   
                background: white !important;   
                box-shadow: none !important;   
                border-radius: 0 !important;   
            }   

            .hasil-laporan {   
                width: 100% !important;   
                margin: 0 !important;   
                padding: 0 !important;   
            }   

            .hasil-laporan h2 {   
                display: block !important;   
                text-align: center !important;   
                color: #000 !important;   
                font-size: 20px !important;   
                margin: 0 0 18px 0 !important;   
                font-weight: bold !important;   
            }   

            .table-wrapper {   
                width: 100% !important;   
                overflow: visible !important;   
                box-shadow: none !important;   
            }   

            table {   
                width: 100% !important;   
                border-collapse: collapse !important;   
                table-layout: fixed !important;   
            }   

            table th,   
            table td {   
                border: 1px solid #000 !important;   
                color: #000 !important;   
                background: white !important;   
                font-size: 12px !important;   
                line-height: 1.5 !important;   
                padding: 8px 7px !important;   
                vertical-align: top !important;   
                word-wrap: break-word !important;   
                overflow-wrap: break-word !important;   
            }   

            table th {   
                text-align: center !important;   
                font-size: 12px !important;   
                font-weight: bold !important;   
                vertical-align: middle !important;   
            }   

            /* Lebar kolom tabel */   

            table th:nth-child(1),   
            table td:nth-child(1) {   
                width: 9% !important;   
                text-align: center !important;   
            }   

            table th:nth-child(2),   
            table td:nth-child(2) {   
                width: 14% !important;   
                text-align: center !important;   
            }   

            table th:nth-child(3),   
            table td:nth-child(3) {   
                width: 13% !important;   
            }   

            table th:nth-child(4),   
            table td:nth-child(4) {   
                width: 30% !important;   
            }   

            table th:nth-child(5),   
            table td:nth-child(5) {   
                width: 14% !important;   
            }   

            table th:nth-child(6),   
            table td:nth-child(6) {   
                width: 11% !important;   
                text-align: center !important;   
            }   

            table th:nth-child(7),   
            table td:nth-child(7) {   
                width: 9% !important;   
                text-align: center !important;   
            }   

            .detail-produk {   
                line-height: 1.6 !important;   
                font-size: 12px !important;   
            }   

            .detail-produk div {   
                margin-bottom: 5px !important;   
            }   

            .detail-produk strong {   
                font-size: 12px !important;   
            }   

            .status-laporan {   
                display: inline-block !important;   
                background: white !important;   
                color: #000 !important;   
                border: none !important;   
                padding: 0 !important;   
                font-size: 12px !important;   
                font-weight: bold !important;   
            }   

            .total-laporan {   
                margin-top: 15px !important;   
                color: #000 !important;   
                font-size: 15px !important;   
                font-weight: bold !important;   
                text-align: right !important;   
            }   

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

            <h1>Laporan Penjualan</h1>       

            <p>       
                Lihat laporan penjualan berdasarkan tanggal       
            </p>       

        </div>       


        <div class="laporan-box">       

            <h2>Pencarian Laporan</h2>       

            <p>       
                Pilih tanggal untuk melihat data penjualan.       
            </p>       


            <form method="GET" action="laporan.php" class="laporan-form">       

                <div>       

                    <label>Tanggal</label>       

                    <input       
                        type="date"       
                        name="tanggal"       
                        value="<?php echo htmlspecialchars($tanggal); ?>"       
                        required       
                    >       

                </div>       


                <button       
                    type="submit"       
                    class="btn-laporan"       
                >       
                    Tampilkan Laporan       
                </button>       


                <a       
                    href="laporan.php"   
                    class="btn-hapus"       
                >       
                    Hapus Pencarian       
                </a>       

            </form>       


            <?php if ($tanggal != "") { ?>       

            <div class="hasil-laporan">       

                <h2>       
                    Laporan Tanggal       
                    <?php echo date("d-m-Y", strtotime($tanggal)); ?>       
                </h2>       


                <div class="table-wrapper">       

                    <table>       

                        <tr>       

                            <th>ID Penjualan</th>       
                            <th>Tanggal</th>       
                            <th>Pelanggan</th>       
                            <th>Produk yang Dibeli</th>       
                            <th>Total Harga</th>       
                            <th>Metode Pembayaran</th>       
                            <th>Status</th>       

                        </tr>       


                        <?php       

                        $total_laporan = 0;       

                        if (       
                            $data_laporan &&       
                            mysqli_num_rows($data_laporan) > 0       
                        ) {       

                            while ($d = mysqli_fetch_assoc($data_laporan)) {       

                                $total_laporan += $d['total_harga'];   

                                $id_penjualan = $d['penjualan_id'];   

                                $detail_produk = mysqli_query($koneksi, "   
                                    SELECT   
                                        produk.nama_produk,   
                                        detail_penjualan.jumlah_produk,   
                                        detail_penjualan.harga,   
                                        detail_penjualan.subtotal   
                                    FROM detail_penjualan   
                                    LEFT JOIN produk   
                                        ON detail_penjualan.produk_id = produk.produk_id   
                                    WHERE detail_penjualan.penjualan_id = '$id_penjualan'   
                                    ORDER BY produk.nama_produk ASC   
                                ");   

                        ?>       

                        <tr>       

                            <td>       
                                #<?php echo $d['penjualan_id']; ?>       
                            </td>       

                            <td>       
                                <?php echo $d['tanggal_penjualan']; ?>       
                            </td>       

                            <td>       

                                <?php       

                                echo $d['nama_pelanggan']       
                                    ? htmlspecialchars($d['nama_pelanggan'])       
                                    : "Umum";       

                                ?>       

                            </td>   

                            <td class="detail-produk">   

                                <?php   

                                if (   
                                    $detail_produk &&   
                                    mysqli_num_rows($detail_produk) > 0       
                                ) {   

                                    while ($p = mysqli_fetch_assoc($detail_produk)) {   

                                ?>   

                                <div>   

                                    <strong>   
                                        <?php   
                                        echo htmlspecialchars($p['nama_produk']);   
                                        ?>   
                                    </strong>   

                                    × <?php echo $p['jumlah_produk']; ?>   

                                    @ Rp <?php   
                                    echo number_format(   
                                        $p['harga'],   
                                        0,   
                                        ',',   
                                        '.'   
                                    );   
                                    ?>   

                                    = Rp <?php   
                                    echo number_format(   
                                        $p['subtotal'],   
                                        0,   
                                        ',',   
                                        '.'   
                                    );   
                                    ?>   

                                </div>   

                                <?php   

                                    }   

                                } else {   

                                    echo "-";   

                                }   

                                ?>       

                            </td>   


                            <td>       

                                Rp <?php echo number_format(       
                                    $d['total_harga'],       
                                    0,       
                                    ',',       
                                    '.'       
                                ); ?>       

                            </td>       

                            <td>       

                                <?php       

                                echo $d['metode_pembayaran']       
                                    ? htmlspecialchars($d['metode_pembayaran'])       
                                    : "-";       

                                ?>       

                            </td>       

                            <td>       

                                <?php       

                                if ($d['status_transaksi']) {   

                                    echo '<span class="status-laporan">';   
                                    echo htmlspecialchars($d['status_transaksi']);   
                                    echo '</span>';   

                                } else {   

                                    echo "-";   

                                }   

                                ?>       

                            </td>       

                        </tr>       

                        <?php       

                            }       

                        } else {       

                        ?>       

                        <tr>       

                            <td colspan="7" style="text-align: center;">       

                                Tidak ada penjualan pada tanggal tersebut.       

                            </td>       

                        </tr>       

                        <?php } ?>       

                    </table>       

                </div>       


                <?php       

                if (       
                    $data_laporan &&       
                    mysqli_num_rows($data_laporan) > 0       
                ) {       

                ?>       

                <div class="total-laporan">       

                    Total Penjualan:       
                    Rp <?php echo number_format(       
                        $total_laporan,       
                        0,       
                        ',',       
                        '.'       
                    ); ?>       

                </div>   

                <div class="area-cetak">   

                    <button   
                        type="button"   
                        class="btn-cetak"   
                        onclick="window.print()"   
                    >   
                        Cetak Laporan   
                    </button>   

                </div>       

                <?php } ?>       




            </div>       

            <?php } ?>       

        </div>       

    </div>       

</div>       

</body>       

</html>