<!DOCTYPE html>
<html>
<head>
    <title>Login Toko Sembako</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <div class="login-page">
        <div class="login-box">

            <h2>Toko Sembako</h2>

            <?php if (isset($_GET['pesan'])) { ?>
                <div class="alert">
                    Username, password, atau level salah.
                </div>
            <?php } ?>

            <form action="proses_login.php" method="POST">

                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username" required>

                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>

                <label>Level</label>
                <select name="level" required>
                    <option value="">Pilih level</option>
                    <option value="admin">Admin</option>
                    <option value="kasir">Kasir</option>
                </select>

                <button type="submit">Login</button>

            </form>

        </div>
    </div>

</body>
</html>