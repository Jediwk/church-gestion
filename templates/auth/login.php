<?php $this->layout('layouts/auth', ['title' => $title]) ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mt-5">
                <div class="card-header">
                    <h3 class="text-center">Connexion</h3>
                </div>
                <div class="card-body">
                    <?php if ($flash = \App\Core\Session::getFlash()): ?>
                        <?php foreach ($flash as $type => $message): ?>
                            <div class="alert alert-<?= $type ?>">
                                <?= $message ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <form action="/login/submit" method="post">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Se connecter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
