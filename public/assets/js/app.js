// Initialisation des tooltips Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Confirmation de suppression
function confirmDelete(event, message) {
    if (!confirm(message || 'Êtes-vous sûr de vouloir supprimer cet élément ?')) {
        event.preventDefault();
        return false;
    }
    return true;
}
