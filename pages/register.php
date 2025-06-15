<?php
// /muze_ys/pages/register.php

// Bu dosya index.php tarafından require_once edildiği için,
// global $db (PDO nesnesi) ve oturum (session) zaten mevcut olmalı.
// is_logged_in() ve has_role() fonksiyonları da erişilebilir olmalı.

global $db; // global $db'yi bu scopeda kullanabilmek için tanımlıyoruz
$error = '';
$success = '';

// Form POST edildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Alanların boş olup olmadığını kontrol et
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Tüm alanları doldurmak zorunludur.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Geçerli bir e-posta adresi girin.";
    } elseif ($password !== $confirm_password) {
        $error = "Şifreler eşleşmiyor.";
    } elseif (strlen($password) < 6) {
        $error = "Şifre en az 6 karakter olmalıdır.";
    } else {
        try {
            // Kullanıcı adının veya e-postanın zaten var olup olmadığını kontrol et
            $stmt = $db->prepare("SELECT user_id FROM users WHERE username = :username OR email = :email");
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                $error = "Bu kullanıcı adı veya e-posta zaten kullanımda.";
            } else {
                // Şifreyi hash'le
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Kullanıcıyı veritabanına ekle
                // *** BURADAKİ DEĞİŞİKLİK: 'role' sütunu 'admin' olarak ayarlandı ***
                $stmt = $db->prepare("INSERT INTO users (username, password_hash, email, role) VALUES (:username, :password_hash, :email, 'admin')");
                $stmt->bindValue(':username', $username, PDO::PARAM_STR);
                $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
                $stmt->bindValue(':email', $email, PDO::PARAM_STR);
                
                if ($stmt->execute()) {
                    $new_user_id = $db->lastInsertId();

                    // Kullanıcıyı oturum açmış gibi işaretle
                    $_SESSION['user_id'] = $new_user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = 'admin'; // Kullanıcının rolü neyse ona göre değiştirin

                    // Eser ekle sayfasına yönlendir
                    header("Location: index.php?page=create");
                    exit();
                    }
                else {
                    $error = "Kayıt sırasında bir hata oluştu.";
                }
            }
        } catch(PDOException $e) {
            $error = "Sistem hatası: " . $e->getMessage();
            error_log("Kayıt hatası: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Müze Yönetim Sistemi - Kayıt Ol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Inter', sans-serif;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        .card-header {
            background-color: #28a745; /* Yeşil tonu */
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            padding: 1.5rem;
            text-align: center;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            border-radius: 10px;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .form-control.rounded-pill {
            border-radius: 50rem !important;
            padding: 0.75rem 1.25rem;
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center mb-0">Müze Yönetim Sistemi - Kayıt Ol</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show rounded" role="alert">
                                <?= htmlspecialchars($success) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show rounded" role="alert">
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="index.php?page=register">
                            <div class="mb-3">
                                <label for="username" class="form-label">Kullanıcı Adı</label>
                                <input type="text" class="form-control rounded-pill" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta</label>
                                <input type="email" class="form-control rounded-pill" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Şifre</label>
                                <input type="password" class="form-control rounded-pill" id="password" name="password" required>
                            </div>
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Şifreyi Tekrar Girin</label>
                                <input type="password" class="form-control rounded-pill" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100 rounded-pill py-2 mb-3">Kayıt Ol</button>
                        </form>
                        <div class="mt-3 text-center">
                            <a href="index.php?page=login" class="text-decoration-none text-success">Zaten hesabınız var mı? Giriş yapın</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
