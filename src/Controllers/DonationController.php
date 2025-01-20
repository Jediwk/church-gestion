<?php
namespace Controllers;

use Core\Controller;
use Core\View;
use Models\Donation;
use Models\Member;

class DonationController extends Controller {
    private Donation $donationModel;
    private Member $memberModel;

    public function __construct() {
        parent::__construct();
        $this->donationModel = new Donation();
        $this->memberModel = new Member();
    }

    /**
     * Liste des dons
     */
    public function index() {
        $this->checkPermission('manage_finances');
        
        $members = $this->memberModel->getAll();
        return View::render('donations/index', ['members' => $members]);
    }

    /**
     * Données pour DataTables
     */
    public function datatable() {
        $this->checkPermission('manage_finances');
        return $this->json($this->donationModel->getForDataTables($_GET));
    }

    /**
     * Création d'un don
     */
    public function store() {
        $this->checkPermission('manage_finances');

        $data = $this->validateDonationData($_POST);
        $data['created_by'] = $this->auth->user['id'];

        if ($this->donationModel->create($data)) {
            return $this->json(['success' => true, 'message' => 'Don enregistré avec succès']);
        }

        return $this->json(['success' => false, 'message' => 'Erreur lors de l\'enregistrement du don'], 500);
    }

    /**
     * Mise à jour d'un don
     */
    public function update(int $id) {
        $this->checkPermission('manage_finances');

        $data = $this->validateDonationData($_POST);

        if ($this->donationModel->update($id, $data)) {
            return $this->json(['success' => true, 'message' => 'Don mis à jour avec succès']);
        }

        return $this->json(['success' => false, 'message' => 'Erreur lors de la mise à jour du don'], 500);
    }

    /**
     * Suppression d'un don
     */
    public function delete(int $id) {
        $this->checkPermission('manage_finances');

        if ($this->donationModel->delete($id)) {
            return $this->json(['success' => true, 'message' => 'Don supprimé avec succès']);
        }

        return $this->json(['success' => false, 'message' => 'Erreur lors de la suppression du don'], 500);
    }

    /**
     * Validation des données de don
     */
    private function validateDonationData(array $data): array {
        return $this->validate($data, [
            'member_id' => 'integer',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'type' => 'required|in:tithe,offering,special,project',
            'campaign' => 'string',
            'payment_method' => 'required|in:cash,check,bank_transfer,mobile_money,other',
            'reference_number' => 'string',
            'notes' => 'string',
            'status' => 'in:pending,completed,cancelled'
        ]);
    }

    /**
     * Génération de reçu
     */
    public function receipt(int $id) {
        $this->checkPermission('manage_finances');

        $donation = $this->donationModel->getWithDetails($id);
        if (!$donation) {
            return $this->notFound();
        }

        return View::render('donations/receipt', [
            'donation' => $donation,
            'church' => [
                'name' => 'Nom de l\'Église',
                'address' => 'Adresse de l\'Église',
                'phone' => 'Téléphone de l\'Église',
                'email' => 'Email de l\'Église'
            ]
        ]);
    }

    /**
     * Statistiques des dons
     */
    public function stats() {
        $this->checkPermission('manage_finances');

        $period = $_GET['period'] ?? 'month';
        $stats = $this->donationModel->getStats($period);

        return View::render('donations/stats', [
            'stats' => $stats,
            'period' => $period
        ]);
    }

    /**
     * Export des statistiques
     */
    public function exportStats() {
        $this->checkPermission('manage_finances');

        $period = $_GET['period'] ?? 'month';
        $format = $_GET['format'] ?? 'pdf';
        $stats = $this->donationModel->getStats($period);

        $data = [
            'stats' => $stats,
            'period' => $period,
            'generated_at' => date('Y-m-d H:i:s')
        ];

        switch ($format) {
            case 'pdf':
                return $this->exportPDF('donations/stats/pdf', $data, 'statistiques_dons.pdf');
            case 'excel':
                return $this->exportExcel('donations/stats/excel', $data, 'statistiques_dons.xlsx');
            default:
                return $this->redirect('/donations/stats');
        }
    }

    /**
     * Dons par membre
     */
    public function memberDonations(int $memberId) {
        $this->checkPermission('manage_finances');

        $member = $this->memberModel->find($memberId);
        if (!$member) {
            return $this->notFound();
        }

        $donations = $this->donationModel->getMemberDonations($memberId);
        $stats = $this->donationModel->getStatsByMember($memberId);

        return View::render('donations/member', [
            'member' => $member,
            'donations' => $donations,
            'stats' => $stats
        ]);
    }
}
