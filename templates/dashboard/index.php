<?php $this->layout('layouts/app', ['title' => 'Tableau de bord']) ?>

<?php $this->start('main') ?>
<div class="container-fluid py-4">
    <!-- Cartes de statistiques -->
    <div class="row mb-4">
        <!-- Statistiques du mois en cours -->
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Entrées (Mois)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($current_month_finances['total_income'], 0, ',', ' ') ?> FCFA</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Sorties (Mois)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($current_month_finances['total_expense'], 0, ',', ' ') ?> FCFA</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Membres</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $members['total_members'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Familles</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $families['total_families'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-home fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row mb-4">
        <!-- Évolution des finances -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Évolution des finances (12 derniers mois)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="financeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Distribution par âge -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Distribution par âge</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="ageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 5 et Dernières activités -->
    <div class="row">
        <!-- Top 5 des entrées -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 5 des entrées</h6>
                </div>
                <div class="card-body">
                    <?php foreach ($top_income_types as $type): ?>
                    <h4 class="small font-weight-bold">
                        <?= htmlspecialchars($type['name']) ?>
                        <span class="float-right"><?= number_format($type['total'], 0, ',', ' ') ?> FCFA</span>
                    </h4>
                    <div class="progress mb-4">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= ($type['total'] / $current_year_finances['total_income'] * 100) ?>%"></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Top 5 des sorties -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 5 des sorties</h6>
                </div>
                <div class="card-body">
                    <?php foreach ($top_expense_types as $type): ?>
                    <h4 class="small font-weight-bold">
                        <?= htmlspecialchars($type['name']) ?>
                        <span class="float-right"><?= number_format($type['total'], 0, ',', ' ') ?> FCFA</span>
                    </h4>
                    <div class="progress mb-4">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?= ($type['total'] / $current_year_finances['total_expense'] * 100) ?>%"></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Dernières activités -->
    <div class="row">
        <!-- Dernières transactions -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dernières transactions</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_transactions as $transaction): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($transaction['date'])) ?></td>
                                    <td><?= htmlspecialchars($transaction['type_name']) ?></td>
                                    <td class="<?= $transaction['category'] === 'Entrée' ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format($transaction['amount'], 0, ',', ' ') ?> FCFA
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Derniers membres -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Derniers membres</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Genre</th>
                                    <th>Téléphone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_members as $member): ?>
                                <tr>
                                    <td><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></td>
                                    <td><?= $member['gender'] === 'M' ? 'Homme' : 'Femme' ?></td>
                                    <td><?= htmlspecialchars($member['phone']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles personnalisés -->
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}
.chart-area {
    position: relative;
    height: 20rem;
}
.chart-pie {
    position: relative;
    height: 15rem;
}
</style>

<!-- Scripts pour les graphiques -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique d'évolution des finances
    var ctx = document.getElementById('financeChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($monthly_stats ? array_map(function($stat) {
                return date('M Y', strtotime($stat['month'] . '-01'));
            }, $monthly_stats) : []) ?>,
            datasets: [{
                label: 'Entrées',
                data: <?= json_encode($monthly_stats ? array_map(function($stat) {
                    return $stat['income'];
                }, $monthly_stats) : []) ?>,
                borderColor: '#1cc88a',
                backgroundColor: 'rgba(28, 200, 138, 0.1)',
                tension: 0.3
            }, {
                label: 'Sorties',
                data: <?= json_encode($monthly_stats ? array_map(function($stat) {
                    return $stat['expense'];
                }, $monthly_stats) : []) ?>,
                borderColor: '#e74a3b',
                backgroundColor: 'rgba(231, 74, 59, 0.1)',
                tension: 0.3
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' FCFA';
                        }
                    }
                }
            }
        }
    });

    // Graphique de distribution par âge
    var ctx2 = document.getElementById('ageChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($age_distribution ? array_map(function($age) {
                return $age['age_group'] . ' ans';
            }, $age_distribution) : []) ?>,
            datasets: [{
                data: <?= json_encode($age_distribution ? array_map(function($age) {
                    return $age['count'];
                }, $age_distribution) : []) ?>,
                backgroundColor: [
                    '#4e73df',
                    '#1cc88a',
                    '#36b9cc',
                    '#f6c23e',
                    '#e74a3b'
                ]
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
<?php $this->stop() ?>
