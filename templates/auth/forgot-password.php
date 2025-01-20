<?php
use Core\View;
$title = 'Mot de passe oublié';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Mot de passe oublié</h2>
                
                <form method="POST" action="<?= View::url('/forgot-password') ?>">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                    <div class="mb-3">
                        <label for="username" class="form-label">Nom d'utilisateur</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               required autofocus autocomplete="username">
                        <div class="form-text">
                            Entrez votre nom d'utilisateur et nous vous enverrons un lien pour réinitialiser votre mot de passe.
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Envoyer le lien
                        </button>
                        <a href="<?= View::url('/login') ?>" class="btn btn-link">
                            Retour à la connexion
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
