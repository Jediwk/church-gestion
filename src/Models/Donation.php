<?php
namespace Models;

use Core\Model;
use PDO;

class Donation extends Model {
    protected string $table = 'donations';

    /**
     * Récupère tous les dons avec les détails des membres
     */
    public function getAllWithDetails(): array {
        $sql = "SELECT d.*, 
                       CONCAT(m.first_name, ' ', m.last_name) as member_name
                FROM donations d
                LEFT JOIN members m ON d.member_id = m.id
                ORDER BY d.date DESC, d.created_at DESC";
        
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Récupère un don avec ses détails
     */
    public function getWithDetails(int $id): ?array {
        $sql = "SELECT d.*, 
                       CONCAT(m.first_name, ' ', m.last_name) as member_name
                FROM donations d
                LEFT JOIN members m ON d.member_id = m.id
                WHERE d.id = ?";
        
        return $this->db->query($sql, [$id])->fetch() ?: null;
    }

    /**
     * Récupère les dons pour DataTables
     */
    public function getForDataTables(array $params): array {
        $limit = $params['length'] ?? 10;
        $offset = $params['start'] ?? 0;
        $search = $params['search']['value'] ?? '';
        $orderCol = $params['order'][0]['column'] ?? 1;
        $orderDir = $params['order'][0]['dir'] ?? 'desc';

        // Colonnes pour le tri
        $columns = [
            0 => 'd.id',
            1 => 'd.date',
            2 => 'member_name',
            3 => 'd.amount',
            4 => 'd.type',
            5 => 'd.payment_method',
            6 => 'd.status'
        ];

        // Construction de la requête
        $sql = "SELECT d.*, 
                       CONCAT(m.first_name, ' ', m.last_name) as member_name
                FROM donations d
                LEFT JOIN members m ON d.member_id = m.id";

        // Recherche
        if (!empty($search)) {
            $sql .= " WHERE (CONCAT(m.first_name, ' ', m.last_name) LIKE :search 
                     OR d.type LIKE :search 
                     OR d.campaign LIKE :search
                     OR d.reference_number LIKE :search)";
        }

        // Tri
        if (isset($columns[$orderCol])) {
            $sql .= " ORDER BY {$columns[$orderCol]} $orderDir";
        }

        // Total des enregistrements
        $countSql = "SELECT COUNT(*) FROM donations";
        $totalRecords = $this->db->query($countSql)->fetchColumn();
        
        // Total filtré
        $filteredRecords = $totalRecords;
        if (!empty($search)) {
            $countSql = str_replace('d.*', 'COUNT(*)', $sql);
            $stmt = $this->db->prepare($countSql);
            $stmt->execute(['search' => "%$search%"]);
            $filteredRecords = $stmt->fetchColumn();
        }

        // Pagination
        $sql .= " LIMIT :limit OFFSET :offset";
        
        // Exécution de la requête
        $stmt = $this->db->prepare($sql);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return [
            'draw' => $params['draw'] ?? 1,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ];
    }

    /**
     * Crée un nouveau don
     */
    public function create(array $data): bool {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO donations (
                        member_id, amount, date, type, campaign,
                        payment_method, reference_number, notes,
                        status, created_by
                    ) VALUES (
                        :member_id, :amount, :date, :type, :campaign,
                        :payment_method, :reference_number, :notes,
                        :status, :created_by
                    )";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'member_id' => $data['member_id'] ?? null,
                'amount' => $data['amount'],
                'date' => $data['date'],
                'type' => $data['type'],
                'campaign' => $data['campaign'] ?? null,
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'] ?? 'completed',
                'created_by' => $data['created_by']
            ]);

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Met à jour un don
     */
    public function update(int $id, array $data): bool {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE donations SET
                        member_id = :member_id,
                        amount = :amount,
                        date = :date,
                        type = :type,
                        campaign = :campaign,
                        payment_method = :payment_method,
                        reference_number = :reference_number,
                        notes = :notes,
                        status = :status
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'id' => $id,
                'member_id' => $data['member_id'] ?? null,
                'amount' => $data['amount'],
                'date' => $data['date'],
                'type' => $data['type'],
                'campaign' => $data['campaign'] ?? null,
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'] ?? 'completed'
            ]);

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Récupère les statistiques des dons
     */
    public function getStats(string $period = 'month'): array {
        $dateFormat = $period === 'year' ? '%Y' : '%Y-%m';
        $groupBy = $period === 'year' ? 'YEAR(date)' : 'DATE_FORMAT(date, "%Y-%m")';

        $sql = "SELECT 
                    $groupBy as period,
                    type,
                    COUNT(*) as count,
                    SUM(amount) as total,
                    AVG(amount) as average
                FROM donations
                WHERE status = 'completed'
                GROUP BY period, type
                ORDER BY period DESC, type";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Récupère les statistiques par membre
     */
    public function getStatsByMember(int $memberId): array {
        $sql = "SELECT 
                    type,
                    COUNT(*) as count,
                    SUM(amount) as total,
                    MIN(amount) as min_amount,
                    MAX(amount) as max_amount,
                    AVG(amount) as average
                FROM donations
                WHERE member_id = ?
                AND status = 'completed'
                GROUP BY type";

        return $this->db->query($sql, [$memberId])->fetchAll();
    }

    /**
     * Récupère les dons d'un membre
     */
    public function getMemberDonations(int $memberId): array {
        $sql = "SELECT * FROM donations 
                WHERE member_id = ? 
                ORDER BY date DESC, created_at DESC";
        
        return $this->db->query($sql, [$memberId])->fetchAll();
    }
}
