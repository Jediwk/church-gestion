<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Auth;
use App\Core\Database;
use PDO;

class MemberController {
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
            // Récupérer tous les membres avec leurs familles
            $stmt = $this->db->query('
                SELECT 
                    m.*,
                    f.name as family_name
                FROM members m
                LEFT JOIN families f ON m.family_id = f.id
                ORDER BY m.last_name, m.first_name
            ');
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Statistiques
            $stmt = $this->db->query('
                SELECT 
                    COUNT(*) as total_members,
                    SUM(CASE WHEN gender = "M" THEN 1 ELSE 0 END) as male_count,
                    SUM(CASE WHEN gender = "F" THEN 1 ELSE 0 END) as female_count
                FROM members
            ');
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            return View::render('members/index', [
                'title' => 'Gestion des membres',
                'members' => $members,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Une erreur est survenue lors du chargement des membres.";
            header('Location: /dashboard');
            exit;
        }
    }

    public function create() {
        try {
            // Récupérer toutes les familles pour le formulaire
            $stmt = $this->db->query('SELECT id, name FROM families ORDER BY name');
            $families = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return View::render('members/create', [
                'title' => 'Nouveau membre',
                'families' => $families
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Une erreur est survenue.";
            header('Location: /members');
            exit;
        }
    }

    public function store() {
        try {
            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';
            $gender = $_POST['gender'] ?? '';
            $birthdate = $_POST['birthdate'] ?? null;
            $phone = $_POST['phone'] ?? '';
            $email = $_POST['email'] ?? '';
            $address = $_POST['address'] ?? '';
            $family_id = $_POST['family_id'] ?? null;
            $profession = $_POST['profession'] ?? '';

            // Validation
            if (empty($first_name) || empty($last_name)) {
                throw new \Exception('Le nom et le prénom sont requis');
            }

            if (!in_array($gender, ['M', 'F'])) {
                throw new \Exception('Genre invalide');
            }

            $stmt = $this->db->prepare('
                INSERT INTO members (
                    first_name, last_name, gender, birthdate, 
                    phone, email, address, family_id, profession
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $stmt->execute([
                $first_name, $last_name, $gender, $birthdate,
                $phone, $email, $address, 
                $family_id ?: null, $profession
            ]);

            $_SESSION['success'] = 'Membre ajouté avec succès';
            header('Location: /members');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur lors de l\'ajout du membre : ' . $e->getMessage();
            header('Location: /members/create');
            exit;
        }
    }

    public function edit($id) {
        try {
            // Récupérer le membre
            $stmt = $this->db->prepare('SELECT * FROM members WHERE id = ?');
            $stmt->execute([$id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$member) {
                $_SESSION['error'] = 'Membre non trouvé';
                header('Location: /members');
                exit;
            }

            // Récupérer toutes les familles
            $stmt = $this->db->query('SELECT id, name FROM families ORDER BY name');
            $families = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return View::render('members/edit', [
                'title' => 'Modifier le membre',
                'member' => $member,
                'families' => $families
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur lors du chargement du membre';
            header('Location: /members');
            exit;
        }
    }

    public function update($id) {
        try {
            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';
            $gender = $_POST['gender'] ?? '';
            $birthdate = $_POST['birthdate'] ?? null;
            $phone = $_POST['phone'] ?? '';
            $email = $_POST['email'] ?? '';
            $address = $_POST['address'] ?? '';
            $family_id = $_POST['family_id'] ?? null;
            $profession = $_POST['profession'] ?? '';

            // Validation
            if (empty($first_name) || empty($last_name)) {
                throw new \Exception('Le nom et le prénom sont requis');
            }

            if (!in_array($gender, ['M', 'F'])) {
                throw new \Exception('Genre invalide');
            }

            $stmt = $this->db->prepare('
                UPDATE members SET 
                    first_name = ?, last_name = ?, gender = ?, 
                    birthdate = ?, phone = ?, email = ?, 
                    address = ?, family_id = ?, profession = ?
                WHERE id = ?
            ');

            $stmt->execute([
                $first_name, $last_name, $gender,
                $birthdate, $phone, $email,
                $address, $family_id ?: null, $profession,
                $id
            ]);

            $_SESSION['success'] = 'Membre mis à jour avec succès';
            header('Location: /members');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur lors de la mise à jour du membre : ' . $e->getMessage();
            header("Location: /members/edit/$id");
            exit;
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare('DELETE FROM members WHERE id = ?');
            $stmt->execute([$id]);

            $_SESSION['success'] = 'Membre supprimé avec succès';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur lors de la suppression du membre';
        }

        header('Location: /members');
        exit;
    }
}
