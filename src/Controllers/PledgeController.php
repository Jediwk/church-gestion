<?php
namespace Controllers;

use Core\Controller;
use Core\View;
use Models\DonationPledge;
use Models\Member;

class PledgeController extends Controller {
    private DonationPledge $pledgeModel;
    private Member $memberModel;

    public function __construct() {
        parent::__construct();
        $this->pledgeModel = new DonationPledge();
        $this->memberModel = new Member();
    }

    /**
     * Liste des promesses
     */
    public function index() {
        $this->checkPermission('manage_finances');
        
        $members = $this->memberModel->getAll();
        return View::render('pledges/index', ['members' => $members]);
    }

    /**
     * Données pour DataTables
     */
    public function datatable() {
        $this->checkPermission('manage_finances');
        return $this->json($this->pledgeModel->getForDataTables($_GET));
    }

    /**
     * Création d'une promesse
     */
    public function store() {
        $this->checkPermission('manage_finances');

        $data = $this->validatePledgeData($_POST);
        $data['created_by'] = $this->auth->user['id'];

        if ($this->pledgeModel->create($data)) {
            return $this->json(['success' => true, 'message' => 'Promesse enregistrée avec succès']);
        }

        return $this->json(['success' => false, 'message' => 'Erreur lors de l\'enregistrement de la promesse'], 500);
    }

    /**
     * Mise à jour d'une promesse
     */
    public function update(int $id) {
        $this->checkPermission('manage_finances');

        $data = $this->validatePledgeData($_POST);

        if ($this->pledgeModel->update($id, $data)) {
            return $this->json(['success' => true, 'message' => 'Promesse mise à jour avec succès']);
        }

        return $this->json(['success' => false, 'message' => 'Erreur lors de la mise à jour de la promesse'], 500);
    }

    /**
     * Suppression d'une promesse
     */
    public function delete(int $id) {
        $this->checkPermission('manage_finances');

        if ($this->pledgeModel->delete($id)) {
            return $this->json(['success' => true, 'message' => 'Promesse supprimée avec succès']);
        }

        return $this->json(['success' => false, 'message' => 'Erreur lors de la suppression de la promesse'], 500);
    }

    /**
     * Validation des données de promesse
     */
    private function validatePledgeData(array $data): array {
        return $this->validate($data, [
            'member_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:tithe,offering,special,project',
            'campaign' => 'string',
            'start_date' => 'required|date',
            'end_date' => 'date',
            'frequency' => 'required|in:one_time,weekly,monthly,quarterly,yearly',
            'notes' => 'string',
            'status' => 'in:active,completed,cancelled'
        ]);
    }

    /**
     * Promesses par membre
     */
    public function memberPledges(int $memberId) {
        $this->checkPermission('manage_finances');

        $member = $this->memberModel->find($memberId);
        if (!$member) {
            return $this->notFound();
        }

        $pledges = $this->pledgeModel->getMemberActivePledges($memberId);

        return View::render('pledges/member', [
            'member' => $member,
            'pledges' => $pledges
        ]);
    }

    /**
     * Liste des promesses dues
     */
    public function duePledges() {
        $this->checkPermission('manage_finances');

        $pledges = $this->pledgeModel->getDuePledges();
        return View::render('pledges/due', ['pledges' => $pledges]);
    }

    /**
     * Enregistrement d'un paiement
     */
    public function recordPayment(int $pledgeId) {
        $this->checkPermission('manage_finances');

        $data = $this->validate($_POST, [
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payment_method' => 'required|in:cash,check,bank_transfer,mobile_money,other',
            'reference_number' => 'string',
            'notes' => 'string'
        ]);

        $data['pledge_id'] = $pledgeId;
        $data['created_by'] = $this->auth->user['id'];

        try {
            $this->db->beginTransaction();

            // Enregistrer le paiement
            $sql = "INSERT INTO pledge_payments (
                        pledge_id, amount, date, payment_method,
                        reference_number, notes, created_by
                    ) VALUES (
                        :pledge_id, :amount, :date, :payment_method,
                        :reference_number, :notes, :created_by
                    )";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($data);

            if ($result) {
                // Vérifier si la promesse est complètement payée
                $pledge = $this->pledgeModel->getWithDetails($pledgeId);
                if ($pledge['paid_amount'] >= $pledge['amount']) {
                    $this->pledgeModel->update($pledgeId, ['status' => 'completed']);
                }

                $this->db->commit();
                return $this->json(['success' => true, 'message' => 'Paiement enregistré avec succès']);
            }

            $this->db->rollBack();
            return $this->json(['success' => false, 'message' => 'Erreur lors de l\'enregistrement du paiement'], 500);

        } catch (\Exception $e) {
            $this->db->rollBack();
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Historique des paiements
     */
    public function payments(int $pledgeId) {
        $this->checkPermission('manage_finances');

        $pledge = $this->pledgeModel->getWithDetails($pledgeId);
        if (!$pledge) {
            return $this->notFound();
        }

        $sql = "SELECT * FROM pledge_payments 
                WHERE pledge_id = ? 
                ORDER BY date DESC, created_at DESC";
        
        $payments = $this->db->query($sql, [$pledgeId])->fetchAll();

        return View::render('pledges/payments', [
            'pledge' => $pledge,
            'payments' => $payments
        ]);
    }
}
