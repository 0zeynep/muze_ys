<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errors[] = "Kullanıcı adı ve şifre boş olamaz.";
    } else {
        // Kullanıcı adıyla veritabanında sorgu yap
        $stmt = $db->prepare("SELECT user_id, username, password_hash, role FROM users WHERE username = :username LIMIT 1");
        $stmt->bindValue(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Giriş başarılı, oturumu başlat
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
             $_SESSION['logged_in'] = true;
            // Anasayfaya ya da dashboard'a yönlendir
            header("Location: index.php?page=dashboard");
            exit();
        } else {
            $errors[] = "Kullanıcı adı veya şifre yanlış.";
        }
    }
}

// Çıktı tamponlamayı başlat
ob_start();
?>

<div class="container">
    <h2>Giriş Yap</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=login">
        <div class="mb-3">
            <label for="username" class="form-label">Kullanıcı Adı</label>
            <input type="text" name="username" id="username" class="form-control" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Şifre</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Giriş Yap</button>
    </form>

    <p class="mt-3">
        Henüz hesabınız yok mu? <a href="index.php?page=register">Kayıt Ol</a><br>
    
    </p>
</div>

<?php
$content = ob_get_clean();
$page_title = "Giriş Yap";
require_once __DIR__ . '/../layout.php';

?>
