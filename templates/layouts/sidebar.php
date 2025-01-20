<?php
$current_page = $_SERVER['REQUEST_URI'];
?>

<nav id="sidebar" class="sidebar bg-dark text-light">
    <div class="sidebar-header">
        <h3 class="text-center">Gestion d'Église</h3>
    </div>

    <ul class="nav flex-column">
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="/" class="nav-link <?= $current_page === '/' ? 'active' : '' ?>">
                <i class="fas fa-home"></i>
                <span>Tableau de bord</span>
            </a>
        </li>

        <!-- Finances -->
        <?php if (Auth::hasPermission('manage_finances')): ?>
            <li class="nav-item">
                <a href="#financeSubmenu" data-bs-toggle="collapse" class="nav-link">
                    <i class="fas fa-money-bill"></i>
                    <span>Finances</span>
                    <i class="fas fa-angle-down float-end"></i>
                </a>
                <ul class="collapse <?= str_starts_with($current_page, '/finances') ? 'show' : '' ?>" id="financeSubmenu">
                    <li class="nav-item">
                        <a href="/finances" class="nav-link <?= $current_page === '/finances' ? 'active' : '' ?>">
                            <i class="fas fa-list"></i>
                            <span>Transactions</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/finances/reports" class="nav-link <?= $current_page === '/finances/reports' ? 'active' : '' ?>">
                            <i class="fas fa-chart-bar"></i>
                            <span>Rapports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/finances/types" class="nav-link <?= $current_page === '/finances/types' ? 'active' : '' ?>">
                            <i class="fas fa-tags"></i>
                            <span>Types</span>
                        </a>
                    </li>
                </ul>
            </li>
        <?php endif; ?>

        <!-- Membres -->
        <?php if (Auth::hasPermission('manage_members')): ?>
            <li class="nav-item">
                <a href="#memberSubmenu" data-bs-toggle="collapse" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Membres</span>
                    <i class="fas fa-angle-down float-end"></i>
                </a>
                <ul class="collapse <?= str_starts_with($current_page, '/members') ? 'show' : '' ?>" id="memberSubmenu">
                    <li class="nav-item">
                        <a href="/members" class="nav-link <?= $current_page === '/members' ? 'active' : '' ?>">
                            <i class="fas fa-list"></i>
                            <span>Liste</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/members/create" class="nav-link <?= $current_page === '/members/create' ? 'active' : '' ?>">
                            <i class="fas fa-user-plus"></i>
                            <span>Nouveau membre</span>
                        </a>
                    </li>
                </ul>
            </li>
        <?php endif; ?>

        <!-- Familles -->
        <?php if (Auth::hasPermission('manage_families')): ?>
            <li class="nav-item">
                <a href="#familySubmenu" data-bs-toggle="collapse" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Familles</span>
                    <i class="fas fa-angle-down float-end"></i>
                </a>
                <ul class="collapse <?= str_starts_with($current_page, '/families') ? 'show' : '' ?>" id="familySubmenu">
                    <li class="nav-item">
                        <a href="/families" class="nav-link <?= $current_page === '/families' ? 'active' : '' ?>">
                            <i class="fas fa-list"></i>
                            <span>Liste</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/families/create" class="nav-link <?= $current_page === '/families/create' ? 'active' : '' ?>">
                            <i class="fas fa-plus"></i>
                            <span>Nouvelle famille</span>
                        </a>
                    </li>
                </ul>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<style>
.sidebar {
    min-width: 250px;
    max-width: 250px;
    min-height: 100vh;
    transition: all 0.3s;
}

.sidebar.active {
    margin-left: -250px;
}

.sidebar .nav-link {
    color: rgba(255, 255, 255, 0.8);
    padding: 10px 20px;
    transition: all 0.3s;
}

.sidebar .nav-link:hover,
.sidebar .nav-link.active {
    color: #fff;
    background: rgba(255, 255, 255, 0.1);
}

.sidebar .nav-link i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

.sidebar-header {
    padding: 20px;
    background: rgba(0, 0, 0, 0.2);
}

.sidebar ul ul .nav-link {
    padding-left: 40px;
    font-size: 0.9em;
}

.sidebar ul ul {
    background: rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .sidebar {
        margin-left: -250px;
    }
    
    .sidebar.active {
        margin-left: 0;
    }
}
</style>
