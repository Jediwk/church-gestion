<?php
use Core\View;
$title = 'Modifier l\'utilisateur';
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="card-title">Modifier l'utilisateur</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= View::url('/admin/users/' . $user['id']) ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                   id="username" name="username" 
                                   value="<?= $data['username'] ?? $user['username'] ?>" required>
                            <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?= $errors['username'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                   id="email" name="email" 
                                   value="<?= $data['email'] ?? $user['email'] ?>" required>
                            <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= $errors['email'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Nouveau mot de passe 
                                <small class="text-muted">(laisser vide pour ne pas modifier)</small>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                       id="password" name="password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= $errors['password'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Rôle</label>
                            <select class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>" 
                                    id="role" name="role" required 
                                    <?= $user['role'] === 'super_admin' ? 'disabled' : '' ?>>
                                <?php foreach ($roles as $role): ?>
                                <option value="<?= $role ?>" 
                                        <?= ($data['role'] ?? $user['role']) === $role ? 'selected' : '' ?>>
                                    <?= ucfirst($role) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['role'])): ?>
                            <div class="invalid-feedback"><?= $errors['role'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" name="status" 
                                    <?= $user['role'] === 'super_admin' ? 'disabled' : '' ?>>
                                <option value="1" <?= ($data['status'] ?? $user['status']) == 1 ? 'selected' : '' ?>>
                                    Actif
                                </option>
                                <option value="0" <?= ($data['status'] ?? $user['status']) == 0 ? 'selected' : '' ?>>
                                    Inactif
                                </option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                            <a href="<?= View::url('/admin/users') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
</script>
