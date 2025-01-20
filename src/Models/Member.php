<?php
namespace App\Models;

use Core\Model;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class Member extends Model {
    protected string $table = 'members';

    /**
     * Recherche avancée de membres
     */
    public function search(array $criteria = [], string $sort = 'last_name', string $order = 'ASC'): array {
        $sql = "SELECT m.*, f.name as family_name, fr.relationship_type 
                FROM {$this->table} m 
                LEFT JOIN family_relationships fr ON m.id = fr.member_id 
                LEFT JOIN families f ON fr.family_id = f.id 
                WHERE 1=1";
        $params = [];

        // Critères de recherche
        if (!empty($criteria['name'])) {
            $sql .= " AND (m.first_name LIKE :name OR m.last_name LIKE :name)";
            $params['name'] = "%{$criteria['name']}%";
        }

        if (!empty($criteria['status'])) {
            $sql .= " AND m.status = :status";
            $params['status'] = $criteria['status'];
        }

        if (!empty($criteria['gender'])) {
            $sql .= " AND m.gender = :gender";
            $params['gender'] = $criteria['gender'];
        }

        if (!empty($criteria['age_min'])) {
            $sql .= " AND YEAR(CURDATE()) - YEAR(m.date_of_birth) >= :age_min";
            $params['age_min'] = $criteria['age_min'];
        }

        if (!empty($criteria['age_max'])) {
            $sql .= " AND YEAR(CURDATE()) - YEAR(m.date_of_birth) <= :age_max";
            $params['age_max'] = $criteria['age_max'];
        }

        if (!empty($criteria['membership_start'])) {
            $sql .= " AND m.membership_date >= :membership_start";
            $params['membership_start'] = $criteria['membership_start'];
        }

        if (!empty($criteria['membership_end'])) {
            $sql .= " AND m.membership_date <= :membership_end";
            $params['membership_end'] = $criteria['membership_end'];
        }

        // Tri
        $sql .= " ORDER BY {$sort} {$order}";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Exporte les membres au format Excel
     */
    public function exportToExcel(array $members): string {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes
        $headers = [
            'ID', 'Nom', 'Prénom', 'Genre', 'Date de naissance', 
            'Téléphone', 'Email', 'Adresse', 'Ville', 'Profession',
            'État civil', 'Date de baptême', 'Date d\'adhésion', 
            'Statut', 'Famille', 'Rôle familial'
        ];
        $sheet->fromArray([$headers], null, 'A1');

        // Données
        $row = 2;
        foreach ($members as $member) {
            $sheet->fromArray([[
                $member['id'],
                $member['last_name'],
                $member['first_name'],
                $member['gender'],
                $member['date_of_birth'],
                $member['phone'],
                $member['email'],
                $member['address'],
                $member['city'],
                $member['profession'],
                $member['marital_status'],
                $member['baptism_date'],
                $member['membership_date'],
                $member['status'],
                $member['family_name'] ?? '',
                $member['relationship_type'] ?? ''
            ]], null, "A{$row}");
            $row++;
        }

        // Formatage
        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Sauvegarde
        $filename = 'membres_' . date('Y-m-d_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $path = UPLOADS_PATH . '/' . $filename;
        $writer->save($path);

        return $filename;
    }

    /**
     * Importe des membres depuis un fichier Excel ou CSV
     */
    public function importFromFile(string $file): array {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Retire les en-têtes
        $headers = array_shift($rows);
        
        $results = ['success' => 0, 'errors' => []];
        
        foreach ($rows as $row) {
            try {
                $data = array_combine($headers, $row);
                $this->validate($data);
                $this->db->insert($this->table, $this->prepare($data));
                $results['success']++;
            } catch (\Exception $e) {
                $results['errors'][] = "Ligne {$results['success']}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Ajoute un visiteur
     */
    public function addVisitor(array $data): int {
        // Crée d'abord le membre
        $memberId = $this->create(array_merge($data, ['status' => 'visitor']));

        // Ajoute l'entrée visiteur
        $visitorData = [
            'member_id' => $memberId,
            'visit_date' => date('Y-m-d'),
            'source' => $data['source'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('visitors', $visitorData);

        return $memberId;
    }

    /**
     * Met à jour le suivi d'un visiteur
     */
    public function updateVisitorFollowUp(int $visitorId, string $status, ?string $notes = null): bool {
        return $this->db->update('visitors', [
            'follow_up_status' => $status,
            'follow_up_notes' => $notes,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $visitorId]);
    }

    /**
     * Obtient les statistiques des visiteurs
     */
    public function getVisitorStats(string $startDate, string $endDate): array {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN follow_up_status = 'joined' THEN 1 ELSE 0 END) as joined,
                SUM(CASE WHEN follow_up_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN follow_up_status = 'contacted' THEN 1 ELSE 0 END) as contacted
                FROM visitors
                WHERE visit_date BETWEEN :start AND :end";

        return $this->db->query($sql, [
            'start' => $startDate,
            'end' => $endDate
        ])->fetch();
    }

    /**
     * Valide les données d'un membre
     */
    private function validate(array $data): void {
        if (empty($data['first_name']) || empty($data['last_name'])) {
            throw new \Exception('Le nom et le prénom sont requis');
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Email invalide');
        }

        if (!empty($data['date_of_birth']) && !strtotime($data['date_of_birth'])) {
            throw new \Exception('Date de naissance invalide');
        }
    }

    /**
     * Prépare les données pour l'insertion/mise à jour
     */
    private function prepare(array $data): array {
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $data;
    }
}
