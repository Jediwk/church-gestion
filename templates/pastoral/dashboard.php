<?php
use Core\View;
?>

<div class="container-fluid p-0">
    <!-- En-tête avec filtres -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Vue d'ensemble pastorale</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <select id="periodFilter" class="form-select">
                                <option value="month" selected>Ce mois</option>
                                <option value="quarter">Ce trimestre</option>
                                <option value="year">Cette année</option>
                                <option value="all">Global</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button id="updateStats" class="btn btn-primary">
                                <i class="fas fa-sync"></i> Mettre à jour
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques générales -->
    <div class="row">
        <!-- Total membres -->
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title">Total membres</h5>
                        </div>
                        <div class="col-auto">
                            <div class="stat text-primary">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3" id="totalMembers">0</h1>
                    <div class="mb-0">
                        <span class="badge badge-success-light" id="memberGrowth">
                            <i class="fas fa-arrow-up"></i> 0%
                        </span>
                        <span class="text-muted">Croissance</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Familles actives -->
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title">Familles actives</h5>
                        </div>
                        <div class="col-auto">
                            <div class="stat text-success">
                                <i class="fas fa-home"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3" id="totalFamilies">0</h1>
                    <div class="mb-0">
                        <span id="averageFamilySize" class="text-muted">
                            Moyenne : 0 membres/famille
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Présence moyenne -->
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title">Présence moyenne</h5>
                        </div>
                        <div class="col-auto">
                            <div class="stat text-info">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3" id="averageAttendance">0%</h1>
                    <div class="mb-0">
                        <span id="attendanceTrend" class="text-muted">
                            Sur les derniers événements
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Engagement -->
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title">Taux d'engagement</h5>
                        </div>
                        <div class="col-auto">
                            <div class="stat text-warning">
                                <i class="fas fa-hand-holding-heart"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3" id="engagementRate">0%</h1>
                    <div class="mb-0">
                        <span id="engagementDetails" class="text-muted">
                            Membres actifs/Total
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row">
        <!-- Évolution des membres -->
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Évolution des membres</h5>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="membershipChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques de présence -->
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Statistiques de présence</h5>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="attendanceChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analyses démographiques -->
    <div class="row">
        <!-- Répartition par âge -->
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Répartition par âge</h5>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="ageDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Répartition par genre -->
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Répartition par genre</h5>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="genderDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Répartition géographique -->
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Répartition géographique</h5>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="locationDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuration des couleurs
    const chartColors = {
        primary: 'rgba(28, 187, 140, 0.2)',
        success: 'rgba(40, 167, 69, 0.2)',
        info: 'rgba(23, 162, 184, 0.2)',
        warning: 'rgba(255, 193, 7, 0.2)',
        danger: 'rgba(220, 53, 69, 0.2)',
        borderPrimary: 'rgb(28, 187, 140)',
        borderSuccess: 'rgb(40, 167, 69)',
        borderInfo: 'rgb(23, 162, 184)',
        borderWarning: 'rgb(255, 193, 7)',
        borderDanger: 'rgb(220, 53, 69)'
    };

    // Initialisation des graphiques
    let membershipChart = new Chart(document.getElementById('membershipChart'), {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Nombre de membres',
                data: [],
                backgroundColor: chartColors.primary,
                borderColor: chartColors.borderPrimary,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    let attendanceChart = new Chart(document.getElementById('attendanceChart'), {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Taux de présence',
                data: [],
                backgroundColor: chartColors.info,
                borderColor: chartColors.borderInfo,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    let ageDistributionChart = new Chart(document.getElementById('ageDistributionChart'), {
        type: 'doughnut',
        data: {
            labels: ['0-17', '18-30', '31-50', '51-70', '70+'],
            datasets: [{
                data: [],
                backgroundColor: [
                    chartColors.primary,
                    chartColors.success,
                    chartColors.info,
                    chartColors.warning,
                    chartColors.danger
                ],
                borderColor: [
                    chartColors.borderPrimary,
                    chartColors.borderSuccess,
                    chartColors.borderInfo,
                    chartColors.borderWarning,
                    chartColors.borderDanger
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    let genderDistributionChart = new Chart(document.getElementById('genderDistributionChart'), {
        type: 'pie',
        data: {
            labels: ['Hommes', 'Femmes'],
            datasets: [{
                data: [],
                backgroundColor: [
                    chartColors.primary,
                    chartColors.info
                ],
                borderColor: [
                    chartColors.borderPrimary,
                    chartColors.borderInfo
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    let locationDistributionChart = new Chart(document.getElementById('locationDistributionChart'), {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Membres',
                data: [],
                backgroundColor: chartColors.success,
                borderColor: chartColors.borderSuccess,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y'
        }
    });

    // Gestionnaire de période
    $('#periodFilter').change(function() {
        updateDashboard($(this).val());
    });

    $('#updateStats').click(function() {
        updateDashboard($('#periodFilter').val());
    });

    // Fonction de mise à jour du dashboard
    function updateDashboard(period) {
        $.get('<?= View::url('/pastoral/dashboard-data') ?>', { period: period }, function(response) {
            if (response.success) {
                updateKPIs(response.data.kpis);
                updateCharts(response.data.charts);
            } else {
                toastr.error('Erreur lors de la mise à jour des données');
            }
        });
    }

    // Mise à jour des KPIs
    function updateKPIs(data) {
        $('#totalMembers').text(data.total_members);
        $('#memberGrowth').html(`<i class="fas fa-arrow-${data.member_growth >= 0 ? 'up' : 'down'}"></i> ${data.member_growth}%`);
        $('#totalFamilies').text(data.total_families);
        $('#averageFamilySize').text(`Moyenne : ${data.average_family_size} membres/famille`);
        $('#averageAttendance').text(data.average_attendance + '%');
        $('#attendanceTrend').text(data.attendance_trend);
        $('#engagementRate').text(data.engagement_rate + '%');
        $('#engagementDetails').text(`${data.active_members}/${data.total_members} membres`);
    }

    // Mise à jour des graphiques
    function updateCharts(data) {
        // Évolution des membres
        membershipChart.data.labels = data.membership.labels;
        membershipChart.data.datasets[0].data = data.membership.data;
        membershipChart.update();

        // Statistiques de présence
        attendanceChart.data.labels = data.attendance.labels;
        attendanceChart.data.datasets[0].data = data.attendance.data;
        attendanceChart.update();

        // Distribution par âge
        ageDistributionChart.data.datasets[0].data = data.age_distribution;
        ageDistributionChart.update();

        // Distribution par genre
        genderDistributionChart.data.datasets[0].data = data.gender_distribution;
        genderDistributionChart.update();

        // Distribution géographique
        locationDistributionChart.data.labels = data.location_distribution.labels;
        locationDistributionChart.data.datasets[0].data = data.location_distribution.data;
        locationDistributionChart.update();
    }

    // Initialisation du dashboard
    updateDashboard('month');
});
</script>
