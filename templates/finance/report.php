<?php
use Core\View;
?>

<div class="container-fluid p-0">
    <div class="row mb-3">
        <div class="col-auto">
            <h1 class="h3 d-inline align-middle">Rapport Financier</h1>
        </div>
        <div class="col-auto ms-auto">
            <div class="btn-group">
                <a href="<?= View::url('/finance/report/export?format=pdf&start_date=' . $start_date . '&end_date=' . $end_date) ?>" 
                   class="btn btn-primary">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                <a href="<?= View::url('/finance/report/export?format=xlsx&start_date=' . $start_date . '&end_date=' . $end_date) ?>" 
                   class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Date de début</label>
                            <input type="date" class="form-control" name="start_date" value="<?= $start_date ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date de fin</label>
                            <input type="date" class="form-control" name="end_date" value="<?= $end_date ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sync"></i> Actualiser
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Résumé -->
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Résumé</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="stat">
                                <h5>Total Revenus</h5>
                                <h2 class="text-success"><?= number_format($report['totals']['total_income'], 2, ',', ' ') ?> €</h2>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="stat">
                                <h5>Total Dépenses</h5>
                                <h2 class="text-danger"><?= number_format($report['totals']['total_expenses'], 2, ',', ' ') ?> €</h2>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <div class="stat">
                                <h5>Balance</h5>
                                <h2 class="<?= $report['totals']['balance'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($report['totals']['balance'], 2, ',', ' ') ?> €
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Évolution mensuelle -->
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Évolution Mensuelle</h5>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Revenus par catégorie -->
        <div class="col-12 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Revenus par Catégorie</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Catégorie</th>
                                    <th class="text-end">Montant</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalIncome = array_sum(array_column($report['income_by_category'], 'total'));
                                foreach ($report['income_by_category'] as $category):
                                    $percentage = ($category['total'] / $totalIncome) * 100;
                                ?>
                                <tr>
                                    <td><?= View::escape($category['name']) ?></td>
                                    <td class="text-end"><?= number_format($category['total'], 2, ',', ' ') ?> €</td>
                                    <td class="text-end"><?= number_format($percentage, 1) ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Total</th>
                                    <th class="text-end"><?= number_format($totalIncome, 2, ',', ' ') ?> €</th>
                                    <th class="text-end">100%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dépenses par catégorie -->
        <div class="col-12 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Dépenses par Catégorie</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Catégorie</th>
                                    <th class="text-end">Montant</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalExpenses = array_sum(array_column($report['expenses_by_category'], 'total'));
                                foreach ($report['expenses_by_category'] as $category):
                                    $percentage = ($category['total'] / $totalExpenses) * 100;
                                ?>
                                <tr>
                                    <td><?= View::escape($category['name']) ?></td>
                                    <td class="text-end"><?= number_format($category['total'], 2, ',', ' ') ?> €</td>
                                    <td class="text-end"><?= number_format($percentage, 1) ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Total</th>
                                    <th class="text-end"><?= number_format($totalExpenses, 2, ',', ' ') ?> €</th>
                                    <th class="text-end">100%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Graphique d'évolution mensuelle
    new Chart(document.getElementById("monthlyChart"), {
        type: "line",
        data: {
            labels: <?= json_encode(array_column($report['monthly_trends'], 'month')) ?>,
            datasets: [{
                label: "Revenus",
                fill: true,
                backgroundColor: "rgba(75, 192, 192, 0.2)",
                borderColor: "rgba(75, 192, 192, 1)",
                data: <?= json_encode(array_column($report['monthly_trends'], 'income')) ?>
            }, {
                label: "Dépenses",
                fill: true,
                backgroundColor: "rgba(255, 99, 132, 0.2)",
                borderColor: "rgba(255, 99, 132, 1)",
                data: <?= json_encode(array_column($report['monthly_trends'], 'expenses')) ?>
            }]
        },
        options: {
            maintainAspectRatio: false,
            legend: {
                display: true
            },
            tooltips: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(tooltipItem, data) {
                        return data.datasets[tooltipItem.datasetIndex].label + ': ' + 
                               new Intl.NumberFormat('fr-FR', { 
                                   style: 'currency', 
                                   currency: 'EUR' 
                               }).format(tooltipItem.yLabel);
                    }
                }
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value, index, values) {
                            return new Intl.NumberFormat('fr-FR', { 
                                style: 'currency', 
                                currency: 'EUR' 
                            }).format(value);
                        }
                    }
                }]
            }
        }
    });
});
</script>
