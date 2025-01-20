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
                    <h2 class="mb-0">Modifier la famille</h2>
                    <a href="/families" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
                <div class="card-body">
                    <form action="/families/update/<?= $family['id'] ?>" method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom de la famille</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($family['name']) ?>" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($family['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($family['email'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Adresse</label>
                            <textarea class="form-control" id="address" name="address" rows="2"><?= htmlspecialchars($family['address'] ?? '') ?></textarea>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer les modifications
                            </button>
                        </div>
                    </form>

                    <?php if (!empty($members)): ?>
                        <hr>
                        <h4 class="mt-4">Membres de la famille</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Genre</th>
                                        <th>Contact</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($members as $member): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></td>
                                            <td>
                                                <i class="fas fa-<?= $member['gender'] === 'M' ? 'male text-primary' : 'female text-danger' ?>"></i>
                                                <?= $member['gender'] === 'M' ? 'Homme' : 'Femme' ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($member['phone'])): ?>
                                                    <div><i class="fas fa-phone me-1"></i> <?= htmlspecialchars($member['phone']) ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($member['email'])): ?>
                                                    <div><i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($member['email']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/members/edit/<?= $member['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="/members/view/<?= $member['id'] ?>" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <hr>
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            Aucun membre n'est associé à cette famille.
                            <a href="/members/create" class="alert-link">Ajouter un membre</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->stop() ?>
