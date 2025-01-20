<?php
use Core\View;
?>

<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Dashboard Financier</h1>

    <div class="row">
        <!-- Résumé du mois -->
        <div class="col-xl-6 col-xxl-5 d-flex">
            <div class="w-100">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col mt-0">
                                        <h5 class="card-title">Revenus du mois</h5>
                                    </div>
                                    <div class="col-auto">
                                        <div class="stat text-primary">
                                            <i class="fas fa-euro-sign"></i>
                                        </div>
                                    </div>
                                </div>
                                <h1 class="mt-1 mb-3"><?= number_format($stats['current_month']['income'], 2, ',', ' ') ?> €</h1>
                                <div class="mb-0">
                                    <span class="badge bg-success"> <i class="fas fa-arrow-up"></i> </span>
                                    <span class="text-muted">Depuis le mois dernier</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col mt-0">
                                        <h5 class="card-title">Dépenses du mois</h5>
                                    </div>
                                    <div class="col-auto">
                                        <div class="stat text-primary">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                    </div>
                                </div>
                                <h1 class="mt-1 mb-3"><?= number_format($stats['current_month']['expenses'], 2, ',', ' ') ?> €</h1>
                                <div class="mb-0">
                                    <span class="badge bg-danger"> <i class="fas fa-arrow-down"></i> </span>
                                    <span class="text-muted">Depuis le mois dernier</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balance -->
        <div class="col-xl-6 col-xxl-7">
            <div class="card flex-fill w-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Balance Mensuelle</h5>
                </div>
                <div class="card-body py-3">
                    <div class="chart chart-sm">
                        <canvas id="chartjs-dashboard-line"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Donateurs -->
        <div class="col-12 col-md-6 col-xxl-3 d-flex order-2 order-xxl-3">
            <div class="card flex-fill w-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Donateurs du Mois</h5>
                </div>
                <div class="card-body d-flex">
                    <div class="align-self-center w-100">
                        <div class="py-3">
                            <div class="chart chart-xs">
                                <canvas id="chartjs-dashboard-pie"></canvas>
                            </div>
                        </div>
                        <table class="table mb-0">
                            <tbody>
                                <?php foreach ($stats['top_donors'] as $donor): ?>
                                <tr>
                                    <td><?= View::escape($donor['first_name']) ?> <?= View::escape($donor['last_name']) ?></td>
                                    <td class="text-end"><?= number_format($donor['total'], 2, ',', ' ') ?> €</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Évolution Annuelle -->
        <div class="col-12 col-md-12 col-xxl-6 d-flex order-3 order-xxl-2">
            <div class="card flex-fill w-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Évolution Annuelle</h5>
                </div>
                <div class="card-body px-4">
                    <div id="world_map" style="height:350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-8 col-xxl-9 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <h5 class="card-title mb-0">Dernières Transactions</h5>
                </div>
                <table class="table table-hover my-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Catégorie</th>
                            <th>Montant</th>
                            <th>Membre</th>
                        </tr>
                    </thead>
                    <tbody>
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
                            <td><?= number_format($transaction['amount'], 2, ',', ' ') ?> €</td>
                            <td><?= View::escape($transaction['member_name']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Graphique d'évolution mensuelle
    new Chart(document.getElementById("chartjs-dashboard-line"), {
        type: "line",
        data: {
            labels: <?= json_encode(array_column($stats['yearly_trends'], 'month')) ?>,
            datasets: [{
                label: "Revenus",
                fill: true,
                backgroundColor: "rgba(75, 192, 192, 0.2)",
                borderColor: "rgba(75, 192, 192, 1)",
                data: <?= json_encode(array_column($stats['yearly_trends'], 'income')) ?>
            }, {
                label: "Dépenses",
                fill: true,
                backgroundColor: "rgba(255, 99, 132, 0.2)",
                borderColor: "rgba(255, 99, 132, 1)",
                data: <?= json_encode(array_column($stats['yearly_trends'], 'expenses')) ?>
            }]
        },
        options: {
            maintainAspectRatio: false,
            legend: {
                display: true
            },
            tooltips: {
                intersect: false
            },
            hover: {
                intersect: true
            },
            plugins: {
                filler: {
                    propagate: false
                }
            },
            scales: {
                xAxes: [{
                    reverse: true,
                    gridLines: {
                        color: "rgba(0,0,0,0.05)"
                    }
                }],
                yAxes: [{
                    ticks: {
                        stepSize: 500
                    },
                    display: true,
                    borderDash: [5, 5],
                    gridLines: {
                        color: "rgba(0,0,0,0)",
                        fontColor: "#fff"
                    }
                }]
            }
        }
    });

    // Graphique des top donateurs
    new Chart(document.getElementById("chartjs-dashboard-pie"), {
        type: "pie",
        data: {
            labels: <?= json_encode(array_map(function($donor) {
                return $donor['first_name'] . ' ' . $donor['last_name'];
            }, $stats['top_donors'])) ?>,
            datasets: [{
                data: <?= json_encode(array_column($stats['top_donors'], 'total')) ?>,
                backgroundColor: [
                    "rgba(75, 192, 192, 0.8)",
                    "rgba(54, 162, 235, 0.8)",
                    "rgba(255, 206, 86, 0.8)",
                    "rgba(153, 102, 255, 0.8)",
                    "rgba(255, 159, 64, 0.8)"
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                display: false
            }
        }
    });
});
</script>
