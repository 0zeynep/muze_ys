<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auto_check.php';

// Yetkilendirme kontrolü
if (!is_logged_in()) {
    header("Location: index.php?page=login");
    exit();
}

// ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?page=exhibits");
    exit();
}

$id = (int)$_GET['id'];
$errors = [];

// Eser bilgilerini çek
$stmt = $db->prepare("SELECT * FROM exhibits WHERE id = ?");
$stmt->execute([$id]);
$exhibit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exhibit) {
    $_SESSION['message'] = "Bu ID'ye ait eser bulunamadı";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php?page=exhibits");
    exit();
}

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verileri al ve temizle
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $artist_period = trim($_POST['artist_period'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $acquisition_date = $_POST['acquisition_date'] ?? '';

    // Validasyon
    if (empty($title)) $errors[] = "Eser adı boş bırakılamaz";
    if (empty($category)) $errors[] = "Kategori seçmelisiniz";
    if (strlen($title) > 255) $errors[] = "Eser adı 255 karakteri geçemez";

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("UPDATE exhibits SET 
                                title = ?, 
                                description = ?, 
                                artist_period = ?, 
                                category = ?, 
                                location = ?, 
                                acquisition_date = ?
                                
                                WHERE id = ?");
            $stmt->execute([
                $title,
                $description,
                $artist_period,
                $category,
                $location,
                $acquisition_date,
                $id
            ]);

            $_SESSION['message'] = "Eser başarıyla güncellendi!";
            $_SESSION['message_type'] = "success";
            header("Location: index.php?page=exhibits");
            exit();

        } catch (PDOException $e) {
            $errors[] = "Güncelleme hatası: " . $e->getMessage();
            error_log("Eser güncelleme hatası: " . $e->getMessage());
        }
    }
}

// İçeriği tamponla
ob_start();
?>

<div class="container py-4">
    <h1>Eser Düzenle - <?= htmlspecialchars($exhibit['title']) ?></h1>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="mb-3">
            <label for="title" class="form-label">Eser Adı</label>
            <input type="text" class="form-control" id="title" name="title" required maxlength="255"
                   value="<?= htmlspecialchars($_POST['title'] ?? $exhibit['title']) ?>">
            <div class="invalid-feedback">Eser adı zorunludur ve 255 karakteri geçemez.</div>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Açıklama</label>
            <textarea class="form-control" id="description" name="description"><?= htmlspecialchars($_POST['description'] ?? $exhibit['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="artist_period" class="form-label">Sanatçı Dönemi</label>
            <input type="text" class="form-control" id="artist_period" name="artist_period"
                   value="<?= htmlspecialchars($_POST['artist_period'] ?? $exhibit['artist_period']) ?>">
        </div>

        <div class="mb-3">
            <label for="category" class="form-label">Kategori</label>
            <input type="text" class="form-control" id="category" name="category" required
                   value="<?= htmlspecialchars($_POST['category'] ?? $exhibit['category']) ?>">
            <div class="invalid-feedback">Kategori zorunludur.</div>
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Konum</label>
            <input type="text" class="form-control" id="location" name="location"
                   value="<?= htmlspecialchars($_POST['location'] ?? $exhibit['location']) ?>">
        </div>

        <div class="mb-3">
            <label for="acquisition_date" class="form-label">Edinim Tarihi</label>
            <input type="date" class="form-control" id="acquisition_date" name="acquisition_date"
                   value="<?= htmlspecialchars($_POST['acquisition_date'] ?? $exhibit['acquisition_date']) ?>">
        </div>

        <button type="submit" class="btn btn-primary">Güncelle</button>
    </form>
</div>

<script>
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
$page_title = "Eser Düzenle - " . htmlspecialchars($exhibit['title']);
require_once __DIR__ . '/../layout.php';
