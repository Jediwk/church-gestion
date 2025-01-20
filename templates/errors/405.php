<?php $this->layout('layouts/app', ['title' => 'Méthode non autorisée']) ?>

<?php $this->start('main') ?>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <h1 class="display-1 text-danger">405</h1>
                    <h2 class="mb-4">Méthode non autorisée</h2>
                    <p class="text-muted mb-4">La méthode de requête utilisée n'est pas autorisée pour cette ressource.</p>
                    <a href="/" class="btn btn-primary">Retour à l'accueil</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->stop() ?>
