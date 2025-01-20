<?php
use Core\View;
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between mb-3">
        <h1 class="h3">Gestion des dons</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDonationModal">
            <i class="fas fa-plus"></i> Nouveau don
        </button>
    </div>

    <!-- Tableau des dons -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Liste des dons</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="donationsTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Membre</th>
                            <th>Type</th>
                            <th>Montant</th>
                            <th>Méthode</th>
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
<div class="modal fade" id="addDonationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouveau don</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="donationForm">
                    <input type="hidden" name="id" id="donationId">
                    
                    <div class="mb-3">
                        <label class="form-label">Membre</label>
                        <select name="member_id" id="memberId" class="form-select">
                            <option value="">Sélectionner un membre</option>
                            <?php foreach ($members as $member): ?>
                            <option value="<?= $member['id'] ?>">
                                <?= $member['first_name'] . ' ' . $member['last_name'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type de don</label>
                        <select name="type" id="donationType" class="form-select" required>
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
                        <label class="form-label">Date</label>
                        <input type="date" name="date" id="date" class="form-control" required>
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
                        <input type="text" name="reference_number" id="referenceNumber" class="form-control">
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
                <button type="button" class="btn btn-primary" id="saveDonation">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation de DataTables
    const table = $('#donationsTable').DataTable({
        ajax: '<?= View::url('/donations/datatable') ?>',
        columns: [
            { data: 'id' },
            { data: 'date' },
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
                data: 'payment_method',
                render: function(data) {
                    const methods = {
                        'cash': 'Espèces',
                        'check': 'Chèque',
                        'bank_transfer': 'Virement',
                        'mobile_money': 'Mobile Money',
                        'other': 'Autre'
                    };
                    return methods[data] || data;
                }
            },
            {
                data: 'status',
                render: function(data) {
                    const badges = {
                        'pending': 'warning',
                        'completed': 'success',
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
                            <button type="button" class="btn btn-sm btn-primary edit-donation" data-id="${data}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="<?= View::url('/donations/receipt/') ?>${data}" class="btn btn-sm btn-info" target="_blank">
                                <i class="fas fa-file-invoice"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger delete-donation" data-id="${data}">
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

    // Enregistrement d'un don
    $('#saveDonation').click(function() {
        const form = $('#donationForm');
        const id = $('#donationId').val();
        const url = id ? 
            `<?= View::url('/donations/') ?>${id}` : 
            '<?= View::url('/donations/store') ?>';

        $.ajax({
            url: url,
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    table.ajax.reload();
                    $('#addDonationModal').modal('hide');
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

    // Édition d'un don
    $(document).on('click', '.edit-donation', function() {
        const id = $(this).data('id');
        
        $.get(`<?= View::url('/donations/') ?>${id}`, function(donation) {
            $('#donationId').val(donation.id);
            $('#memberId').val(donation.member_id);
            $('#donationType').val(donation.type);
            $('#amount').val(donation.amount);
            $('#date').val(donation.date);
            $('#paymentMethod').val(donation.payment_method);
            $('#referenceNumber').val(donation.reference_number);
            $('#campaign').val(donation.campaign);
            $('#notes').val(donation.notes);
            
            $('.modal-title').text('Modifier le don');
            $('#addDonationModal').modal('show');
        });
    });

    // Suppression d'un don
    $(document).on('click', '.delete-donation', function() {
        const id = $(this).data('id');
        
        if (confirm('Êtes-vous sûr de vouloir supprimer ce don ?')) {
            $.ajax({
                url: `<?= View::url('/donations/') ?>${id}/delete`,
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

    // Réinitialisation du formulaire
    $('#addDonationModal').on('hidden.bs.modal', function() {
        $('#donationForm')[0].reset();
        $('#donationId').val('');
        $('.modal-title').text('Nouveau don');
    });
});
</script>
