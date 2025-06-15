<?php
// Oturum ve yetkilendirme kontrolü
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['redirect_to'] = 'index.php?page=create';
    $_SESSION['message'] = 'Eser eklemek için lütfen giriş yapın';
    $_SESSION['message_type'] = 'warning';
    header('Location: index.php?page=login');
    exit();
}

global $db;
$errors = [];
$success = '';

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini al ve temizle
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $artist_period = trim($_POST['artist_period'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $acquisition_date = $_POST['acquisition_date'] ?? '';
    $user_id = $_SESSION['user_id'];

    // Validasyon
    if (empty($title)) $errors[] = "Eser adı boş olamaz";
    if (empty($category)) $errors[] = "Kategori seçmelisiniz";
    if (strlen($title) > 255) $errors[] = "Eser adı 255 karakteri geçemez";

    // Hata yoksa veritabanına kaydet
    if (empty($errors)) {
        try {
           $stmt = $db->prepare("INSERT INTO exhibits 
                    (user_id, title, description, artist_period, category, location, acquisition_date) 
                    VALUES (:user_id, :title, :description, :artist_period, :category, :location, :acquisition_date)");
            $stmt->execute([
 
                   ':user_id' => $user_id,
                    ':title' => $title,
                    ':description' => $description,
                    ':artist_period' => $artist_period,
                    ':category' => $category,
                    ':location' => $location,
                    ':acquisition_date' => $acquisition_date
]);

            $_SESSION['message'] = "Eser başarıyla eklendi!";
            $_SESSION['message_type'] = "success";
            header("Location: index.php?page=exhibits");
            exit();
            
        } catch(PDOException $e) {
            $errors[] = "Veritabanı hatası: " . $e->getMessage();
            error_log("Eser ekleme hatası: " . $e->getMessage());
            
          
        }
    }
}

ob_start();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-plus-circle"></i> Yeni Eser Ekle</h2>
        <a href="index.php?page=exhibits" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Listeye Dön
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">
            <form method="POST" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="title" class="form-label">Eser Adı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required
                               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                        <div class="invalid-feedback">Lütfen eser adını girin</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="artist_period" class="form-label">Sanatçı/Dönem</label>
                        <input type="text" class="form-control" id="artist_period" name="artist_period"
                               value="<?= htmlspecialchars($_POST['artist_period'] ?? '') ?>">
                    </div>
                    
                    <div class="col-12">
                        <label for="description" class="form-label">Açıklama</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?= 
                            htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Seçiniz</option>
                            <option value="Resim" <?= ($_POST['category'] ?? '') === 'Resim' ? 'selected' : '' ?>>Resim</option>
                            <option value="Heykel" <?= ($_POST['category'] ?? '') === 'Heykel' ? 'selected' : '' ?>>Heykel</option>
                            <option value="Seramik" <?= ($_POST['category'] ?? '') === 'Seramik' ? 'selected' : '' ?>>Seramik</option>
                            <option value="Diğer" <?= ($_POST['category'] ?? '') === 'Diğer' ? 'selected' : '' ?>>Diğer</option>
                        </select>
                        <div class="invalid-feedback">Lütfen kategori seçin</div>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="location" class="form-label">Konum</label>
                        <input type="text" class="form-control" id="location" name="location"
                               value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="acquisition_date" class="form-label">Edinme Tarihi</label>
                        <input type="date" class="form-control" id="acquisition_date" name="acquisition_date"
                               value="<?= htmlspecialchars($_POST['acquisition_date'] ?? '') ?>">
                    </div>
                    
                   
                    
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save"></i> Kaydet
                        </button>
                        <button type="reset" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-eraser"></i> Temizle
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Client-side validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
});
</script>

<?php
$content = ob_get_clean();
$page_title = "Yeni Eser Ekle";
require_once __DIR__ . '/../layout.php';
?>