<?php $this->layout('layouts/app', ['title' => $title]) ?>

<?php $this->start('main') ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">Liste des familles</h2>
                            <p class="text-muted mb-0">
                                Total : <?= $stats['total_families'] ?> famille<?= $stats['total_families'] > 1 ? 's' : '' ?> 
                                (<?= $stats['active_families'] ?> active<?= $stats['active_families'] > 1 ? 's' : '' ?>)
                            </p>
                        </div>
                        <a href="/families/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Ajouter une famille
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Navigation -->
                    <ul class="nav nav-pills mb-4" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active d-flex align-items-center" id="table-tab" data-bs-toggle="tab" data-bs-target="#table-view" type="button" role="tab">
                                <i class="fas fa-table me-2"></i>Vue tableau
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center" id="tree-tab" data-bs-toggle="tab" data-bs-target="#tree-view" type="button" role="tab">
                                <i class="fas fa-sitemap me-2"></i>Vue arborescente
                            </button>
                        </li>
                    </ul>

                    <!-- Contenu des onglets -->
                    <div class="tab-content">
                        <!-- Vue tableau -->
                        <div class="tab-pane fade show active" id="table-view" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover" id="families-table">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Adresse</th>
                                            <th>Contact</th>
                                            <th>Membres</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($families as $family): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-house-user text-primary me-2"></i>
                                                    <?= htmlspecialchars($family['name']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($family['address']): ?>
                                                    <i class="fas fa-map-marker-alt text-muted me-1"></i>
                                                    <?= htmlspecialchars($family['address']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($family['phone'] || $family['email']): ?>
                                                    <?php if ($family['phone']): ?>
                                                        <div><i class="fas fa-phone text-muted me-1"></i> <?= htmlspecialchars($family['phone']) ?></div>
                                                    <?php endif; ?>
                                                    <?php if ($family['email']): ?>
                                                        <div><i class="fas fa-envelope text-muted me-1"></i> <?= htmlspecialchars($family['email']) ?></div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $memberCount = $family['member_count'];
                                                $maleCount = isset($family['members']) ? count(array_filter($family['members'], fn($m) => $m['gender'] === 'M')) : 0;
                                                $femaleCount = isset($family['members']) ? count(array_filter($family['members'], fn($m) => $m['gender'] === 'F')) : 0;
                                                ?>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-primary me-2">
                                                        <?= $memberCount ?> membre<?= $memberCount > 1 ? 's' : '' ?>
                                                    </span>
                                                    <?php if ($memberCount > 0): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-male text-primary"></i> <?= $maleCount ?>
                                                            <i class="fas fa-female text-danger ms-1"></i> <?= $femaleCount ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <a href="/families/edit/<?= $family['id'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmDelete(<?= $family['id'] ?>)" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Vue arborescente -->
                        <div class="tab-pane fade" id="tree-view" role="tabpanel">
                            <div class="tree-controls mb-3">
                                <div class="btn-group">
                                    <button class="btn btn-outline-primary" onclick="expandAll()">
                                        <i class="fas fa-expand-arrows-alt me-2"></i>Tout déplier
                                    </button>
                                    <button class="btn btn-outline-primary" onclick="collapseAll()">
                                        <i class="fas fa-compress-arrows-alt me-2"></i>Tout replier
                                    </button>
                                </div>
                                <div class="btn-group ms-2">
                                    <button class="btn btn-outline-secondary" onclick="zoomIn()" title="Zoom avant">
                                        <i class="fas fa-search-plus"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="zoomOut()" title="Zoom arrière">
                                        <i class="fas fa-search-minus"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="resetZoom()" title="Réinitialiser le zoom">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="tree-container">
                                <div class="chart" id="family-tree"></div>
                            </div>
                        </div>
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

<?php $this->stop() ?>

<?php $this->start('scripts') ?>
<script>
let treeInstance = null;
let currentZoom = 1;

$(document).ready(function() {
    // Initialisation de DataTables
    $('#families-table').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
        },
        responsive: true,
        order: [[0, 'asc']],
        columnDefs: [
            {
                targets: -1,
                orderable: false,
                searchable: false
            }
        ]
    });

    // Chargement de l'arbre familial lors du changement d'onglet
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.getAttribute('data-bs-target') === '#tree-view') {
            loadFamilyTree();
        }
    });

    // Si l'URL contient #tree-view, activer l'onglet arborescent
    if (window.location.hash === '#tree-view') {
        $('button[data-bs-target="#tree-view"]').tab('show');
    }
});

