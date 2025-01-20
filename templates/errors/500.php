<?php 
$this->layout('layouts/app', ['title' => 'Erreur serveur']) 
?>

<?php $this->start('main') ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <h1 class="display-1">500</h1>
            <h2>Erreur serveur</h2>
            <p class="lead">Une erreur inattendue s'est produite. Veuillez réessayer plus tard.</p>
            <?php if (isset($error) && !empty($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <a href="/" class="btn btn-primary">Retour à l'accueil</a>
        </div>
    </div>
</div>
<?php $this->stop() ?>
