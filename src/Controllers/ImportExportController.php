<?php
namespace Controllers;

use Core\Controller;
use Core\Session;
use Core\Import;
use Core\Export;
use Models\Member;
use Models\Family;

class ImportExportController extends Controller {
    private Member $memberModel;
    private Family $familyModel;

    public function __construct() {
        parent::__construct();
        $this->memberModel = new Member();
        $this->familyModel = new Family();
    }

    /**
     * Page d'import/export
     */
    public function index() {
        if (!$this->checkPermission(['manage_members'])) {
            Session::setFlash('danger', 'Permission refusée');
            $this->redirect('/dashboard');
        }

        $this->view->render('import_export/index');
    }

    /**
     * Export des membres
     */
    public function exportMembers() {
        if (!$this->checkPermission(['manage_members', 'read_members'])) {
            Session::setFlash('danger', 'Permission refusée');
            $this->redirect('/import-export');
        }

        $format = $this->getGet('format', 'csv');
        $members = $this->memberModel->getAll();

        $headers = [
            'ID', 'Nom', 'Prénom', 'Email', 'Téléphone', 'Date de naissance',
            'Adresse', 'Genre', 'Statut matrimonial', 'Date d\'adhésion', 'Notes'
        ];

        $data = [];
        foreach ($members as $member) {
            $data[] = [
                $member['id'],
                $member['last_name'],
                $member['first_name'],
                $member['email'],
                $member['phone'],
                $member['birth_date'],
                $member['address'],
                $member['gender'],
                $member['marital_status'],
                $member['join_date'],
                $member['notes']
            ];
        }

        switch ($format) {
            case 'excel':
                Export::toExcel($data, $headers, 'membres.xls');
                break;
            case 'pdf':
                Export::toPdf($data, $headers, 'membres.pdf', 'Liste des membres');
                break;
            default:
                Export::toCsv($data, $headers, 'membres.csv');
        }
    }

    /**
     * Export des familles
     */
    public function exportFamilies() {
        if (!$this->checkPermission(['manage_members', 'read_members'])) {
            Session::setFlash('danger', 'Permission refusée');
            $this->redirect('/import-export');
        }

        $format = $this->getGet('format', 'csv');
        $families = $this->familyModel->getAllWithMembers();

        $headers = [
            'ID', 'Nom', 'Adresse', 'Téléphone', 'Email', 
            'Date de mariage', 'Notes', 'Membres'
        ];

        $data = [];
        foreach ($families as $family) {
            $members = array_map(function($member) {
                return $member['name'] . ' (' . $member['role'] . ')';
            }, $family['members']);

            $data[] = [
                $family['id'],
                $family['name'],
                $family['address'],
                $family['phone'],
                $family['email'],
                $family['wedding_date'],
                $family['notes'],
                implode(', ', $members)
            ];
        }

        switch ($format) {
            case 'excel':
                Export::toExcel($data, $headers, 'familles.xls');
                break;
            case 'pdf':
                Export::toPdf($data, $headers, 'familles.pdf', 'Liste des familles');
                break;
            default:
                Export::toCsv($data, $headers, 'familles.csv');
        }
    }

    /**
     * Import des membres
     */
    public function importMembers() {
        if (!$this->checkPermission('manage_members')) {
            Session::setFlash('danger', 'Permission refusée');
            $this->redirect('/import-export');
        }

        try {
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('Erreur lors du téléchargement du fichier');
            }

            $file = $_FILES['file']['tmp_name'];
            $extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

            // En-têtes requis
            $requiredHeaders = [
                'Nom', 'Prénom', 'Email', 'Téléphone', 'Date de naissance',
                'Genre', 'Statut matrimonial'
            ];

            // Import selon le format
            $data = $extension === 'xls' 
                ? Import::fromExcel($file, $requiredHeaders)
                : Import::fromCsv($file, $requiredHeaders);

            // Règles de validation
            $rules = [
                'Nom' => ['required' => true, 'min' => 2],
                'Prénom' => ['required' => true, 'min' => 2],
                'Email' => ['type' => 'email'],
                'Téléphone' => ['required' => true, 'type' => 'phone'],
                'Date de naissance' => ['type' => 'date'],
                'Genre' => ['enum' => ['M', 'F']],
                'Statut matrimonial' => ['enum' => ['Célibataire', 'Marié(e)', 'Divorcé(e)', 'Veuf/Veuve']]
            ];

            // Validation des données
            $errors = Import::validate($data, $rules);
            if (!empty($errors)) {
                throw new \Exception(implode("\n", $errors));
            }

            // Import des membres
            $imported = 0;
            foreach ($data as $row) {
                $memberData = [
                    'last_name' => $row['Nom'],
                    'first_name' => $row['Prénom'],
                    'email' => $row['Email'],
                    'phone' => $row['Téléphone'],
                    'birth_date' => $row['Date de naissance'],
                    'gender' => $row['Genre'],
                    'marital_status' => $row['Statut matrimonial'],
                    'address' => $row['Adresse'] ?? '',
                    'join_date' => $row['Date d\'adhésion'] ?? date('Y-m-d'),
                    'notes' => $row['Notes'] ?? '',
                    'created_by' => Session::get('user_id')
                ];

                if ($this->memberModel->create($memberData)) {
                    $imported++;
                }
            }

            Session::setFlash('success', "$imported membres importés avec succès");

        } catch (\Exception $e) {
            Session::setFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        $this->redirect('/import-export');
    }

    /**
     * Import des familles
     */
    public function importFamilies() {
        if (!$this->checkPermission('manage_members')) {
            Session::setFlash('danger', 'Permission refusée');
            $this->redirect('/import-export');
        }

        try {
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('Erreur lors du téléchargement du fichier');
            }

            $file = $_FILES['file']['tmp_name'];
            $extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

            // En-têtes requis
            $requiredHeaders = ['Nom', 'Téléphone'];

            // Import selon le format
            $data = $extension === 'xls' 
                ? Import::fromExcel($file, $requiredHeaders)
                : Import::fromCsv($file, $requiredHeaders);

            // Règles de validation
            $rules = [
                'Nom' => ['required' => true, 'min' => 2],
                'Téléphone' => ['required' => true, 'type' => 'phone'],
                'Email' => ['type' => 'email'],
                'Date de mariage' => ['type' => 'date']
            ];

            // Validation des données
            $errors = Import::validate($data, $rules);
            if (!empty($errors)) {
                throw new \Exception(implode("\n", $errors));
            }

            // Import des familles
            $imported = 0;
            foreach ($data as $row) {
                $familyData = [
                    'name' => $row['Nom'],
                    'phone' => $row['Téléphone'],
                    'email' => $row['Email'] ?? '',
                    'address' => $row['Adresse'] ?? '',
                    'wedding_date' => $row['Date de mariage'] ?? null,
                    'notes' => $row['Notes'] ?? '',
                    'created_by' => Session::get('user_id')
                ];

                if ($this->familyModel->create($familyData)) {
                    $imported++;
                }
            }

            Session::setFlash('success', "$imported familles importées avec succès");

        } catch (\Exception $e) {
            Session::setFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        $this->redirect('/import-export');
    }
}
