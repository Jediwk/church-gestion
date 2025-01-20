<?php 
use App\Core\Auth;
$this->layout('layouts/app', ['title' => $title]) 
?>

<?php $this->start('main') ?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total des membres</h5>
                    <h3><?= $stats['total_members'] ?? 0 ?></h3>
                    <div class="row">
                        <div class="col">
                            <small class="text-muted">
                                Hommes: <?= $stats['male_count'] ?? 0 ?>
                            </small>
                        </div>
                        <div class="col">
                            <small class="text-muted">
                                Femmes: <?= $stats['female_count'] ?? 0 ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des membres</h1>
        <a href="/members/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouveau membre
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($members)): ?>
                <div class="alert alert-info">
                    Aucun membre enregistré pour le moment.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Genre</th>
                                <th>Contact</th>
                                <th>Famille</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                                    </td>
                                    <td><?= $member['gender'] === 'M' ? 'Homme' : 'Femme' ?></td>
                                    <td>
                                        <?php if ($member['phone']): ?>
                                            <i class="fas fa-phone me-1"></i> <?= htmlspecialchars($member['phone']) ?><br>
                                        <?php endif; ?>
                                        <?php if ($member['email']): ?>
                                            <i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($member['email']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $member['family_name'] ? htmlspecialchars($member['family_name']) : '<span class="text-muted">Aucune</span>' ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/members/edit/<?= $member['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="if(confirm('Êtes-vous sûr de vouloir supprimer ce membre ?')) document.getElementById('delete-form-<?= $member['id'] ?>').submit()">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <form id="delete-form-<?= $member['id'] ?>" action="/members/delete/<?= $member['id'] ?>" method="POST" class="d-none">
                                        </form>
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
<?php $this->stop() ?>
