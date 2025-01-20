<?php 
use App\Core\Auth;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Gestion d'église</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
        }
        .sidebar .nav-link {
            color: #fff;
            padding: 0.5rem 1rem;
        }
        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        .main-content {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .nav-treeview {
            margin-left: 1rem;
            list-style: none;
            padding-left: 0;
        }
        .nav-treeview .nav-link {
            padding-left: 2rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 position-fixed sidebar">
                <div class="p-3 text-white">
                    <h4>Gestion d'église</h4>
                </div>
                <nav class="mt-2">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard">
                                <i class="fas fa-home me-2"></i> Tableau de bord
                            </a>
                        </li>

                        <?php if (Auth::hasPermission('*')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/users">
                                <i class="fas fa-users-cog me-2"></i> Utilisateurs
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if (Auth::hasPermission('manage_finances')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/finances">
                                <i class="fas fa-money-bill me-2"></i> Finances
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a class="nav-link" href="/finances/transactions">
                                        <i class="fas fa-list me-2"></i> Transactions
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/finances/reports">
                                        <i class="fas fa-chart-bar me-2"></i> Rapports
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/finances/types">
                                        <i class="fas fa-tags me-2"></i> Types
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <?php if (Auth::hasPermission('manage_members')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/members">
                                <i class="fas fa-users me-2"></i> Membres
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if (Auth::hasPermission('manage_families')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/families">
                                <i class="fas fa-home me-2"></i> Familles
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>

            <!-- Main content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-4 main-content">
                <!-- Top navigation -->
                <nav class="navbar navbar-expand-lg navbar-light bg-light">
                    <div class="container-fluid">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav ms-auto">
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-user me-2"></i><?= Auth::user()['email'] ?? 'Utilisateur' ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="/profile"><i class="fas fa-user-circle me-2"></i>Profil</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="/logout"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>

                <!-- Flash messages -->
                <?php if (isset($_SESSION['flash'])): ?>
                    <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                        <div class="alert alert-<?= $type ?> alert-dismissible fade show mt-3">
                            <?= $message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endforeach; ?>
                    <?php unset($_SESSION['flash']); ?>
                <?php endif; ?>

                <!-- Main content -->
                <?= $this->section('main') ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom scripts -->
    <?= $this->section('scripts') ?>
</body>
</html>
