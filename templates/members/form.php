<?php
use Core\View;
use Core\Session;
?>

<div class="container-fluid p-0">
    <div class="row mb-3">
        <div class="col-auto">
            <h1 class="h3 d-inline align-middle"><?= isset($member) ? 'Modifier' : 'Nouveau' ?> Membre</h1>
        </div>
        <div class="col-auto ms-auto">
            <a href="<?= View::url('/members') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="<?= View::url('/members' . (isset($member) ? '/' . $member['id'] : '')) ?>" 
                          method="POST" id="memberForm" class="needs-validation" novalidate 
                          enctype="multipart/form-data">
                        
                        <input type="hidden" name="csrf_token" value="<?= Session::get('csrf_token') ?>">

                        <div class="row g-3">
                            <!-- Informations personnelles -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Informations personnelles</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <!-- Photo -->
                                            <div class="col-12 text-center mb-3">
                                                <div class="member-photo-container">
                                                    <img src="<?= isset($member) && $member['photo'] ? View::url('/uploads/members/' . $member['photo']) : View::url('/assets/img/default-avatar.png') ?>" 
                                                         class="img-fluid rounded-circle member-photo" 
                                                         id="photoPreview"
                                                         alt="Photo du membre">
                                                </div>
                                                <div class="mt-2">
                                                    <label class="btn btn-primary btn-sm">
                                                        <i class="fas fa-camera"></i> Changer la photo
                                                        <input type="file" name="photo" id="photoInput" 
                                                               class="d-none" accept="image/*">
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Nom et prénom -->
                                            <div class="col-md-6">
                                                <label class="form-label" for="last_name">Nom</label>
                                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                                       required maxlength="100"
                                                       value="<?= isset($member) ? View::escape($member['last_name']) : '' ?>">
                                                <div class="invalid-feedback">
                                                    Veuillez entrer un nom
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label" for="first_name">Prénom</label>
                                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                                       required maxlength="100"
                                                       value="<?= isset($member) ? View::escape($member['first_name']) : '' ?>">
                                                <div class="invalid-feedback">
                                                    Veuillez entrer un prénom
                                                </div>
                                            </div>

                                            <!-- Date de naissance et genre -->
                                            <div class="col-md-6">
                                                <label class="form-label" for="birth_date">Date de naissance</label>
                                                <input type="date" class="form-control" id="birth_date" name="birth_date"
                                                       value="<?= isset($member) ? $member['birth_date'] : '' ?>">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label" for="gender">Genre</label>
                                                <select class="form-select" id="gender" name="gender">
                                                    <option value="">Non spécifié</option>
                                                    <option value="M" <?= isset($member) && $member['gender'] === 'M' ? 'selected' : '' ?>>Masculin</option>
                                                    <option value="F" <?= isset($member) && $member['gender'] === 'F' ? 'selected' : '' ?>>Féminin</option>
                                                </select>
                                            </div>

                                            <!-- Email et téléphone -->
                                            <div class="col-md-6">
                                                <label class="form-label" for="email">Email</label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       maxlength="255"
                                                       value="<?= isset($member) ? View::escape($member['email']) : '' ?>">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label" for="phone">Téléphone</label>
                                                <input type="tel" class="form-control" id="phone" name="phone" 
                                                       maxlength="20"
                                                       value="<?= isset($member) ? View::escape($member['phone']) : '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Adresse et informations complémentaires -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Adresse et informations complémentaires</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <!-- Adresse -->
                                            <div class="col-12">
                                                <label class="form-label" for="address">Adresse</label>
                                                <input type="text" class="form-control" id="address" name="address" 
                                                       maxlength="255"
                                                       value="<?= isset($member) ? View::escape($member['address']) : '' ?>">
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label" for="postal_code">Code postal</label>
                                                <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                                       maxlength="10"
                                                       value="<?= isset($member) ? View::escape($member['postal_code']) : '' ?>">
                                            </div>

                                            <div class="col-md-8">
                                                <label class="form-label" for="city">Ville</label>
                                                <input type="text" class="form-control" id="city" name="city" 
                                                       maxlength="100"
                                                       value="<?= isset($member) ? View::escape($member['city']) : '' ?>">
                                            </div>

                                            <!-- Famille -->
                                            <div class="col-12">
                                                <label class="form-label" for="family">Famille</label>
                                                <select class="form-select" id="family" name="family_id">
                                                    <option value="">Sélectionner une famille</option>
                                                    <?php foreach ($families as $family): ?>
                                                    <option value="<?= $family['id'] ?>"
                                                            <?= (isset($member) && $member['family_id'] === $family['id']) ? 'selected' : '' ?>>
                                                        <?= View::escape($family['name']) ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <!-- Statut -->
                                            <div class="col-md-6">
                                                <label class="form-label" for="status">Statut</label>
                                                <select class="form-select" id="status" name="status" required>
                                                    <option value="active" <?= (!isset($member) || $member['status'] === 'active') ? 'selected' : '' ?>>Actif</option>
                                                    <option value="inactive" <?= (isset($member) && $member['status'] === 'inactive') ? 'selected' : '' ?>>Inactif</option>
                                                </select>
                                            </div>

                                            <!-- Date d'adhésion -->
                                            <div class="col-md-6">
                                                <label class="form-label" for="join_date">Date d'adhésion</label>
                                                <input type="date" class="form-control" id="join_date" name="join_date"
                                                       value="<?= isset($member) ? $member['join_date'] : date('Y-m-d') ?>">
                                            </div>

                                            <!-- Notes -->
                                            <div class="col-12">
                                                <label class="form-label" for="notes">Notes</label>
                                                <textarea class="form-control" id="notes" name="notes" 
                                                          rows="3" maxlength="1000"><?= isset($member) ? View::escape($member['notes']) : '' ?></textarea>
                                                <div class="form-text">
                                                    <span id="notesCount">0</span>/1000 caractères
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-secondary" onclick="window.history.back()">Annuler</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                <?= isset($member) ? 'Mettre à jour' : 'Enregistrer' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.member-photo-container {
    width: 150px;
    height: 150px;
    margin: 0 auto;
    overflow: hidden;
    border-radius: 50%;
    border: 3px solid #e9ecef;
}

.member-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

@media (max-width: 768px) {
    .member-photo-container {
        width: 120px;
        height: 120px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation du formulaire
    const form = document.getElementById('memberForm');
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        } else {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enregistrement...';
        }
        form.classList.add('was-validated');
    });

    // Prévisualisation de la photo
    const photoInput = document.getElementById('photoInput');
    const photoPreview = document.getElementById('photoPreview');
    
    photoInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.src = e.target.result;
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Compteur de caractères pour les notes
    const notes = document.getElementById('notes');
    const notesCount = document.getElementById('notesCount');
    
    function updateCount() {
        const count = notes.value.length;
        notesCount.textContent = count;
        if (count > 900) {
            notesCount.classList.add('text-warning');
        } else {
            notesCount.classList.remove('text-warning');
        }
    }
    
    notes.addEventListener('input', updateCount);
    updateCount();
});
</script>
