<?php 
use App\Core\Auth;
$this->layout('layouts/app', ['title' => $title]) 
?>

<?php $this->start('main') ?>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Modifier le membre</h2>
                    <a href="/members" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
                <div class="card-body">
                    <form action="/members/update/<?= $member['id'] ?>" method="post">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?= htmlspecialchars($member['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?= htmlspecialchars($member['last_name']) ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Genre</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Sélectionner</option>
                                    <option value="M" <?= $member['gender'] === 'M' ? 'selected' : '' ?>>Homme</option>
                                    <option value="F" <?= $member['gender'] === 'F' ? 'selected' : '' ?>>Femme</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="birthdate" class="form-label">Date de naissance</label>
                                <input type="date" class="form-control" id="birthdate" name="birthdate" 
                                       value="<?= $member['birthdate'] ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($member['phone']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($member['email']) ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Adresse</label>
                            <textarea class="form-control" id="address" name="address" rows="2"><?= htmlspecialchars($member['address']) ?></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="family_id" class="form-label">Famille</label>
                                <select class="form-select" id="family_id" name="family_id">
                                    <option value="">Sélectionner une famille</option>
                                    <?php foreach ($families as $family): ?>
                                        <option value="<?= $family['id'] ?>" 
                                                <?= $member['family_id'] == $family['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($family['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="profession" class="form-label">Profession</label>
                                <input type="text" class="form-control" id="profession" name="profession" 
                                       value="<?= htmlspecialchars($member['profession']) ?>">
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->stop() ?>
