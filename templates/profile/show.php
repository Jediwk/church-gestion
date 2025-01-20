<?php $this->layout('layouts/app', ['title' => $title]) ?>

<?php $this->start('main') ?>
<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $title ?></h1>
    
    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-1"></i>
                    Informations du profil
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?= $_SESSION['success'] ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['error'] ?>
                        </div>
                    <?php endif; ?>

                    <form action="/profile/update" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Rôle</label>
                            <input type="text" class="form-control" id="role" value="<?= htmlspecialchars($role['role_description']) ?>" readonly>
                        </div>

                        <hr>
                        <h5>Changer le mot de passe</h5>
                        <p class="text-muted">Laissez vide si vous ne souhaitez pas changer votre mot de passe</p>

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mot de passe actuel</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                            <div class="form-text">Le mot de passe doit contenir au moins 6 caractères</div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>

                        <button type="submit" class="btn btn-primary">Mettre à jour le profil</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-shield-alt me-1"></i>
                    Sécurité
                </div>
                <div class="card-body">
                    <p><strong>Dernière connexion :</strong><br>
                    <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais' ?></p>

                    <p><strong>Statut du compte :</strong><br>
                    <?php if ($user['status']): ?>
                        <span class="badge bg-success">Actif</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Inactif</span>
                    <?php endif; ?></p>

                    <hr>
                    <div class="small">
                        <p class="mb-0"><i class="fas fa-info-circle me-1"></i> Pour votre sécurité :</p>
                        <ul class="ps-4">
                            <li>Changez régulièrement votre mot de passe</li>
                            <li>Utilisez un mot de passe fort et unique</li>
                            <li>Ne partagez jamais vos identifiants</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->stop() ?>