function loadFamilyTree() {
    const families = <?= json_encode($families, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    
    // Créer la configuration de l'arbre
    const config = {
        chart: {
            container: "#family-tree",
            rootOrientation: "WEST",
            levelSeparation: 120,
            siblingSeparation: 60,
            subTeeSeparation: 60,
            nodeAlign: "BOTTOM",
            padding: 35,
            node: {
                HTMLclass: "family-node",
                collapsable: true
            },
            connectors: {
                type: "step",
                style: {
                    "stroke-width": 2,
                    "stroke": "#ccc"
                }
            },
            animation: {
                nodeAnimation: "easeOutBounce",
                nodeSpeed: 700,
                connectorsAnimation: "bounce",
                connectorsSpeed: 700
            }
        },
        nodeStructure: {
            text: { 
                name: "Familles",
                title: `${families.length} famille${families.length > 1 ? 's' : ''}`
            },
            HTMLclass: "root-node",
            children: []
        }
    };

    // Ajouter les familles à l'arbre
    families.forEach(family => {
        const memberStats = {
            total: family.members ? family.members.length : 0,
            male: family.members ? family.members.filter(m => m.gender === 'M').length : 0,
            female: family.members ? family.members.filter(m => m.gender === 'F').length : 0
        };

        const familyNode = {
            text: {
                name: family.name,
                title: `${memberStats.total} membre${memberStats.total > 1 ? 's' : ''} • ` +
                       `<i class="fas fa-male text-primary"></i> ${memberStats.male} • ` +
                       `<i class="fas fa-female text-danger"></i> ${memberStats.female}`
            },
            HTMLclass: "family-card",
            children: [],
            link: {
                href: `/families/edit/${family.id}`,
                target: "_self"
            }
        };

        // Ajouter les informations de contact
        const contactInfo = [];
        if (family.phone) contactInfo.push(`<i class="fas fa-phone"></i> ${family.phone}`);
        if (family.email) contactInfo.push(`<i class="fas fa-envelope"></i> ${family.email}`);
        if (family.address) contactInfo.push(`<i class="fas fa-map-marker-alt"></i> ${family.address}`);
        
        if (contactInfo.length > 0) {
            familyNode.text.contact = contactInfo.join(' • ');
        }

        // Ajouter les membres de la famille
        if (family.members && family.members.length > 0) {
            family.members.forEach(member => {
                const age = member.birthdate ? calculateAge(member.birthdate) : '';
                const ageText = age ? ` (${age} ans)` : '';
                
                const memberNode = {
                    text: {
                        name: `${member.first_name} ${member.last_name}${ageText}`,
                        title: member.profession || ''
                    },
                    HTMLclass: `member-node ${member.gender === 'M' ? 'male' : 'female'}`,
                    link: {
                        href: `/members/edit/${member.id}`,
                        target: "_self"
                    }
                };

                familyNode.children.push(memberNode);
            });
        }

        config.nodeStructure.children.push(familyNode);
    });

    // Initialiser l'arbre
    treeInstance = new Treant(config);
}

// Fonctions de contrôle de l'arbre
function expandAll() {
    $('.Treant .node').each(function() {
        const nodeId = $(this).attr('id');
        if (nodeId && treeInstance.tree.nodeDB.db[nodeId]) {
            treeInstance.tree.expand(nodeId);
        }
    });
}

function collapseAll() {
    $('.Treant .node').each(function() {
        const nodeId = $(this).attr('id');
        if (nodeId && treeInstance.tree.nodeDB.db[nodeId]) {
            treeInstance.tree.collapse(nodeId);
        }
    });
}

function zoomIn() {
    currentZoom = Math.min(currentZoom + 0.1, 2);
    applyZoom();
}

function zoomOut() {
    currentZoom = Math.max(currentZoom - 0.1, 0.5);
    applyZoom();
}

function resetZoom() {
    currentZoom = 1;
    applyZoom();
}

function applyZoom() {
    $('.tree-container').css('transform', `scale(${currentZoom})`);
}

function calculateAge(birthdate) {
    const today = new Date();
    const birthDate = new Date(birthdate);
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}

function confirmDelete(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette famille ?')) {
        const form = document.getElementById('delete-form');
        form.action = `/families/delete/${id}`;
        form.submit();
    }
}
</script>

<style>
.tree-container {
    width: 100%;
    height: 800px;
    overflow: auto;
    padding: 20px;
    transform-origin: 0 0;
    transition: transform 0.3s ease;
}

.tree-controls {
    position: sticky;
    top: 0;
    z-index: 100;
    background-color: rgba(255, 255, 255, 0.9);
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.Treant > .node {
    padding: 15px;
    border-radius: 8px;
    background-color: white;
    border: 1px solid #ddd;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    width: 250px;
    cursor: pointer;
}

.Treant .node p {
    margin: 0;
    line-height: 1.4;
}

.Treant .node p.node-name {
    font-size: 1.1em;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.Treant .node p.node-title {
    font-size: 0.9em;
    color: #666;
}

.Treant .node p.node-contact {
    font-size: 0.85em;
    color: #888;
    margin-top: 5px;
    border-top: 1px solid #eee;
    padding-top: 5px;
}

.Treant .node.family-card {
    background: linear-gradient(to right, #f8f9fa 0%, #ffffff 100%);
    border-left: 4px solid #0d6efd;
}

.Treant .node.member-node {
    width: 220px;
}

.Treant .node.member-node.male {
    border-left: 4px solid #0d6efd;
    background: linear-gradient(to right, #e3f2fd 0%, #ffffff 100%);
}

.Treant .node.member-node.female {
    border-left: 4px solid #dc3545;
    background: linear-gradient(to right, #fce4ec 0%, #ffffff 100%);
}

.Treant .node.root-node {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    color: white;
    border: none;
    width: 200px;
}

.Treant .node.root-node p.node-title {
    color: rgba(255,255,255,0.8);
}

.Treant .connector {
    transition: stroke 0.3s ease;
}

.Treant .connector:hover {
    stroke: #0d6efd !important;
}

/* Animation pour le collapse/expand */
.Treant .collapse-switch {
    width: 24px;
    height: 24px;
    border-radius: 12px;
    background: #f8f9fa;
    border: 1px solid #ddd;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    transition: all 0.3s ease;
}

.Treant .collapse-switch:hover {
    background: #e9ecef;
    border-color: #ced4da;
}

.Treant .collapsed .collapse-switch:before {
    content: '+';
}

.Treant .expanded .collapse-switch:before {
    content: '−';
}
</style>
<?php $this->stop() ?>
