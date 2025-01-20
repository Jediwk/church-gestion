<?php
namespace App\Models;

use Core\Model;

class Family extends Model {
    protected string $table = 'families';

    /**
     * Crée une nouvelle famille avec ses membres
     */
    public function createWithMembers(array $familyData, array $members): int {
        $this->db->beginTransaction();

        try {
            // Crée la famille
            $familyId = $this->create($familyData);

            // Ajoute les membres
            foreach ($members as $member) {
                $this->db->insert('family_relationships', [
                    'family_id' => $familyId,
                    'member_id' => $member['id'],
                    'relationship_type' => $member['relationship_type'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            $this->db->commit();
            return $familyId;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Obtient les détails d'une famille avec ses membres
     */
    public function getWithMembers(int $id): array {
        $sql = "SELECT f.*, 
                GROUP_CONCAT(
                    CONCAT_WS('|', 
                        m.id, 
                        m.first_name, 
                        m.last_name, 
                        fr.relationship_type
                    )
                ) as members
                FROM {$this->table} f
                LEFT JOIN family_relationships fr ON f.id = fr.family_id
                LEFT JOIN members m ON fr.member_id = m.id
                WHERE f.id = :id
                GROUP BY f.id";

        $family = $this->db->query($sql, ['id' => $id])->fetch();
        
        if ($family) {
            $family['members'] = [];
            $membersData = explode(',', $family['members']);
            foreach ($membersData as $memberData) {
                list($id, $firstName, $lastName, $relationshipType) = explode('|', $memberData);
                $family['members'][] = [
                    'id' => $id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'relationship_type' => $relationshipType
                ];
            }
        }

        return $family;
    }

    /**
     * Met à jour une famille et ses relations
     */
    public function updateWithMembers(int $id, array $familyData, array $members): bool {
        $this->db->beginTransaction();

        try {
            // Met à jour la famille
            $this->update($id, $familyData);

            // Supprime les anciennes relations
            $this->db->delete('family_relationships', ['family_id' => $id]);

            // Ajoute les nouvelles relations
            foreach ($members as $member) {
                $this->db->insert('family_relationships', [
                    'family_id' => $id,
                    'member_id' => $member['id'],
                    'relationship_type' => $member['relationship_type'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Supprime une famille et ses relations
     */
    public function delete(int $id): bool {
        $this->db->beginTransaction();

        try {
            // Supprime les relations
            $this->db->delete('family_relationships', ['family_id' => $id]);

            // Supprime la famille
            parent::delete($id);

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Liste toutes les familles avec le nombre de membres
     */
    public function listWithMemberCount(): array {
        $sql = "SELECT f.*, 
                COUNT(fr.member_id) as member_count
                FROM {$this->table} f
                LEFT JOIN family_relationships fr ON f.id = fr.family_id
                GROUP BY f.id
                ORDER BY f.name";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Recherche des familles
     */
    public function search(string $term): array {
        $sql = "SELECT f.*, 
                COUNT(fr.member_id) as member_count
                FROM {$this->table} f
                LEFT JOIN family_relationships fr ON f.id = fr.family_id
                WHERE f.name LIKE :term
                OR f.address LIKE :term
                GROUP BY f.id
                ORDER BY f.name";

        return $this->db->query($sql, ['term' => "%{$term}%"])->fetchAll();
    }
}
