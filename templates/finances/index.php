<?php 
use App\Core\Auth;
$this->layout('layouts/app', ['title' => $title]) 
?>

<?php $this->start('main') ?>
<div class="container-fluid py-4">
    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-2">
                    <label for="year" class="form-label">Année</label>
                    <select name="year" id="year" class="form-select">
                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                            <option value="<?= $y ?>" <?= $filters['year'] == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="month" class="form-label">Mois</label>
                    <select name="month" id="month" class="form-select">
                        <option value="">Tous</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $filters['month'] == $m ? 'selected' : '' ?>>
                                <?= strftime('%B', mktime(0, 0, 0, $m, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="type" class="form-label">Type</label>
                    <select name="type" id="type" class="form-select">
                        <option value="">Tous</option>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= $filters['type'] == $t['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="category" class="form-label">Catégorie</label>
                    <select name="category" id="category" class="form-select">
                        <option value="">Toutes</option>
                        <option value="Entrée" <?= $filters['category'] === 'Entrée' ? 'selected' : '' ?>>Entrée</option>
                        <option value="Sortie" <?= $filters['category'] === 'Sortie' ? 'selected' : '' ?>>Sortie</option>
                    </select>
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filtrer</button>
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Exporter
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="<?= $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') ? '&' : '?') ?>export=csv">
                                    Export CSV
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') ? '&' : '?') ?>export=pdf">
                                    Rapport PDF
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total des entrées</h5>
                    <h3><?= number_format($stats['total_income'] ?? 0, 0, ',', ' ') ?> FCFA</h3>
                    <small>Pour la période sélectionnée</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Total des sorties</h5>
                    <h3><?= number_format($stats['total_expense'] ?? 0, 0, ',', ' ') ?> FCFA</h3>
                    <small>Pour la période sélectionnée</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Solde</h5>
                    <h3><?= number_format(($stats['total_income'] ?? 0) - ($stats['total_expense'] ?? 0), 0, ',', ' ') ?> FCFA</h3>
                    <small>Pour la période sélectionnée</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques par type -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Entrées par type</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th class="text-end">Montant</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalIncome = array_reduce($typeStats, function($carry, $item) {
                                    return $carry + ($item['category'] === 'Entrée' ? $item['total'] : 0);
                                }, 0);
                                
                                foreach ($typeStats as $stat):
                                    if ($stat['category'] === 'Entrée'):
                                        $percentage = $totalIncome > 0 ? ($stat['total'] / $totalIncome * 100) : 0;
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($stat['name']) ?></td>
                                        <td class="text-end"><?= number_format($stat['total'], 0, ',', ' ') ?> FCFA</td>
                                        <td class="text-end"><?= number_format($percentage, 1) ?>%</td>
                                    </tr>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Sorties par type</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th class="text-end">Montant</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalExpense = array_reduce($typeStats, function($carry, $item) {
                                    return $carry + ($item['category'] === 'Sortie' ? $item['total'] : 0);
                                }, 0);
                                
                                foreach ($typeStats as $stat):
                                    if ($stat['category'] === 'Sortie'):
                                        $percentage = $totalExpense > 0 ? ($stat['total'] / $totalExpense * 100) : 0;
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($stat['name']) ?></td>
                                        <td class="text-end"><?= number_format($stat['total'], 0, ',', ' ') ?> FCFA</td>
                                        <td class="text-end"><?= number_format($percentage, 1) ?>%</td>
                                    </tr>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des transactions -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Transactions</h5>
            <div class="card-tools">
                <a href="/finances/reports" class="btn btn-info mr-2">
                    <i class="fas fa-chart-bar"></i> Rapports
                </a>
                <a href="/finances/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouvelle transaction
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($transactions)): ?>
                <div class="alert alert-info">
                    Aucune transaction pour la période sélectionnée.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Référence</th>
                                <th class="text-end">Montant</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><?= (new DateTime($transaction['date']))->format('d/m/Y') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $transaction['category'] === 'Entrée' ? 'success' : 'danger' ?>">
                                            <?= htmlspecialchars($transaction['type_name']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($transaction['description'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($transaction['reference'] ?? '-') ?></td>
                                    <td class="text-end">
                                        <span class="text-<?= $transaction['category'] === 'Entrée' ? 'success' : 'danger' ?>">
                                            <?= number_format($transaction['amount'], 0, ',', ' ') ?> FCFA
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/finances/edit/<?= $transaction['id'] ?>" 
                                               class="btn btn-outline-primary" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') ? '&' : '?') ?>receipt=<?= $transaction['id'] ?>" 
                                               class="btn btn-outline-info" title="Reçu" target="_blank">
                                                <i class="fas fa-file-alt"></i>
                                            </a>
                                            <form action="/finances/delete/<?= $transaction['id'] ?>" method="post" 
                                                  class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette transaction ?');">
                                                <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mise à jour automatique du formulaire lors du changement des filtres
    document.querySelectorAll('select[name]').forEach(function(select) {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>
<?php $this->stop() ?>
