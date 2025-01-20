<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Auth;
use App\Core\Database;
use PDO;

class FamilyController {
    private $db;

    public function __construct() {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }
        $this->db = Database::getInstance();
    }

    public function index() {
        try {
            // Récupérer toutes les familles avec le nombre de membres
            $stmt = $this->db->query('
                SELECT 
                    f.*,
                    COUNT(DISTINCT m.id) as member_count,
                    GROUP_CONCAT(
                        COALESCE(m.id, "") || "|" ||
                        COALESCE(m.first_name, "") || "|" ||
                        COALESCE(m.last_name, "") || "|" ||
                        COALESCE(m.gender, "") || "|" ||
                        COALESCE(m.birthdate, "") || "|" ||
                        COALESCE(m.profession, "")
                    ) as members_data
                FROM families f
                LEFT JOIN members m ON f.id = m.family_id
                GROUP BY f.id, f.name, f.address, f.phone, f.email, f.created_at, f.updated_at
                ORDER BY f.name
            ');
            $families = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Traiter les données des membres pour chaque famille
            foreach ($families as &$family) {
                $family['members'] = [];
                if (!empty($family['members_data'])) {
                    $members = explode(',', $family['members_data']);
                    foreach ($members as $member) {
                        if (empty($member)) continue;
                        $memberData = explode('|', $member);
                        if (count($memberData) === 6) {
                            $family['members'][] = [
                                'id' => $memberData[0],
                                'first_name' => $memberData[1],
                                'last_name' => $memberData[2],
                                'gender' => $memberData[3],
                                'birthdate' => $memberData[4],
                                'profession' => $memberData[5]
                            ];
                        }
                    }
                }
                unset($family['members_data']);
            }

            // Statistiques
            $stmt = $this->db->query('
                SELECT 
                    COUNT(DISTINCT f.id) as total_families,
                    COUNT(DISTINCT CASE WHEN m.id IS NOT NULL THEN f.id END) as active_families
                FROM families f
                LEFT JOIN members m ON f.id = m.family_id
            ');
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            return View::render('families/index', [
                'title' => 'Gestion des familles',
                'families' => $families,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            error_log("Erreur dans FamilyController::index - " . $e->getMessage());
            $_SESSION['error'] = "Une erreur est survenue lors du chargement des familles : " . $e->getMessage();
            header('Location: /dashboard');
            exit;
        }
    }

    public function create() {
        return View::render('families/create', [
            'title' => 'Nouvelle famille'
        ]);
    }

    public function store() {
        try {
            $name = $_POST['name'] ?? '';
            $address = $_POST['address'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $email = $_POST['email'] ?? '';

            // Validation
            if (empty($name)) {
                throw new \Exception('Le nom de la famille est requis');
            }

            $stmt = $this->db->prepare('
                INSERT INTO families (name, address, phone, email)
                VALUES (?, ?, ?, ?)
            ');

            $stmt->execute([$name, $address, $phone, $email]);

            $_SESSION['success'] = 'Famille ajoutée avec succès';
            header('Location: /families');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur lors de l\'ajout de la famille : ' . $e->getMessage();
            header('Location: /families/create');
            exit;
        }
    }

    public function edit($id) {
        try {
            // Récupérer la famille
            $stmt = $this->db->prepare('
                SELECT f.*, COUNT(m.id) as member_count
                FROM families f
                LEFT JOIN members m ON f.id = m.family_id
                WHERE f.id = ?
                GROUP BY f.id
            ');
            $stmt->execute([$id]);
            $family = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$family) {
                $_SESSION['error'] = 'Famille non trouvée';
                header('Location: /families');
                exit;
            }

            // Récupérer les membres de la famille
            $stmt = $this->db->prepare('
                SELECT id, first_name, last_name, gender
                FROM members
                WHERE family_id = ?
                ORDER BY last_name, first_name
            ');
            $stmt->execute([$id]);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return View::render('families/edit', [
                'title' => 'Modifier la famille',
                'family' => $family,
                'members' => $members
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur lors du chargement de la famille';
            header('Location: /families');
            exit;
        }
    }

    public function update($id) {
        try {
            $name = $_POST['name'] ?? '';
            $address = $_POST['address'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $email = $_POST['email'] ?? '';

            // Validation
            if (empty($name)) {
                throw new \Exception('Le nom de la famille est requis');
            }

            $stmt = $this->db->prepare('
                UPDATE families 
                SET name = ?, address = ?, phone = ?, email = ?
                WHERE id = ?
            ');

            $stmt->execute([$name, $address, $phone, $email, $id]);

            $_SESSION['success'] = 'Famille mise à jour avec succès';
            header('Location: /families');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur lors de la mise à jour de la famille : ' . $e->getMessage();
            header("Location: /families/edit/$id");
            exit;
        }
    }

    public function delete($id) {
        try {
            // Vérifier si la famille a des membres
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM members WHERE family_id = ?');
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                throw new \Exception('Cette famille ne peut pas être supprimée car elle a des membres');
            }

            $stmt = $this->db->prepare('DELETE FROM families WHERE id = ?');
            $stmt->execute([$id]);

            $_SESSION['success'] = 'Famille supprimée avec succès';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur : ' . $e->getMessage();
        }

        header('Location: /families');
        exit;
    }
}
