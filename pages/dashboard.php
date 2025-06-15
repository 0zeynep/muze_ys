<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
}

if (!function_exists('has_role')) {
    function has_role($role) {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
}

global $db; 
$stats = [
    'total_exhibits' => 0,
    'my_exhibits' => 0,
    'total_users' => 0,
    'recent_exhibits' => [],
    'category_counts' => []
];

try {
    $stats['total_exhibits'] = $db->query("SELECT COUNT(*) FROM exhibits")->fetchColumn();
    
    if (isset($_SESSION['user_id'])) {
        $stmt_my_exhibits = $db->prepare("SELECT COUNT(*) FROM exhibits WHERE user_id = :user_id");
        $stmt_my_exhibits->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt_my_exhibits->execute();
        $stats['my_exhibits'] = $stmt_my_exhibits->fetchColumn();
    }

    $stats['total_users'] = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    $stmt_recent_exhibits = $db->query("SELECT * FROM exhibits ORDER BY created_at DESC LIMIT 5");
    $stats['recent_exhibits'] = $stmt_recent_exhibits->fetchAll(PDO::FETCH_OBJ);

    $stmt_category_counts = $db->query("SELECT category, COUNT(*) as count FROM exhibits GROUP BY category");
    $category_data = $stmt_category_counts->fetchAll(PDO::FETCH_ASSOC);
    
    $chart_labels = [];
    $chart_data = [];
    $chart_colors = ['#3a5a78', '#6c757d', '#0d6efd', '#20c997', '#fd7e14', '#6610f2'];

    foreach ($category_data as $row) {
        $chart_labels[] = htmlspecialchars($row['category']);
        $chart_data[] = (int)$row['count'];
    }
    $dynamic_chart_colors = [];
    for ($i = 0; $i < count($chart_labels); $i++) {
        $dynamic_chart_colors[] = $chart_colors[$i % count($chart_colors)];
    }

} catch (PDOException $e) {
    $_SESSION['error'] = "İstatistikler yüklenirken bir hata oluştu: " . htmlspecialchars($e->getMessage());
    error_log("Dashboard istatistik hatası: " . $e->getMessage());
}


ob_start();
?>
<div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
    <div class="col">
        <div class="card h-100 text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Toplam Eser</h5>
                        <h1 class="mb-0"><?= $stats['total_exhibits'] ?></h1>
                    </div>
                    <i class="bi bi-collection fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card h-100 text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Eserlerim</h5>
                        <h1 class="mb-0"><?= $stats['my_exhibits'] ?></h1>
                    </div>
                    <i class="bi bi-person-badge fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    
    <?php if(function_exists('has_role') && has_role('admin')): ?>
    <div class="col">
        <div class="card h-100 text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Kullanıcılar</h5>
                        <h1 class="mb-0"><?= $stats['total_users'] ?></h1>
                    </div>
                    <i class="bi bi-people fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Son Eklenen Eserler</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Eser Adı</th>
                                <th>Kategori</th>
                                <th>Tarih</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($stats['recent_exhibits'])): ?>
                                <?php foreach($stats['recent_exhibits'] as $exhibit): ?>
                                <tr>
                                    <td><?= htmlspecialchars($exhibit->title) ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($exhibit->category) ?></span></td>
                                    <td><?= date('d/m/Y H:i', strtotime($exhibit->created_at)) ?></td>
                                    <td class="text-end">
                                        <a href="index.php?page=view&id=<?= htmlspecialchars($exhibit->id) ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Görüntüle
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Henüz eklenmiş eser bulunmamaktadır.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Eser Kategorileri</h5>
            </div>
            <div class="card-body">
                <canvas id="statsChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('statsChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($chart_labels); ?>,
            datasets: [{
                data: <?= json_encode($chart_data); ?>,
                backgroundColor: <?= json_encode($dynamic_chart_colors); ?>,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>

<?php

$content = ob_get_clean();

$page_title = "Panel"; 
.
include 'layout.php'; 
?>
