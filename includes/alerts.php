<?php
// /muze_ys/includes/alerts.php

// session_start() zaten index.php tarafından çağrıldığı için burada tekrara gerek yok.
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }
?>

<?php if(isset($_SESSION['success'])): ?>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 11">
    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <strong class="me-auto"><i class="bi bi-check-circle"></i> Başarılı</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
    </div>
</div>
<?php 
unset($_SESSION['success']); 
endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 11">
    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-danger text-white">
            <strong class="me-auto"><i class="bi bi-exclamation-triangle"></i> Hata</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
    </div>
</div>
<?php 
unset($_SESSION['error']); 
endif; ?>

<script>
// Bu script, toast'ların görünür olmasını ve otomatik kapanmasını sağlar.
// Eğer DataTables script'iniz içinde zaten bir $(document).ready() bloğu varsa,
// bu kodu onun içine taşıyabilirsiniz. Layout.php zaten DataTables'ı çağırıyor.
document.addEventListener('DOMContentLoaded', function () {
    var toastElList = [].slice.call(document.querySelectorAll('.toast'));
    var toastList = toastElList.map(function (toastEl) {
        return new bootstrap.Toast(toastEl);
    });
    toastList.forEach(toast => toast.show()); // Tüm toast'ları göster
});
</script>
