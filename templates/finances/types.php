<?php 
use App\Core\Auth;
$this->layout('layouts/app', ['title' => 'Types de Transactions']) 
?>

<?php $this->start('main') ?>
<div class="container py-4">
    <div class="row">
        <!-- Formulaire d'ajout -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Nouveau type de finance</h5>
                </div>
                <div class="card-body">
                    <form action="/finances/types/store" method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">Catégorie *</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Sélectionner une catégorie</option>
                                <option value="Entrée">Entrée</option>
                                <option value="Sortie">Sortie</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Liste des types -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Types de finance</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($types)): ?>
                        <div class="alert alert-info">
                            Aucun type de finance n'a été créé.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Catégorie</th>
                                        <th>Description</th>
                                        <th>Transactions</th>
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($types as $type): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($type['name']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $type['category'] === 'Entrée' ? 'success' : 'danger' ?>">
                                                    <?= $type['category'] ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($type['description'] ?? '-') ?></td>
                                            <td><?= number_format($type['transaction_count'], 0, ',', ' ') ?></td>
                                            <td class="text-<?= $type['category'] === 'Entrée' ? 'success' : 'danger' ?>">
                                                <?= number_format($type['total'] ?? 0, 0, ',', ' ') ?> FCFA
                                            </td>
                                            <td>
                                                <?php if ($type['transaction_count'] == 0): ?>
                                                    <form action="/finances/types/delete/<?= $type['id'] ?>" 
                                                          method="post" class="d-inline"
                                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce type ?');">
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-danger btn-sm" disabled 
                                                            title="Ce type ne peut pas être supprimé car il est utilisé par des transactions">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->stop() ?>
