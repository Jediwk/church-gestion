<?php
namespace Models;

use Core\Model;
use PDO;

class DonationPledge extends Model {
    protected string $table = 'donation_pledges';

    /**
     * Récupère toutes les promesses avec les détails des membres
     */
    public function getAllWithDetails(): array {
        $sql = "SELECT p.*, 
                       CONCAT(m.first_name, ' ', m.last_name) as member_name,
                       (SELECT SUM(amount) FROM pledge_payments WHERE pledge_id = p.id AND status = 'completed') as paid_amount
                FROM donation_pledges p
                LEFT JOIN members m ON p.member_id = m.id
                ORDER BY p.start_date DESC, p.created_at DESC";
        
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Récupère une promesse avec ses détails
     */
    public function getWithDetails(int $id): ?array {
        $sql = "SELECT p.*, 
                       CONCAT(m.first_name, ' ', m.last_name) as member_name,
                       (SELECT SUM(amount) FROM pledge_payments WHERE pledge_id = p.id AND status = 'completed') as paid_amount
                FROM donation_pledges p
                LEFT JOIN members m ON p.member_id = m.id
                WHERE p.id = ?";
        
        return $this->db->query($sql, [$id])->fetch() ?: null;
    }

    /**
     * Récupère les promesses pour DataTables
     */
    public function getForDataTables(array $params): array {
        $limit = $params['length'] ?? 10;
        $offset = $params['start'] ?? 0;
        $search = $params['search']['value'] ?? '';
        $orderCol = $params['order'][0]['column'] ?? 1;
        $orderDir = $params['order'][0]['dir'] ?? 'desc';

        // Colonnes pour le tri
        $columns = [
            0 => 'p.id',
            1 => 'p.start_date',
            2 => 'member_name',
            3 => 'p.amount',
            4 => 'p.type',
            5 => 'p.frequency',
            6 => 'p.status'
        ];

        // Construction de la requête
        $sql = "SELECT p.*, 
                       CONCAT(m.first_name, ' ', m.last_name) as member_name,
                       (SELECT SUM(amount) FROM pledge_payments WHERE pledge_id = p.id AND status = 'completed') as paid_amount
                FROM donation_pledges p
                LEFT JOIN members m ON p.member_id = m.id";

        // Recherche
        if (!empty($search)) {
            $sql .= " WHERE (CONCAT(m.first_name, ' ', m.last_name) LIKE :search 
                     OR p.type LIKE :search 
                     OR p.campaign LIKE :search)";
        }

        // Tri
        if (isset($columns[$orderCol])) {
            $sql .= " ORDER BY {$columns[$orderCol]} $orderDir";
        }

        // Total des enregistrements
        $countSql = "SELECT COUNT(*) FROM donation_pledges";
        $totalRecords = $this->db->query($countSql)->fetchColumn();
        
        // Total filtré
        $filteredRecords = $totalRecords;
        if (!empty($search)) {
            $countSql = str_replace('p.*', 'COUNT(*)', $sql);
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
     * Crée une nouvelle promesse
     */
    public function create(array $data): bool {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO donation_pledges (
                        member_id, amount, type, campaign,
                        start_date, end_date, frequency,
                        notes, status, created_by
                    ) VALUES (
                        :member_id, :amount, :type, :campaign,
                        :start_date, :end_date, :frequency,
                        :notes, :status, :created_by
                    )";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'member_id' => $data['member_id'],
                'amount' => $data['amount'],
                'type' => $data['type'],
                'campaign' => $data['campaign'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'frequency' => $data['frequency'],
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'] ?? 'active',
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
     * Met à jour une promesse
     */
    public function update(int $id, array $data): bool {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE donation_pledges SET
                        member_id = :member_id,
                        amount = :amount,
                        type = :type,
                        campaign = :campaign,
                        start_date = :start_date,
                        end_date = :end_date,
                        frequency = :frequency,
                        notes = :notes,
                        status = :status
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'id' => $id,
                'member_id' => $data['member_id'],
                'amount' => $data['amount'],
                'type' => $data['type'],
                'campaign' => $data['campaign'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'frequency' => $data['frequency'],
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'] ?? 'active'
            ]);

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Récupère les promesses actives d'un membre
     */
    public function getMemberActivePledges(int $memberId): array {
        $sql = "SELECT p.*,
                       (SELECT SUM(amount) FROM pledge_payments WHERE pledge_id = p.id AND status = 'completed') as paid_amount
                FROM donation_pledges p
                WHERE member_id = ?
                AND status = 'active'
                ORDER BY start_date DESC";
        
        return $this->db->query($sql, [$memberId])->fetchAll();
    }

    /**
     * Récupère les promesses qui nécessitent un paiement
     */
    public function getDuePledges(): array {
        $sql = "SELECT p.*,
                       CONCAT(m.first_name, ' ', m.last_name) as member_name,
                       (SELECT SUM(amount) FROM pledge_payments WHERE pledge_id = p.id AND status = 'completed') as paid_amount
                FROM donation_pledges p
                JOIN members m ON p.member_id = m.id
                WHERE p.status = 'active'
                AND (p.end_date IS NULL OR p.end_date >= CURRENT_DATE)
                AND EXISTS (
                    SELECT 1
                    FROM pledge_payments pp
                    WHERE pp.pledge_id = p.id
                    GROUP BY pp.pledge_id
                    HAVING SUM(pp.amount) < p.amount
                )
                ORDER BY p.start_date";

        return $this->db->query($sql)->fetchAll();
    }
}
