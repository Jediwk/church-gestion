<?php $this->layout('layouts/app', ['title' => $title]) ?>

<?php $this->start('main') ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Gestion des utilisateurs</h2>
                        <?php if ($this->hasPermission('*')): ?>
                        <a href="/users/create" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Nouvel utilisateur
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="users-table">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                    <th>Dernière connexion</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-primary rounded-circle me-2">
                                                <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                            </div>
                                            <?= htmlspecialchars($user['username']) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <?php 
                                        $roleClass = match($user['role_name']) {
                                            'super_admin' => 'bg-danger',
                                            'pastor' => 'bg-primary',
                                            'treasurer' => 'bg-success',
                                            'secretary' => 'bg-info',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $roleClass ?>">
                                            <?= htmlspecialchars(ucfirst($user['role_name'] ?? 'Aucun rôle')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['status']): ?>
                                            <span class="badge bg-success">Actif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['last_login']): ?>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                <?= date('d/m/Y H:i', strtotime($user['last_login'])) ?>
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">Jamais connecté</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($this->hasPermission('*')): ?>
                                        <div class="btn-group">
                                            <a href="/users/edit/<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['id'] != $_SESSION['user']['id']): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete(<?= $user['id'] ?>)" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
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

<!-- Formulaire de suppression caché -->
<form id="delete-form" method="POST" style="display: none;">
    <input type="hidden" name="_method" value="DELETE">
</form>

<script>
$(document).ready(function() {
    $('#users-table').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
        },
        order: [[0, 'asc']],
        columnDefs: [
            {
                targets: -1,
                orderable: false,
                searchable: false
            }
        ]
    });
});

function confirmDelete(userId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
        fetch(`/users/delete/${userId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Une erreur est survenue');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Une erreur est survenue');
        });
    }
}
</script>

<style>
.avatar {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}

.table td {
    vertical-align: middle;
}
</style>
<?php $this->stop() ?>
