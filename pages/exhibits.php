<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auto_check.php';

// Yetkilendirme kontrolü
if (!is_logged_in()) {
    $_SESSION['message'] = 'Eserleri görüntülemek için lütfen giriş yapın.';
    $_SESSION['message_type'] = 'danger';
    header("Location: index.php?page=login");
    exit();
}

// Sayfalama ayarları
$current_page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$per_page = 10;
$offset = ($current_page - 1) * $per_page;

// Arama ve filtreleme
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Sorgu oluşturma
$query = "SELECT e.*, u.username FROM exhibits e JOIN users u ON e.user_id = u.user_id";
$params = [];

if (!empty($search)) {
    $query .= " WHERE (e.title LIKE ? OR e.artist_period LIKE ? OR e.description LIKE ?)";
    $params = array_fill(0, 3, "%$search%");
}

if (!empty($category)) {
    $query .= empty($search) ? " WHERE " : " AND ";
    $query .= "e.category = ?";
    $params[] = $category;
}

// Toplam kayıt sayısı
$count_query = str_replace('e.*, u.username', 'COUNT(*) as total', $query);
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$total_items = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

 

$total_pages = ceil($total_items / $per_page);

// Verileri çekme
$query .= " ORDER BY e.created_at DESC LIMIT $offset, $per_page";
$stmt = $db->prepare($query);
$stmt->execute($params);
$exhibits = $stmt->fetchAll(PDO::FETCH_OBJ);

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-collection"></i> Eserler</h2>
    <?php if(has_role('staff') || has_role('admin')): ?>
    <a href="index.php?page=create" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Yeni Eser
    </a>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?= $_SESSION['message_type'] ?? 'info' ?> alert-dismissible fade show rounded" role="alert">
        <?= $_SESSION['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
<?php endif; ?>

<!-- Arama ve Filtreleme Formu -->
<form method="GET" action="index.php?page=exhibits" class="mb-4">
    <input type="hidden" name="page" value="exhibits">
    <div class="row g-3">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Arama..." 
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-4">
            <select name="category" class="form-select">
                <option value="">Tüm Kategoriler</option>
                <option value="Resim" <?= $category == 'Resim' ? 'selected' : '' ?>>Resim</option>
                <option value="Heykel" <?= $category == 'Heykel' ? 'selected' : '' ?>>Heykel</option>
                <option value="Seramik" <?= $category == 'Seramik' ? 'selected' : '' ?>>Seramik</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filtrele</button>
        </div>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (!empty($exhibits)): ?>
        <div class="table-responsive">
            <table id="exhibitsTable" class="table table-striped table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Eser Adı</th>
                        <th>Sanatçı/Dönem</th>
                        <th>Kategori</th>
                        <th>Ekleyen</th>
                        <th>Tarih</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($exhibits as $exhibit): ?>
                    <tr>
                        <td><?= htmlspecialchars($exhibit->id) ?></td>
                        <td><?= htmlspecialchars($exhibit->title) ?></td>
                        <td><?= htmlspecialchars($exhibit->artist_period ?? '') ?></td>
                        <td><span class="badge bg-primary"><?= htmlspecialchars($exhibit->category) ?></span></td>
                        <td><?= htmlspecialchars($exhibit->username) ?></td>
                        <td><?= date('d/m/Y', strtotime($exhibit->created_at)) ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="index.php?page=view&id=<?= $exhibit->id ?>" class="btn btn-info" title="Görüntüle">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if($_SESSION['user_id'] == $exhibit->user_id || has_role('admin')): ?>
                                <a href="index.php?page=edit&id=<?= $exhibit->id ?>" class="btn btn-warning" title="Düzenle">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="index.php?page=delete&id=<?= $exhibit->id ?>" class="btn btn-danger" 
                                   title="Sil" onclick="return confirm('Bu eseri silmek istediğinize emin misiniz?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Sayfalama -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($current_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" 
                           href="index.php?page=exhibits&page_num=<?= $current_page-1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>">
                            Önceki
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                        <a class="page-link" 
                           href="index.php?page=exhibits&page_num=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" 
                           href="index.php?page=exhibits&page_num=<?= $current_page+1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>">
                            Sonraki
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php else: ?>
            <div class="alert alert-info mb-0">Henüz hiç eser bulunmamaktadır.</div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$page_title = "Eser Listesi";
require_once __DIR__ . '/../layout.php';
?>