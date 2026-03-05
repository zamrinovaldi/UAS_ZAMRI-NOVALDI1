<?php
session_start();
require 'koneksi.php';

$error = "";

if (isset($_POST['login'])) {
    $user = mysqli_real_escape_string($koneksi, $_POST['username']);
    $pass = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$user'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user'] = $row;
            if ($row['role'] === 'admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: pos.php");
            }
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Gadget</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: var(--bg-main);
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-soft);
            text-align: center;
        }
        .logo-placeholder {
            width: 64px;
            height: 64px;
            background: rgba(37, 99, 235, 0.1);
            border-radius: 16px;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: var(--primary);
        }
        .login-card h2 { margin-bottom: 8px; font-weight: 700; color: var(--text-main); font-size: 24px; }
        .login-card p { margin-bottom: 32px; color: var(--text-muted); font-size: 14px; }
        .input-group { margin-bottom: 20px; text-align: left; }
        .input-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: #1a1c2c; }
        .btn-bio {
            background: #f8f9fc;
            border: 1px solid #e1e4e8;
            color: var(--text-main);
            margin-top: 15px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-bio:hover { background: #eee; }
    </style>
</head>
<body>
    <div class="win-bloom-bg"></div>
    
    <div class="win-login-container fade-in">
        <div class="win-avatar-ring">
            <div class="win-avatar">👤</div>
        </div>
        
        <div class="win-glass-card">
            <h2 style="margin-bottom: 5px;">Selamat Datang</h2>
            <p style="margin-bottom: 30px; font-size: 14px; opacity: 0.8;">AI zamri gacor</p>

            <?php if($error): ?>
                <div style="background: rgba(247, 37, 133, 0.2); color: #fff; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; border-left: 4px solid var(--danger);">
                    ⚠️ <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="text" name="username" class="win-input" placeholder="Username" required>
                <input type="password" name="password" class="win-input" placeholder="Password" required>
                
                <div style="text-align: right; margin-top: -15px; margin-bottom: 20px;">
                    <a href="lupa_password.php" style="font-size: 12px; color: var(--primary); text-decoration: none; opacity: 0.8;">Lupa Password?</a>
                </div>
                
                <button type="submit" name="login" class="win-btn-primary">Masuk</button>
                
                <div style="margin: 25px 0; border-bottom: 1px solid rgba(0,0,0,0.05); position: relative;">
                    <span style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: #f3f5f7; padding: 0 15px; font-size: 11px; opacity: 0.6; color: #323130;">OPSI SIGN-IN</span>
                </div>

                <button type="button" id="btn-login-bio" class="win-btn-hello">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0-4.4-3.6-8-8-8s-8 3.6-8 8"/><path d="M12 14c-4.4 0-8 3.6-8 8"/><path d="M12 2v12"/><path d="M12 14a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg>
                    Windows Hello
                </button>
            </form>
        </div>
        
        <p style="margin-top: 30px; color: white; opacity: 0.5; font-size: 12px;">© 2026 E-Gadget System for Windows</p>
    </div>

    <script>
    // Windows Hello Login Logic
    document.getElementById('btn-login-bio').addEventListener('click', async () => {
        try {
            // Visual feedback
            const btn = document.getElementById('btn-login-bio');
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<span>⏳ Memverifikasi...</span>';

            const challenge = new Uint8Array(32);
            window.crypto.getRandomValues(challenge);
            
            const publicKey = {
                challenge: challenge,
                allowCredentials: [], 
                timeout: 60000,
                userVerification: "required"
            };

            const assertion = await navigator.credentials.get({ publicKey });
            const credentialId = btoa(String.fromCharCode.apply(null, new Uint8Array(assertion.rawId)));
            
            const response = await fetch('login_biometric.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ credential_id: credentialId })
            });

            const result = await response.json();
            if (result.status === 'success') {
                window.location.href = 'dashboard.php';
            } else {
                btn.innerHTML = originalContent;
                alert("Login biometrik gagal: " + result.message);
            }
        } catch (err) {
            console.error(err);
            const btn = document.getElementById('btn-login-bio');
            btn.innerHTML = '<span>☝️</span> Windows Hello';
            alert("Windows Hello: Pastikan Anda sudah mendaftarkan sidik jari di Dashboard.");
        }
    });
    </script>
</body>
</html>
