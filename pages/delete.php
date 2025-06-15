<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auto_check.php';

if (!isset($_GET['id'])) {
   header("Location: index.php?page=exhibits");
    exit();
}

$id = (int)$_GET['id'];


$stmt = $db->prepare("SELECT user_id FROM exhibits WHERE id = ?");
$stmt->execute([$id]);
$exhibit = $stmt->fetch();

if (!$exhibit) {
   header("Location: index.php?page=exhibits");
    exit();
}





if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $db->prepare("DELETE FROM exhibits WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success'] = "Eser başarıyla silindi";
        header("Location: index.php?page=exhibits");
        exit();
    } catch(PDOException $e) {
        die("Silme hatası: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Eser Sil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    
    
    <div class="container mt-4">
        <h2>Eser Sil</h2>
        <div class="alert alert-warning">
            <p>Bu eseri silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!</p>
            
            <form method="POST">
                <button type="submit" class="btn btn-danger">Evet, Sil</button>
                <a href="index.php?page=exhibits" class="btn btn-secondary">Hayır, İptal</a>

            </form>
        </div>
    </div>
    
  
</body>
</html>