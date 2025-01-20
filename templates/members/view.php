<?php
use Core\View;
use Core\Session;
?>

<div class="container-fluid p-0">
    <div class="row mb-3">
        <div class="col-auto">
            <h1 class="h3 d-inline align-middle">Profil du membre</h1>
        </div>
        <div class="col-auto ms-auto">
            <div class="btn-group">
                <a href="<?= View::url('/members') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <a href="<?= View::url('/members/edit/' . $member['id']) ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Modifier
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations principales -->
        <div class="col-md-4 col-xl-3">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Profil</h5>
                </div>
                <div class="card-body text-center">
                    <img src="<?= $member['photo'] ? View::url('/uploads/members/' . $member['photo']) : View::url('/assets/img/default-avatar.png') ?>" 
                         class="img-fluid rounded-circle mb-2" width="128" height="128"
                         alt="<?= View::escape($member['first_name']) ?> <?= View::escape($member['last_name']) ?>">
                    
                    <h5 class="card-title mb-0"><?= View::escape($member['first_name']) ?> <?= View::escape($member['last_name']) ?></h5>
                    
                    <div class="text-muted mb-2">
                        <?php if ($member['family_name']): ?>
                            <i class="fas fa-home me-1"></i> <?= View::escape($member['family_name']) ?>
                        <?php endif; ?>
                    </div>

                    <div>
                        <span class="badge <?= $member['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $member['status'] === 'active' ? 'Actif' : 'Inactif' ?>
                        </span>
                    </div>
                </div>
                <hr class="my-0">
                <div class="card-body">
                    <h5 class="h6 card-title">Contact</h5>
                    <?php if ($member['email']): ?>
                        <p class="mb-1">
                            <i class="fas fa-envelope text-muted me-2"></i>
                            <a href="mailto:<?= View::escape($member['email']) ?>" class="text-decoration-none">
                                <?= View::escape($member['email']) ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    
                    <?php if ($member['phone']): ?>
                        <p class="mb-1">
                            <i class="fas fa-phone text-muted me-2"></i>
                            <a href="tel:<?= View::escape($member['phone']) ?>" class="text-decoration-none">
                                <?= View::escape($member['phone']) ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    
                    <?php if ($member['address']): ?>
                        <p class="mb-1">
                            <i class="fas fa-map-marker-alt text-muted me-2"></i>
                            <?= View::escape($member['address']) ?><br>
                            <?php if ($member['postal_code'] || $member['city']): ?>
                                <span class="ms-4">
                                    <?= View::escape($member['postal_code']) ?> <?= View::escape($member['city']) ?>
                                </span>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
                <hr class="my-0">
                <div class="card-body">
                    <h5 class="h6 card-title">Informations</h5>
                    <?php if ($member['birth_date']): ?>
                        <p class="mb-1">
                            <i class="fas fa-birthday-cake text-muted me-2"></i>
                            <?= date('d/m/Y', strtotime($member['birth_date'])) ?>
                            <small class="text-muted">(<?= date_diff(date_create($member['birth_date']), date_create())->y ?> ans)</small>
                        </p>
                    <?php endif; ?>
                    
                    <?php if ($member['gender']): ?>
                        <p class="mb-1">
                            <i class="fas <?= $member['gender'] === 'M' ? 'fa-mars' : 'fa-venus' ?> text-muted me-2"></i>
                            <?= $member['gender'] === 'M' ? 'Masculin' : 'Féminin' ?>
                        </p>
                    <?php endif; ?>
                    
                    <p class="mb-1">
                        <i class="fas fa-calendar-alt text-muted me-2"></i>
                        Membre depuis le <?= date('d/m/Y', strtotime($member['join_date'])) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Onglets d'informations détaillées -->
        <div class="col-md-8 col-xl-9">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#donations" role="tab">
                                <i class="fas fa-hand-holding-heart me-1"></i>
                                Dons
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#events" role="tab">
                                <i class="fas fa-calendar-check me-1"></i>
                                Événements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#notes" role="tab">
                                <i class="fas fa-sticky-note me-1"></i>
                                Notes
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Dons -->
                        <div class="tab-pane fade show active" id="donations" role="tabpanel">
                            <?php if (empty($donations)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-hand-holding-heart fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucun don enregistré</p>
                                    <?php if (Session::hasPermission('donations.create')): ?>
                                        <a href="<?= View::url('/donations/create?member_id=' . $member['id']) ?>" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Enregistrer un don
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Montant</th>
                                                <th>Catégorie</th>
                                                <th>Méthode</th>
                                                <th>Référence</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($donations as $donation): ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($donation['transaction_date'])) ?></td>
                                                <td class="text-end"><?= number_format($donation['amount'], 2, ',', ' ') ?> €</td>
                                                <td><?= View::escape($donation['category_name']) ?></td>
                                                <td>
                                                    <?php
                                                    $methods = [
                                                        'cash' => 'Espèces',
                                                        'check' => 'Chèque',
                                                        'transfer' => 'Virement',
                                                        'other' => 'Autre'
                                                    ];
                                                    echo $methods[$donation['payment_method']] ?? $donation['payment_method'];
                                                    ?>
                                                </td>
                                                <td><?= View::escape($donation['reference_number']) ?: '-' ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <?php if (Session::hasPermission('donations.edit')): ?>
                                                        <a href="<?= View::url('/donations/edit/' . $donation['id']) ?>" 
                                                           class="btn btn-sm btn-info"
                                                           data-bs-toggle="tooltip"
                                                           title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (Session::hasPermission('donations.receipt')): ?>
                                                        <a href="<?= View::url('/donations/receipt/' . $donation['id']) ?>" 
                                                           class="btn btn-sm btn-secondary"
                                                           data-bs-toggle="tooltip"
                                                           title="Reçu fiscal"
                                                           target="_blank">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="fw-bold">
                                                <td>Total</td>
                                                <td class="text-end"><?= number_format($total_donations, 2, ',', ' ') ?> €</td>
                                                <td colspan="4"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Événements -->
                        <div class="tab-pane fade" id="events" role="tabpanel">
                            <?php if (empty($events)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucun événement enregistré</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Événement</th>
                                                <th>Type</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($events as $event): ?>
                                            <tr>
                                                <td><?= date('d/m/Y H:i', strtotime($event['start_date'])) ?></td>
                                                <td><?= View::escape($event['title']) ?></td>
                                                <td><?= View::escape($event['type']) ?></td>
                                                <td>
                                                    <?php
                                                    $status_badges = [
                                                        'pending' => 'bg-warning',
                                                        'confirmed' => 'bg-success',
                                                        'cancelled' => 'bg-danger'
                                                    ];
                                                    $status_labels = [
                                                        'pending' => 'En attente',
                                                        'confirmed' => 'Confirmé',
                                                        'cancelled' => 'Annulé'
                                                    ];
                                                    ?>
                                                    <span class="badge <?= $status_badges[$event['status']] ?? 'bg-secondary' ?>">
                                                        <?= $status_labels[$event['status']] ?? $event['status'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="<?= View::url('/events/view/' . $event['id']) ?>" 
                                                       class="btn btn-sm btn-info"
                                                       data-bs-toggle="tooltip"
                                                       title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Notes -->
                        <div class="tab-pane fade" id="notes" role="tabpanel">
                            <?php if (empty($member['notes'])): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-sticky-note fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucune note</p>
                                    <?php if (Session::hasPermission('members.edit')): ?>
                                        <a href="<?= View::url('/members/edit/' . $member['id']) ?>" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Ajouter une note
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <?= nl2br(View::escape($member['notes'])) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
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
