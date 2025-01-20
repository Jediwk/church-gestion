<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Auth;
use App\Core\Database;

class DashboardController {
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
            // Statistiques des finances du mois en cours
            $stmt = $this->db->query('
                SELECT 
                    SUM(CASE WHEN ft.category = "Entrée" THEN f.amount ELSE 0 END) as total_income,
                    SUM(CASE WHEN ft.category = "Sortie" THEN f.amount ELSE 0 END) as total_expense,
                    COUNT(*) as total_transactions
                FROM finances f
                JOIN finance_types ft ON f.type_id = ft.id
                WHERE strftime("%Y-%m", f.date) = strftime("%Y-%m", "now")
            ');
            $current_month_finances = $stmt->fetch();

            // Statistiques des finances de l'année en cours
            $stmt = $this->db->query('
                SELECT 
                    SUM(CASE WHEN ft.category = "Entrée" THEN f.amount ELSE 0 END) as total_income,
                    SUM(CASE WHEN ft.category = "Sortie" THEN f.amount ELSE 0 END) as total_expense,
                    COUNT(*) as total_transactions
                FROM finances f
                JOIN finance_types ft ON f.type_id = ft.id
                WHERE strftime("%Y", f.date) = strftime("%Y", "now")
            ');
            $current_year_finances = $stmt->fetch();

            // Évolution des finances sur les 12 derniers mois
            $stmt = $this->db->query('
                SELECT 
                    strftime("%Y-%m", f.date) as month,
                    SUM(CASE WHEN ft.category = "Entrée" THEN f.amount ELSE 0 END) as income,
                    SUM(CASE WHEN ft.category = "Sortie" THEN f.amount ELSE 0 END) as expense
                FROM finances f
                JOIN finance_types ft ON f.type_id = ft.id
                WHERE f.date >= date("now", "-12 months")
                GROUP BY strftime("%Y-%m", f.date)
                ORDER BY month ASC
            ');
            $monthly_stats = $stmt->fetchAll();

            // Top 5 des types de transactions (entrées)
            $stmt = $this->db->query('
                SELECT 
                    ft.name,
                    SUM(f.amount) as total
                FROM finances f
                JOIN finance_types ft ON f.type_id = ft.id
                WHERE ft.category = "Entrée"
                AND strftime("%Y", f.date) = strftime("%Y", "now")
                GROUP BY ft.id, ft.name
                ORDER BY total DESC
                LIMIT 5
            ');
            $top_income_types = $stmt->fetchAll();

            // Top 5 des types de transactions (sorties)
            $stmt = $this->db->query('
                SELECT 
                    ft.name,
                    SUM(f.amount) as total
                FROM finances f
                JOIN finance_types ft ON f.type_id = ft.id
                WHERE ft.category = "Sortie"
                AND strftime("%Y", f.date) = strftime("%Y", "now")
                GROUP BY ft.id, ft.name
                ORDER BY total DESC
                LIMIT 5
            ');
            $top_expense_types = $stmt->fetchAll();

            // Statistiques des membres
            $stmt = $this->db->query('
                SELECT 
                    COUNT(*) as total_members,
                    SUM(CASE WHEN gender = "M" THEN 1 ELSE 0 END) as male_count,
                    SUM(CASE WHEN gender = "F" THEN 1 ELSE 0 END) as female_count,
                    AVG(CASE 
                        WHEN birthdate IS NOT NULL 
                        THEN (julianday("now") - julianday(birthdate))/365.25 
                        ELSE NULL 
                    END) as avg_age
                FROM members
            ');
            $members = $stmt->fetch();

            // Répartition des membres par tranche d'âge
            $stmt = $this->db->query('
                SELECT 
                    CASE 
                        WHEN age < 18 THEN "0-17"
                        WHEN age < 30 THEN "18-29"
                        WHEN age < 45 THEN "30-44"
                        WHEN age < 60 THEN "45-59"
                        ELSE "60+"
                    END as age_group,
                    COUNT(*) as count
                FROM (
                    SELECT 
                        (julianday("now") - julianday(birthdate))/365.25 as age
                    FROM members
                    WHERE birthdate IS NOT NULL
                )
                GROUP BY age_group
                ORDER BY age_group
            ');
            $age_distribution = $stmt->fetchAll();

            // Statistiques des familles
            $stmt = $this->db->query('
                SELECT 
                    COUNT(*) as total_families,
                    AVG(member_count) as avg_members_per_family
                FROM (
                    SELECT f.id, COUNT(m.id) as member_count
                    FROM families f
                    LEFT JOIN members m ON m.family_id = f.id
                    GROUP BY f.id
                )
            ');
            $families = $stmt->fetch();

            // Dernières transactions
            $stmt = $this->db->query('
                SELECT f.*, ft.name as type_name, ft.category
                FROM finances f
                JOIN finance_types ft ON f.type_id = ft.id
                ORDER BY f.date DESC, f.id DESC
                LIMIT 5
            ');
            $recent_transactions = $stmt->fetchAll();

            // Derniers membres ajoutés
            $stmt = $this->db->query('
                SELECT *
                FROM members
                ORDER BY id DESC
                LIMIT 5
            ');
            $recent_members = $stmt->fetchAll();

            return View::render('dashboard/index', [
                'current_month_finances' => $current_month_finances ?: ['total_income' => 0, 'total_expense' => 0, 'total_transactions' => 0],
                'current_year_finances' => $current_year_finances ?: ['total_income' => 0, 'total_expense' => 0, 'total_transactions' => 0],
                'monthly_stats' => $monthly_stats ?: [],
                'top_income_types' => $top_income_types ?: [],
                'top_expense_types' => $top_expense_types ?: [],
                'members' => $members ?: ['total_members' => 0, 'male_count' => 0, 'female_count' => 0, 'avg_age' => 0],
                'age_distribution' => $age_distribution ?: [],
                'families' => $families ?: ['total_families' => 0, 'avg_members_per_family' => 0],
                'recent_transactions' => $recent_transactions ?: [],
                'recent_members' => $recent_members ?: []
            ]);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $_SESSION['error'] = "Une erreur est survenue lors du chargement du tableau de bord.";
            return View::render('dashboard/index', [
                'error' => $e->getMessage(),
                'current_month_finances' => ['total_income' => 0, 'total_expense' => 0, 'total_transactions' => 0],
                'current_year_finances' => ['total_income' => 0, 'total_expense' => 0, 'total_transactions' => 0],
                'monthly_stats' => [],
                'top_income_types' => [],
                'top_expense_types' => [],
                'members' => ['total_members' => 0, 'male_count' => 0, 'female_count' => 0, 'avg_age' => 0],
                'age_distribution' => [],
                'families' => ['total_families' => 0, 'avg_members_per_family' => 0],
                'recent_transactions' => [],
                'recent_members' => []
            ]);
        }
    }
}
