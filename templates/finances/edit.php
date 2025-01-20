<?php 
use App\Core\Auth;
$this->layout('layouts/app', ['title' => 'Modifier la Transaction']) 
?>

<?php $this->start('main') ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Modifier la transaction</h5>
                    <a href="/finances" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
                <div class="card-body">
                    <form action="/finances/update/<?= $finance['id'] ?>" method="post">
                        <!-- Type de finance -->
                        <div class="mb-3">
                            <label for="type_id" class="form-label">Type de finance *</label>
                            <select class="form-select" id="type_id" name="type_id" required>
                                <option value="">Sélectionner un type</option>
                                <?php foreach ($types as $type): ?>
                                    <optgroup label="<?= $type['category'] ?>">
                                        <option value="<?= $type['id'] ?>" 
                                                <?= $type['id'] == $finance['type_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($type['name']) ?>
                                        </option>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Montant -->
                        <div class="mb-3">
                            <label for="amount" class="form-label">Montant (FCFA) *</label>
                            <input type="number" class="form-control" id="amount" name="amount" 
                                   value="<?= $finance['amount'] ?>" min="0" step="100" required>
                        </div>

                        <!-- Date -->
                        <div class="mb-3">
                            <label for="date" class="form-label">Date *</label>
                            <input type="date" class="form-control" id="date" name="date" 
                                   value="<?= $finance['date'] ?>" required>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="2"><?= htmlspecialchars($finance['description'] ?? '') ?></textarea>
                        </div>

                        <!-- Référence -->
                        <div class="mb-3">
                            <label for="reference" class="form-label">Référence</label>
                            <input type="text" class="form-control" id="reference" name="reference" 
                                   value="<?= htmlspecialchars($finance['reference'] ?? '') ?>">
                        </div>

                        <!-- Options -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="generate_receipt" 
                                       name="generate_receipt" value="1">
                                <label class="form-check-label" for="generate_receipt">
                                    Générer un nouveau reçu après la modification
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->stop() ?>
