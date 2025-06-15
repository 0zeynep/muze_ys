<?php
// Oturumu başlat (eğer başlatılmamışsa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Eğer fonksiyonlar henüz tanımlanmadıysa
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
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Müze Yönetim Sistemi') ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root {
            --primary-color: #3a5a78;
            --secondary-color: #6c757d;
        }
        
        body {
            padding-top: 56px;
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            height: calc(100vh - 56px);
            position: sticky;
            top: 56px;
            background-color: #212529;
            overflow-y: auto;
        }
        
        .sidebar .nav-link {
            color: #adb5bd;
            border-radius: 0.375rem;
            margin-bottom: 0.25rem;
        }
        
        .sidebar .nav-link:hover, 
        .sidebar .nav-link.active {
            color: white;
            background-color: var(--primary-color);
        }
        
        .navbar-brand {
            font-weight: 700;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .card {
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-3px);
        }
        
        .exhibit-card .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        
        footer {
            background-color: var(--primary-color);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top shadow">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php?page=dashboard">
                <i class="bi bi-building"></i> Müze YS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=dashboard"><i class="bi bi-house"></i> Anasayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=exhibits"><i class="bi bi-collection"></i> Eserler</a>
                    </li>
                    <?php if(is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=create"><i class="bi bi-plus-circle"></i> Ekle</a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if(is_logged_in()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username'] ?? 'Kullanıcı') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                           
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?page=logout"><i class="bi bi-box-arrow-right"></i> Çıkış</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=login"><i class="bi bi-box-arrow-in-right"></i> Giriş</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=register"><i class="bi bi-person-plus"></i> Kayıt</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">
            <?php if(is_logged_in()): ?>
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-dark p-3">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['page'] ?? '') === 'dashboard' ? 'active' : '' ?>" 
                               href="index.php?page=dashboard">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['page'] ?? '') === 'exhibits' ? 'active' : '' ?>" 
                               href="index.php?page=exhibits">
                                <i class="bi bi-collection me-2"></i> Eserler
                            </a>
                        </li>
                       
                    </ul>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Main Area -->
            <main class="<?= is_logged_in() ? 'col-md-9 col-lg-10' : 'col-12' ?> ms-sm-auto px-md-4 py-4">
                <?php 
                // Hata mesajları ve başarı bildirimleri
                if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?= $content ?? 'İçerik bulunamadı' ?>
            </main>
        </div>
    </div>

   

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // DataTables başlatma
            $('.datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/tr.json'
                }
            });
            
            // Otomatik kapanan alert'leri yönet
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>
</html>