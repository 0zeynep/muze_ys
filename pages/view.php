<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auto_check.php';

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$id = (int)$_GET['id'];

// Eser bilgilerini getir
$stmt = $db->prepare("SELECT e.*, u.username FROM exhibits e JOIN users u ON e.user_id = u.user_id WHERE e.id = ?");
$stmt->execute([$id]);
$exhibit = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$exhibit) {
    header("Location: list.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Eser Detayları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
   
    
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h2><?= htmlspecialchars($exhibit['title']) ?></h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <p><strong>Sanatçı/Dönem:</strong> <?= htmlspecialchars($exhibit['artist_period']) ?></p>
                        <p><strong>Kategori:</strong> <?= htmlspecialchars($exhibit['category']) ?></p>
                        <p><strong>Konum:</strong> <?= htmlspecialchars($exhibit['location']) ?></p>
                        <p><strong>Edinme Tarihi:</strong> <?= $exhibit['acquisition_date'] ?></p>
                        <p><strong>Ekleyen:</strong> <?= htmlspecialchars($exhibit['username']) ?></p>
                        <p><strong>Ekleme Tarihi:</strong> <?= $exhibit['created_at'] ?></p>
                    </div>
                </div>
                
                <hr>
                
                <h4>Açıklama</h4>
                <p><?= nl2br(htmlspecialchars($exhibit['description'])) ?></p>
                
                <div class="mt-4">
                   <a href="index.php?page=exhibits" class="btn btn-secondary">Geri Dön</a>
                   <a href="index.php?page=edit&id=<?= $exhibit['id'] ?>" class="btn btn-primary">Düzenle</a>

                </div>
            </div>
        </div>
    </div>
    
 
</body>
</html>