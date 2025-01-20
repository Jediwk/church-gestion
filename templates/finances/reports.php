<?php $this->layout('layouts/app', ['title' => 'Rapports Financiers']) ?>

<?php $this->start('main') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Rapports Financiers</h3>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <form id="reportForm" class="mb-4" method="GET" action="/finances/reports">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="report_type">Type de rapport</label>
                                    <select class="form-select" id="report_type" name="report_type">
                                        <option value="monthly" <?= $report_type === 'monthly' ? 'selected' : '' ?>>Mensuel</option>
                                        <option value="annual" <?= $report_type === 'annual' ? 'selected' : '' ?>>Annuel</option>
                                        <option value="custom" <?= $report_type === 'custom' ? 'selected' : '' ?>>Personnalisé</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3" id="monthSelector" <?= $report_type !== 'monthly' ? 'style="display:none;"' : '' ?>>
                                <div class="form-group">
                                    <label for="month">Mois</label>
                                    <input type="month" class="form-control" id="month" name="month" value="<?= $month ?>">
                                </div>
                            </div>
                            <div class="col-md-3" id="yearSelector" <?= $report_type !== 'annual' ? 'style="display:none;"' : '' ?>>
                                <div class="form-group">
                                    <label for="year">Année</label>
                                    <select class="form-select" id="year" name="year">
                                        <?php for($y = date('Y'); $y >= date('Y')-5; $y--): ?>
                                            <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6" id="dateRangeSelector" <?= $report_type !== 'custom' ? 'style="display:none;"' : '' ?>>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="start_date">Date début</label>
                                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $start_date ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="end_date">Date fin</label>
                                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $end_date ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Filtrer
                                    </button>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="fas fa-download"></i> Exporter
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="<?= $current_url ?>&export=excel">
                                                    <i class="fas fa-file-excel"></i> Excel
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="<?= $current_url ?>&export=pdf">
                                                    <i class="fas fa-file-pdf"></i> PDF
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Résumé -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Entrées</h5>
                                    <h3><?= number_format($total_income, 0, ',', ' ') ?> FCFA</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Sorties</h5>
                                    <h3><?= number_format($total_expense, 0, ',', ' ') ?> FCFA</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card <?= $balance >= 0 ? 'bg-info' : 'bg-warning' ?> text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Balance</h5>
                                    <h3><?= number_format($balance, 0, ',', ' ') ?> FCFA</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Graphiques -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Répartition par type</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="typeChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Évolution dans le temps</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="timeChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau détaillé -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Entrée</th>
                                    <th>Sortie</th>
                                    <th>Référence</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($transaction['date'])) ?></td>
                                        <td><?= htmlspecialchars($transaction['type_name']) ?></td>
                                        <td><?= htmlspecialchars($transaction['description']) ?></td>
                                        <td class="text-end">
                                            <?= $transaction['category'] === 'Entrée' ? number_format($transaction['amount'], 0, ',', ' ') : '' ?>
                                        </td>
                                        <td class="text-end">
                                            <?= $transaction['category'] === 'Sortie' ? number_format($transaction['amount'], 0, ',', ' ') : '' ?>
                                        </td>
                                        <td><?= htmlspecialchars($transaction['reference']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-dark">
                                    <th colspan="3">Total</th>
                                    <th class="text-end"><?= number_format($total_income, 0, ',', ' ') ?></th>
                                    <th class="text-end"><?= number_format($total_expense, 0, ',', ' ') ?></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->stop() ?>

<?php $this->push('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des sélecteurs de date
    const reportType = document.getElementById('report_type');
    const monthSelector = document.getElementById('monthSelector');
    const yearSelector = document.getElementById('yearSelector');
    const dateRangeSelector = document.getElementById('dateRangeSelector');

    reportType.addEventListener('change', function() {
        monthSelector.style.display = this.value === 'monthly' ? 'block' : 'none';
        yearSelector.style.display = this.value === 'annual' ? 'block' : 'none';
        dateRangeSelector.style.display = this.value === 'custom' ? 'block' : 'none';
    });

    // Graphique par type
    const typeCtx = document.getElementById('typeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($type_stats, 'type_name')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($type_stats, 'total')) ?>,
                backgroundColor: [
                    '#4CAF50', '#2196F3', '#FFC107', '#E91E63', '#9C27B0',
                    '#00BCD4', '#FF5722', '#795548', '#607D8B', '#3F51B5'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });

    // Graphique d'évolution
    const timeCtx = document.getElementById('timeChart').getContext('2d');
    new Chart(timeCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($time_stats, 'date')) ?>,
            datasets: [{
                label: 'Entrées',
                data: <?= json_encode(array_column($time_stats, 'income')) ?>,
                borderColor: '#4CAF50',
                fill: false
            }, {
                label: 'Sorties',
                data: <?= json_encode(array_column($time_stats, 'expense')) ?>,
                borderColor: '#E91E63',
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
<?php $this->end() ?>
