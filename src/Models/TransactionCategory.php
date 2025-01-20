<?php
namespace Models;

use Core\Model;

class TransactionCategory extends Model {
    protected string $table = 'transaction_categories';

    /**
     * Récupère toutes les catégories par type
     */
    public function getAllByType(string $type): array {
        $sql = "SELECT * FROM transaction_categories WHERE type = ? ORDER BY name";
        return $this->db->query($sql, [$type])->fetchAll();
    }

    /**
     * Récupère une catégorie avec ses statistiques
     */
    public function getWithStats(int $id): ?array {
        $sql = "SELECT 
                    tc.*,
                    COUNT(t.id) as transaction_count,
                    SUM(CASE WHEN t.status = 'completed' THEN t.amount ELSE 0 END) as total_amount
                FROM transaction_categories tc
                LEFT JOIN transactions t ON tc.id = t.category_id
                WHERE tc.id = ?
                GROUP BY tc.id";
        
        return $this->db->query($sql, [$id])->fetch() ?: null;
    }

    /**
     * Crée une nouvelle catégorie
     */
    public function create(array $data): bool {
        $sql = "INSERT INTO transaction_categories (
                    name, type, description, created_by
                ) VALUES (
                    :name, :type, :description, :created_by
                )";

        return $this->db->prepare($sql)->execute([
            'name' => $data['name'],
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
            'created_by' => $data['created_by']
        ]);
    }

    /**
     * Met à jour une catégorie
     */
    public function update(int $id, array $data): bool {
        $sql = "UPDATE transaction_categories SET
                    name = :name,
                    type = :type,
                    description = :description
                WHERE id = :id";

        return $this->db->prepare($sql)->execute([
            'id' => $id,
            'name' => $data['name'],
            'type' => $data['type'],
            'description' => $data['description'] ?? null
        ]);
    }

    /**
     * Vérifie si une catégorie peut être supprimée
     */
    public function canDelete(int $id): bool {
        $sql = "SELECT COUNT(*) FROM transactions WHERE category_id = ?";
        return $this->db->query($sql, [$id])->fetchColumn() === 0;
    }
}
