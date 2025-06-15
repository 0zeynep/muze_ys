<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h4 class="mb-0"><i class="bi bi-palette"></i> <?= isset($exhibit) ? 'Eser Düzenle' : 'Yeni Eser Ekle' ?></h4>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate action="index.php?page=edit<?= isset($exhibit) ? '&id=' . $exhibit['id'] : '' ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="title" class="form-label">Eser Adı*</label>
                    <input type="text" class="form-control" id="title" name="title" 
                           value="<?= htmlspecialchars($exhibit['title'] ?? '') ?>" required>
                    <div class="invalid-feedback">Lütfen eser adını giriniz</div>
                </div>
                
                <div class="col-md-6">
                    <label for="artist_period" class="form-label">Sanatçı/Dönem</label>
                    <input type="text" class="form-control" id="artist_period" name="artist_period" 
                           value="<?= htmlspecialchars($exhibit['artist_period'] ?? '') ?>">
                </div>
                
                <div class="col-12">
                    <label for="description" class="form-label">Açıklama</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($exhibit['description'] ?? '') ?></textarea>
                </div>
                
                <div class="col-md-4">
                    <label for="category" class="form-label">Kategori*</label>
                    <select class="form-select" id="category" name="category" required>
                        <option value="">Seçiniz</option>
                        <option value="Resim" <?= ($exhibit['category'] ?? '') == 'Resim' ? 'selected' : '' ?>>Resim</option>
                        <option value="Heykel" <?= ($exhibit['category'] ?? '') == 'Heykel' ? 'selected' : '' ?>>Heykel</option>
                        <option value="Seramik" <?= ($exhibit['category'] ?? '') == 'Seramik' ? 'selected' : '' ?>>Seramik</option>
                    </select>
                    <div class="invalid-feedback">Lütfen kategori seçiniz</div>
                </div>
                
                <div class="col-md-4">
                    <label for="location" class="form-label">Konum</label>
                    <input type="text" class="form-control" id="location" name="location" 
                           value="<?= htmlspecialchars($exhibit['location'] ?? '') ?>">
                </div>
                
                <div class="col-md-4">
                    <label for="acquisition_date" class="form-label">Edinme Tarihi</label>
                    <input type="date" class="form-control" id="acquisition_date" name="acquisition_date" 
                           value="<?= htmlspecialchars($exhibit['acquisition_date'] ?? '') ?>">
                </div>
                
                
                    <?php endif; ?>
                </div>
                
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save"></i> <?= isset($exhibit) ? 'Güncelle' : 'Kaydet' ?>
                    </button>
                    <a href="<?= isset($exhibit) ? 'index.php?page=view&id='.$exhibit['id'] : 'index.php?page=exhibits' ?>" class="btn btn-outline-secondary ms-2">

                        <i class="bi bi-x-circle"></i> İptal
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>

document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
});
</script>