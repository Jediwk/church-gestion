<?php
use Core\View;
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between mb-3">
        <h1 class="h3">Gestion des promesses</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPledgeModal">
            <i class="fas fa-plus"></i> Nouvelle promesse
        </button>
    </div>

    <!-- Tableau des promesses -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Liste des promesses</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="pledgesTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date début</th>
                            <th>Membre</th>
                            <th>Type</th>
                            <th>Montant</th>
                            <th>Payé</th>
                            <th>Fréquence</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajout/Modification -->
<div class="modal fade" id="addPledgeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle promesse</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="pledgeForm">
                    <input type="hidden" name="id" id="pledgeId">
                    
                    <div class="mb-3">
                        <label class="form-label">Membre</label>
                        <select name="member_id" id="memberId" class="form-select" required>
                            <option value="">Sélectionner un membre</option>
                            <?php foreach ($members as $member): ?>
                            <option value="<?= $member['id'] ?>">
                                <?= $member['first_name'] . ' ' . $member['last_name'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type de promesse</label>
                        <select name="type" id="pledgeType" class="form-select" required>
                            <option value="tithe">Dîme</option>
                            <option value="offering">Offrande</option>
                            <option value="special">Don spécial</option>
                            <option value="project">Projet</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Montant (XOF)</label>
                        <input type="number" name="amount" id="amount" class="form-control" required min="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date de début</label>
                        <input type="date" name="start_date" id="startDate" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date de fin (optionnel)</label>
                        <input type="date" name="end_date" id="endDate" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fréquence</label>
                        <select name="frequency" id="frequency" class="form-select" required>
                            <option value="one_time">Une fois</option>
                            <option value="weekly">Hebdomadaire</option>
                            <option value="monthly">Mensuel</option>
                            <option value="quarterly">Trimestriel</option>
                            <option value="yearly">Annuel</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Campagne</label>
                        <input type="text" name="campaign" id="campaign" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="savePledge">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Paiement -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enregistrer un paiement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <input type="hidden" name="pledge_id" id="paymentPledgeId">

                    <div class="mb-3">
                        <label class="form-label">Montant (XOF)</label>
                        <input type="number" name="amount" id="paymentAmount" class="form-control" required min="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" id="paymentDate" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Méthode de paiement</label>
                        <select name="payment_method" id="paymentMethod" class="form-select" required>
                            <option value="cash">Espèces</option>
                            <option value="check">Chèque</option>
                            <option value="bank_transfer">Virement bancaire</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Référence</label>
                        <input type="text" name="reference_number" id="paymentReference" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="paymentNotes" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="savePayment">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation de DataTables
    const table = $('#pledgesTable').DataTable({
        ajax: '<?= View::url('/pledges/datatable') ?>',
        columns: [
            { data: 'id' },
            { data: 'start_date' },
            { data: 'member_name' },
            { 
                data: 'type',
                render: function(data) {
                    const types = {
                        'tithe': 'Dîme',
                        'offering': 'Offrande',
                        'special': 'Don spécial',
                        'project': 'Projet'
                    };
                    return types[data] || data;
                }
            },
            { 
                data: 'amount',
                render: function(data) {
                    return new Intl.NumberFormat('fr-FR').format(data) + ' XOF';
                }
            },
            {
                data: 'paid_amount',
                render: function(data, type, row) {
                    const amount = data || 0;
                    const percentage = row.amount ? Math.round(amount * 100 / row.amount) : 0;
                    return `
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: ${percentage}%;" 
                                 aria-valuenow="${percentage}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                ${new Intl.NumberFormat('fr-FR').format(amount)} XOF (${percentage}%)
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'frequency',
                render: function(data) {
                    const frequencies = {
                        'one_time': 'Une fois',
                        'weekly': 'Hebdomadaire',
                        'monthly': 'Mensuel',
                        'quarterly': 'Trimestriel',
                        'yearly': 'Annuel'
                    };
                    return frequencies[data] || data;
                }
            },
            {
                data: 'status',
                render: function(data) {
                    const badges = {
                        'active': 'success',
                        'completed': 'info',
                        'cancelled': 'danger'
                    };
                    return `<span class="badge bg-${badges[data]}">${data}</span>`;
                }
            },
            {
                data: 'id',
                render: function(data, type, row) {
                    return `
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-success add-payment" data-id="${data}">
                                <i class="fas fa-dollar-sign"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary edit-pledge" data-id="${data}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="<?= View::url('/pledges/payments/') ?>${data}" class="btn btn-sm btn-info">
                                <i class="fas fa-history"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger delete-pledge" data-id="${data}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[1, 'desc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
        }
    });

    // Enregistrement d'une promesse
    $('#savePledge').click(function() {
        const form = $('#pledgeForm');
        const id = $('#pledgeId').val();
        const url = id ? 
            `<?= View::url('/pledges/') ?>${id}` : 
            '<?= View::url('/pledges/store') ?>';

        $.ajax({
            url: url,
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    table.ajax.reload();
                    $('#addPledgeModal').modal('hide');
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('Une erreur est survenue');
            }
        });
    });

    // Édition d'une promesse
    $(document).on('click', '.edit-pledge', function() {
        const id = $(this).data('id');
        
        $.get(`<?= View::url('/pledges/') ?>${id}`, function(pledge) {
            $('#pledgeId').val(pledge.id);
            $('#memberId').val(pledge.member_id);
            $('#pledgeType').val(pledge.type);
            $('#amount').val(pledge.amount);
            $('#startDate').val(pledge.start_date);
            $('#endDate').val(pledge.end_date);
            $('#frequency').val(pledge.frequency);
            $('#campaign').val(pledge.campaign);
            $('#notes').val(pledge.notes);
            
            $('.modal-title').text('Modifier la promesse');
            $('#addPledgeModal').modal('show');
        });
    });

    // Ajout d'un paiement
    $(document).on('click', '.add-payment', function() {
        const id = $(this).data('id');
        $('#paymentPledgeId').val(id);
        $('#paymentDate').val(new Date().toISOString().split('T')[0]);
        $('#paymentModal').modal('show');
    });

    // Enregistrement d'un paiement
    $('#savePayment').click(function() {
        const form = $('#paymentForm');
        const pledgeId = $('#paymentPledgeId').val();

        $.ajax({
            url: `<?= View::url('/pledges/') ?>${pledgeId}/payment`,
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    table.ajax.reload();
                    $('#paymentModal').modal('hide');
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('Une erreur est survenue');
            }
        });
    });

    // Suppression d'une promesse
    $(document).on('click', '.delete-pledge', function() {
        const id = $(this).data('id');
        
        if (confirm('Êtes-vous sûr de vouloir supprimer cette promesse ?')) {
            $.ajax({
                url: `<?= View::url('/pledges/') ?>${id}/delete`,
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        table.ajax.reload();
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error('Une erreur est survenue');
                }
            });
        }
    });

    // Réinitialisation des formulaires
    $('#addPledgeModal').on('hidden.bs.modal', function() {
        $('#pledgeForm')[0].reset();
        $('#pledgeId').val('');
        $('.modal-title').text('Nouvelle promesse');
    });

    $('#paymentModal').on('hidden.bs.modal', function() {
        $('#paymentForm')[0].reset();
        $('#paymentPledgeId').val('');
    });
});
</script>
