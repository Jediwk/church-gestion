<?php
use Core\View;
use Core\Session;
?>

<div class="container-fluid p-0">
    <div class="row mb-3">
        <div class="col-auto">
            <h1 class="h3 d-inline align-middle">Transactions</h1>
        </div>
        <div class="col-auto ms-auto">
            <a href="<?= View::url('/finance/transactions/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvelle Transaction
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type">
                                <option value="">Tous</option>
                                <option value="income" <?= isset($_GET['type']) && $_GET['type'] === 'income' ? 'selected' : '' ?>>Revenus</option>
                                <option value="expense" <?= isset($_GET['type']) && $_GET['type'] === 'expense' ? 'selected' : '' ?>>Dépenses</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Catégorie</label>
                            <select class="form-select" name="category_id">
                                <option value="">Toutes</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= isset($_GET['category_id']) && $_GET['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= View::escape($category['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date début</label>
                            <input type="date" class="form-control" name="start_date" value="<?= $_GET['start_date'] ?? '' ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date fin</label>
                            <input type="date" class="form-control" name="end_date" value="<?= $_GET['end_date'] ?? '' ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filtrer
                                </button>
                                <a href="<?= View::url('/finance/transactions') ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des transactions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Catégorie</th>
                                    <th>Membre</th>
                                    <th>Montant</th>
                                    <th>Méthode</th>
                                    <th>Référence</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($transactions)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Aucune transaction trouvée</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($transaction['transaction_date'])) ?></td>
                                        <td>
                                            <?php if ($transaction['type'] === 'income'): ?>
                                                <span class="badge bg-success">Revenu</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Dépense</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= View::escape($transaction['category_name']) ?></td>
                                        <td>
                                            <?php if ($transaction['member_id']): ?>
                                                <a href="<?= View::url('/members/view/' . $transaction['member_id']) ?>">
                                                    <?= View::escape($transaction['member_name']) ?>
                                                </a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <span class="<?= $transaction['type'] === 'income' ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format($transaction['amount'], 2, ',', ' ') ?> €
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $methods = [
                                                'cash' => 'Espèces',
                                                'check' => 'Chèque',
                                                'transfer' => 'Virement',
                                                'other' => 'Autre'
                                            ];
                                            echo $methods[$transaction['payment_method']] ?? $transaction['payment_method'];
                                            ?>
                                        </td>
                                        <td><?= View::escape($transaction['reference_number']) ?: '-' ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="<?= View::url('/finance/transactions/edit/' . $transaction['id']) ?>" 
                                                   class="btn btn-sm btn-primary"
                                                   data-bs-toggle="tooltip"
                                                   title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal<?= $transaction['id'] ?>"
                                                        title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>

                                            <!-- Modal de suppression -->
                                            <div class="modal fade" id="deleteModal<?= $transaction['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Confirmer la suppression</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Êtes-vous sûr de vouloir supprimer cette transaction ?</p>
                                                            <p class="mb-0"><strong>Date :</strong> <?= date('d/m/Y', strtotime($transaction['transaction_date'])) ?></p>
                                                            <p class="mb-0"><strong>Montant :</strong> <?= number_format($transaction['amount'], 2, ',', ' ') ?> €</p>
                                                            <p><strong>Catégorie :</strong> <?= View::escape($transaction['category_name']) ?></p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <form action="<?= View::url('/finance/transactions/delete/' . $transaction['id']) ?>" method="POST">
                                                                <input type="hidden" name="csrf_token" value="<?= Session::get('csrf_token') ?>">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                <button type="submit" class="btn btn-danger">
                                                                    <i class="fas fa-trash me-1"></i> Supprimer
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav class="mt-3">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= View::url('/finance/transactions', array_merge($_GET, ['page' => $current_page - 1])) ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == 1 || $i == $total_pages || ($i >= $current_page - 2 && $i <= $current_page + 2)): ?>
                                    <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= View::url('/finance/transactions', array_merge($_GET, ['page' => $i])) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php elseif ($i == $current_page - 3 || $i == $current_page + 3): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= View::url('/finance/transactions', array_merge($_GET, ['page' => $current_page + 1])) ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
