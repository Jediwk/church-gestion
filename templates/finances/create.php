<?php 
use App\Core\Auth;
$this->layout('layouts/app', ['title' => 'Nouvelle Transaction']) 
?>

<?php $this->start('main') ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Nouvelle transaction</h5>
                    <a href="/finances" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
                <div class="card-body">
                    <form action="/finances/store" method="post">
                        <!-- Type de finance -->
                        <div class="mb-3">
                            <label for="type_id" class="form-label">Type de finance *</label>
                            <div class="input-group">
                                <select class="form-select" id="type_id" name="type_id" required>
                                    <option value="">Sélectionner un type</option>
                                    <optgroup label="Entrées">
                                        <?php foreach ($types as $type): ?>
                                            <?php if ($type['category'] === 'Entrée'): ?>
                                                <option value="<?= $type['id'] ?>">
                                                    <?= htmlspecialchars($type['name']) ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <optgroup label="Sorties">
                                        <?php foreach ($types as $type): ?>
                                            <?php if ($type['category'] === 'Sortie'): ?>
                                                <option value="<?= $type['id'] ?>">
                                                    <?= htmlspecialchars($type['name']) ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </optgroup>
                                </select>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#newTypeModal">
                                    <i class="fas fa-plus"></i> Nouveau type
                                </button>
                            </div>
                        </div>

                        <!-- Montant -->
                        <div class="mb-3">
                            <label for="amount" class="form-label">Montant (FCFA) *</label>
                            <input type="number" class="form-control" id="amount" name="amount" 
                                   min="0" step="100" required>
                        </div>

                        <!-- Date -->
                        <div class="mb-3">
                            <label for="date" class="form-label">Date *</label>
                            <input type="date" class="form-control" id="date" name="date" 
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="2" placeholder="Description de la transaction"></textarea>
                        </div>

                        <!-- Référence -->
                        <div class="mb-3">
                            <label for="reference" class="form-label">Référence</label>
                            <input type="text" class="form-control" id="reference" name="reference" 
                                   placeholder="Numéro de référence ou reçu">
                        </div>

                        <!-- Options -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="generate_receipt" 
                                       name="generate_receipt" value="1">
                                <label class="form-check-label" for="generate_receipt">
                                    Générer un reçu après l'enregistrement
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer la transaction
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour nouveau type -->
<div class="modal fade" id="newTypeModal" tabindex="-1" aria-labelledby="newTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newTypeModalLabel">Nouveau type de finance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="newTypeForm" onsubmit="return createNewType(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_type_name" class="form-label">Nom *</label>
                        <input type="text" class="form-control" id="new_type_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_type_category" class="form-label">Catégorie *</label>
                        <select class="form-select" id="new_type_category" name="category" required>
                            <option value="">Sélectionner une catégorie</option>
                            <option value="Entrée">Entrée</option>
                            <option value="Sortie">Sortie</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_type_description" class="form-label">Description</label>
                        <textarea class="form-control" id="new_type_description" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Générer une référence automatique
    if (!document.getElementById('reference').value) {
        const date = new Date();
        const ref = 'TR-' + date.getFullYear() + 
                   String(date.getMonth() + 1).padStart(2, '0') + 
                   String(date.getDate()).padStart(2, '0') + '-' +
                   String(Math.floor(Math.random() * 1000)).padStart(3, '0');
        document.getElementById('reference').value = ref;
    }
});

async function createNewType(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/finances/types/store', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Ajouter le nouveau type à la liste
            const select = document.getElementById('type_id');
            const option = new Option(result.type.name, result.type.id);
            const optgroup = select.querySelector(`optgroup[label="${result.type.category}s"]`);
            optgroup.appendChild(option);
            
            // Sélectionner le nouveau type
            option.selected = true;
            
            // Fermer le modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('newTypeModal'));
            modal.hide();
            
            // Réinitialiser le formulaire
            form.reset();
            
            // Afficher un message de succès
            alert('Type de finance ajouté avec succès !');
        } else {
            throw new Error(result.message || 'Une erreur est survenue');
        }
    } catch (error) {
        alert(error.message);
    }
    
    return false;
}
</script>

<?php $this->stop() ?>
