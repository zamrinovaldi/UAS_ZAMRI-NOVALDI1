<?php
session_start();
require 'koneksi.php';

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    
    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $_SESSION['user'] = $data;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Gadget Inventory</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #eaeff2; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 20px rgba(0,0,0,0.05); width: 100%; max-width: 380px; }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 25px; font-weight: 600; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #7f8c8d; font-size: 14px; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #dce4ec; border-radius: 6px; box-sizing: border-box; transition: border-color 0.3s; font-size: 15px; }
        .form-group input:focus { border-color: #3498db; outline: none; }
        .btn-login { width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: 600; transition: background 0.3s; }
        .btn-login:hover { background: #2980b9; }
        .alert { color: #fff; background: #e74c3c; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-size: 14px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>E-Gadget System</h2>
        <?php if($error): ?>
            <div class="alert"><?= $error ?></div>
        <?php endif; ?>

        <div style="text-align:center; margin-bottom:20px; border-bottom: 1px solid #eee; padding-bottom: 20px;">
            <p style="font-size:13px; color:#7f8c8d; margin-bottom:10px;">Masuk lebih cepat</p>
            <button type="button" id="btn-login-bio" class="btn-login" style="background:#9b59b6;">Sidik Jari</button>
        </div>

        <p style="text-align:center; font-size:13px; color:#7f8c8d; margin-bottom:15px;">Atau gunakan kredensial manual:</p>
        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn-login">Sign In</button>
        </form>
    </div>

    <script>
    const btnLoginBio = document.getElementById('btn-login-bio');
    if (btnLoginBio) {
        btnLoginBio.addEventListener('click', async () => {
            try {
                const challenge = new Uint8Array(32);
                window.crypto.getRandomValues(challenge);

                const publicKey = {
                    challenge: challenge,
                    rpId: window.location.hostname,
                    timeout: 60000,
                    userVerification: "required"
                };

                const assertion = await navigator.credentials.get({ publicKey });
                const rawId = btoa(String.fromCharCode.apply(null, new Uint8Array(assertion.rawId)));
                
                const response = await fetch('login_biometric.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ credential_id: rawId })
                });
                
                const result = await response.json();
                if (result.status === 'success') {
                    window.location.href = 'dashboard.php';
                } else {
                    alert(result.message || "Gagal login.");
                }
            } catch (err) {
                console.error(err);
                alert("Autentikasi biometrik dibatalkan, atau perangkat ini belum terdaftar di sistem.");
            }
        });
    }
    </script>
</body>
</html>
