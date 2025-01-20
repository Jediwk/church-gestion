<?php $this->layout('layouts/app', ['title' => $title]) ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Détails de la famille <?= htmlspecialchars($family['name']) ?></h2>
                    <a href="/families" class="btn btn-secondary">Retour</a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4>Informations</h4>
                            <dl class="row">
                                <dt class="col-sm-4">Adresse</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($family['address'] ?? 'Non renseignée') ?></dd>

                                <dt class="col-sm-4">Téléphone</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($family['phone'] ?? 'Non renseigné') ?></dd>

                                <dt class="col-sm-4">Nombre de membres</dt>
                                <dd class="col-sm-8"><?= $family['member_count'] ?></dd>
                            </dl>
                        </div>
                    </div>

                    <h4>Membres de la famille</h4>
                    <?php if (empty($members)): ?>
                        <div class="alert alert-info">
                            Aucun membre dans cette famille.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Relation</th>
                                        <th>Téléphone</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($members as $member): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($member['last_name']) ?></td>
                                            <td><?= htmlspecialchars($member['first_name']) ?></td>
                                            <td><?= htmlspecialchars($member['relationship'] ?? 'Non spécifiée') ?></td>
                                            <td><?= htmlspecialchars($member['phone'] ?? 'Non renseigné') ?></td>
                                            <td><?= htmlspecialchars($member['email'] ?? 'Non renseigné') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <a href="/families/edit/<?= $family['id'] ?>" class="btn btn-primary">Modifier la famille</a>
                        <form action="/families/delete/<?= $family['id'] ?>" method="post" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette famille ?');">
                            <button type="submit" class="btn btn-danger">Supprimer la famille</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
