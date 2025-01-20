<?php
use Core\View;
$title = 'Import/Export';
?>

<div class="container-fluid">
    <h1 class="h3 mb-4">Import/Export des données</h1>

    <div class="row">
        <!-- Export des membres -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Export des membres</h5>
                </div>
                <div class="card-body">
                    <p>Exportez la liste complète des membres dans différents formats.</p>
                    <div class="btn-group">
                        <a href="<?= View::url('/import-export/members/export?format=csv') ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-file-csv"></i> CSV
                        </a>
                        <a href="<?= View::url('/import-export/members/export?format=excel') ?>" 
                           class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Excel
                        </a>
                        <a href="<?= View::url('/import-export/members/export?format=pdf') ?>" 
                           class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export des familles -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Export des familles</h5>
                </div>
                <div class="card-body">
                    <p>Exportez la liste complète des familles dans différents formats.</p>
                    <div class="btn-group">
                        <a href="<?= View::url('/import-export/families/export?format=csv') ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-file-csv"></i> CSV
                        </a>
                        <a href="<?= View::url('/import-export/families/export?format=excel') ?>" 
                           class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Excel
                        </a>
                        <a href="<?= View::url('/import-export/families/export?format=pdf') ?>" 
                           class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import des membres -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Import des membres</h5>
                </div>
                <div class="card-body">
                    <p>Importez une liste de membres depuis un fichier CSV ou Excel.</p>
                    <form action="<?= View::url('/import-export/members/import') ?>" 
                          method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="memberFile" class="form-label">Fichier (CSV ou XLSX)</label>
                            <input type="file" class="form-control" id="memberFile" name="file" 
                                   accept=".csv,.xlsx" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Importer
                        </button>
                    </form>
                </div>
                <div class="card-footer">
                    <h6>Format requis :</h6>
                    <ul class="mb-0">
                        <li>Nom (requis)</li>
                        <li>Prénom (requis)</li>
                        <li>Email</li>
                        <li>Téléphone (requis)</li>
                        <li>Date de naissance (YYYY-MM-DD)</li>
                        <li>Genre (M/F)</li>
                        <li>Statut matrimonial (Célibataire/Marié(e)/Divorcé(e)/Veuf/Veuve)</li>
                        <li>Adresse</li>
                        <li>Date d'adhésion (YYYY-MM-DD)</li>
                        <li>Notes</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Import des familles -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Import des familles</h5>
                </div>
                <div class="card-body">
                    <p>Importez une liste de familles depuis un fichier CSV ou Excel.</p>
                    <form action="<?= View::url('/import-export/families/import') ?>" 
                          method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="familyFile" class="form-label">Fichier (CSV ou XLSX)</label>
                            <input type="file" class="form-control" id="familyFile" name="file" 
                                   accept=".csv,.xlsx" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Importer
                        </button>
                    </form>
                </div>
                <div class="card-footer">
                    <h6>Format requis :</h6>
                    <ul class="mb-0">
                        <li>Nom (requis)</li>
                        <li>Téléphone (requis)</li>
                        <li>Email</li>
                        <li>Adresse</li>
                        <li>Date de mariage (YYYY-MM-DD)</li>
                        <li>Notes</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Modèles de fichiers -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Modèles de fichiers</h5>
                </div>
                <div class="card-body">
                    <p>Téléchargez nos modèles de fichiers pour l'import :</p>
                    <div class="btn-group">
                        <a href="<?= View::url('/assets/templates/members_template.xlsx') ?>" 
                           class="btn btn-outline-success">
                            <i class="fas fa-download"></i> Modèle Membres (Excel)
                        </a>
                        <a href="<?= View::url('/assets/templates/members_template.csv') ?>" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-download"></i> Modèle Membres (CSV)
                        </a>
                        <a href="<?= View::url('/assets/templates/families_template.xlsx') ?>" 
                           class="btn btn-outline-success">
                            <i class="fas fa-download"></i> Modèle Familles (Excel)
                        </a>
                        <a href="<?= View::url('/assets/templates/families_template.csv') ?>" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-download"></i> Modèle Familles (CSV)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
