<?php
use Core\View;
use Core\Session;
?>

<div class="container-fluid p-0">
    <div class="row mb-3">
        <div class="col-auto">
            <h1 class="h3 d-inline align-middle"><?= isset($transaction) ? 'Modifier' : 'Nouvelle' ?> Transaction</h1>
        </div>
        <div class="col-auto ms-auto">
            <a href="<?= View::url('/finance/transactions') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <form action="<?= View::url('/finance/transactions' . (isset($transaction) ? '/' . $transaction['id'] : '')) ?>" 
                          method="POST" id="transactionForm" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= Session::get('csrf_token') ?>">

                        <div class="row g-3">
                            <!-- Informations de base -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="category">Catégorie</label>
                                    <select class="form-select" id="category" name="category_id" required onchange="updateTransactionType(this)">
                                        <option value="">Sélectionner une catégorie</option>
                                        <optgroup label="Revenus">
                                            <?php foreach ($categories as $category): ?>
                                                <?php if ($category['type'] === 'income'): ?>
                                                <option value="<?= $category['id'] ?>" 
                                                        data-type="income"
                                                        <?= (isset($transaction) && $transaction['category_id'] === $category['id']) ? 'selected' : '' ?>>
                                                    <?= View::escape($category['name']) ?>
                                                </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </optgroup>
                                        <optgroup label="Dépenses">
                                            <?php foreach ($categories as $category): ?>
                                                <?php if ($category['type'] === 'expense'): ?>
                                                <option value="<?= $category['id'] ?>"
                                                        data-type="expense"
                                                        <?= (isset($transaction) && $transaction['category_id'] === $category['id']) ? 'selected' : '' ?>>
                                                    <?= View::escape($category['name']) ?>
                                                </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    </select>
                                    <div class="invalid-feedback">
                                        Veuillez sélectionner une catégorie
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="amount">Montant</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="amount" name="amount" 
                                               step="0.01" required min="0.01"
                                               value="<?= isset($transaction) ? $transaction['amount'] : '' ?>"
                                               aria-describedby="amountHelp">
                                        <span class="input-group-text">€</span>
                                        <div class="invalid-feedback">
                                            Veuillez entrer un montant valide
                                        </div>
                                    </div>
                                    <small id="amountHelp" class="form-text text-muted">
                                        Entrez le montant avec deux décimales maximum
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="transaction_date">Date</label>
                                    <input type="date" class="form-control" id="transaction_date" name="transaction_date" 
                                           required
                                           value="<?= isset($transaction) ? $transaction['transaction_date'] : date('Y-m-d') ?>">
                                    <div class="invalid-feedback">
                                        Veuillez sélectionner une date
                                    </div>
                                </div>
                            </div>

                            <!-- Détails supplémentaires -->
                            <div class="col-md-6">
                                <div class="mb-3" id="memberSection">
                                    <label class="form-label" for="member">Membre</label>
                                    <select class="form-select" id="member" name="member_id">
                                        <option value="">Sélectionner un membre</option>
                                        <?php foreach ($members as $member): ?>
                                        <option value="<?= $member['id'] ?>"
                                                <?= (isset($transaction) && $transaction['member_id'] === $member['id']) ? 'selected' : '' ?>>
                                            <?= View::escape($member['last_name']) ?> <?= View::escape($member['first_name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="payment_method">Méthode de paiement</label>
                                    <select class="form-select" id="payment_method" name="payment_method" required>
                                        <option value="cash" <?= (isset($transaction) && $transaction['payment_method'] === 'cash') ? 'selected' : '' ?>>Espèces</option>
                                        <option value="check" <?= (isset($transaction) && $transaction['payment_method'] === 'check') ? 'selected' : '' ?>>Chèque</option>
                                        <option value="transfer" <?= (isset($transaction) && $transaction['payment_method'] === 'transfer') ? 'selected' : '' ?>>Virement</option>
                                        <option value="other" <?= (isset($transaction) && $transaction['payment_method'] === 'other') ? 'selected' : '' ?>>Autre</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Veuillez sélectionner une méthode de paiement
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="reference">Numéro de référence</label>
                                    <input type="text" class="form-control" id="reference" name="reference_number"
                                           value="<?= isset($transaction) ? View::escape($transaction['reference_number']) : '' ?>"
                                           placeholder="Ex: Numéro de chèque">
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label" for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" 
                                              rows="3" maxlength="500"><?= isset($transaction) ? View::escape($transaction['description']) : '' ?></textarea>
                                    <div class="form-text">
                                        <span id="descriptionCount">0</span>/500 caractères
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-secondary" onclick="window.history.back()">Annuler</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                <?= isset($transaction) ? 'Mettre à jour' : 'Enregistrer' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation du formulaire
    const form = document.getElementById('transactionForm');
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

    // Compteur de caractères pour la description
    const description = document.getElementById('description');
    const descriptionCount = document.getElementById('descriptionCount');
    
    function updateCount() {
        const count = description.value.length;
        descriptionCount.textContent = count;
        if (count > 450) {
            descriptionCount.classList.add('text-warning');
        } else {
            descriptionCount.classList.remove('text-warning');
        }
    }
    
    description.addEventListener('input', updateCount);
    updateCount();

    // Mise à jour de l'affichage en fonction du type de transaction
    const categorySelect = document.querySelector('select[name="category_id"]');
    if (categorySelect.value) {
        updateTransactionType(categorySelect);
    }
});

// Met à jour l'affichage en fonction du type de transaction
function updateTransactionType(select) {
    const type = select.options[select.selectedIndex].dataset.type;
    const memberSection = document.getElementById('memberSection');
    const memberSelect = memberSection.querySelector('select');
    
    if (type === 'income') {
        memberSection.style.display = 'block';
        memberSelect.required = true;
    } else {
        memberSection.style.display = 'none';
        memberSelect.required = false;
        memberSelect.value = '';
    }
}
</script>
