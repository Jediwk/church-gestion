<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Export;
use PDO;

class FinanceController {
    private $db;

    public function __construct() {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }
        $this->db = Database::getInstance()->getConnection();
    }

    public function create() {
        try {
            // Récupérer les types de finances
            $stmt = $this->db->query('SELECT * FROM finance_types ORDER BY category, name');
            $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return View::render('finances/create', [
                'title' => 'Nouvelle Transaction',
                'types' => $types
            ]);
        } catch (\Exception $e) {
            // Log l'erreur
            error_log($e->getMessage());
            header('Location: /finances');
            exit;
        }
    }

    public function index() {
        try {
            // Récupérer les filtres
            $year = $_GET['year'] ?? date('Y');
            $month = $_GET['month'] ?? '';
            $type = $_GET['type'] ?? '';
            $category = $_GET['category'] ?? '';

            // Construction de la requête de base
            $query = '
                SELECT 
                    f.*,
                    ft.name as type_name,
                    ft.category
                FROM finances f
                JOIN finance_types ft ON f.type_id = ft.id
                WHERE 1=1
            ';
            $params = [];

            // Ajouter les conditions de filtrage
            if ($year) {
                $query .= ' AND strftime("%Y", f.date) = ?';
                $params[] = $year;
            }
            if ($month) {
                $query .= ' AND strftime("%m", f.date) = ?';
                $params[] = str_pad($month, 2, '0', STR_PAD_LEFT);
            }
            if ($type) {
                $query .= ' AND f.type_id = ?';
                $params[] = $type;
            }
            if ($category) {
                $query .= ' AND ft.category = ?';
                $params[] = $category;
            }

            $query .= ' ORDER BY f.date DESC, f.created_at DESC';

            // Exécuter la requête
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Statistiques
            $statsQuery = '
                SELECT 
                    SUM(CASE WHEN ft.category = "Entrée" THEN f.amount ELSE 0 END) as total_income,
                    SUM(CASE WHEN ft.category = "Sortie" THEN f.amount ELSE 0 END) as total_expense,
                    COUNT(*) as total_transactions
                FROM finances f
                JOIN finance_types ft ON f.type_id = ft.id
                WHERE 1=1
            ';
            
            // Réinitialiser les paramètres pour les statistiques
            $statsParams = [];
            
            if ($year) {
                $statsQuery .= ' AND strftime("%Y", f.date) = ?';
                $statsParams[] = $year;
            }
            if ($month) {
                $statsQuery .= ' AND strftime("%m", f.date) = ?';
                $statsParams[] = str_pad($month, 2, '0', STR_PAD_LEFT);
            }
            if ($type) {
                $statsQuery .= ' AND f.type_id = ?';
                $statsParams[] = $type;
            }
            if ($category) {
                $statsQuery .= ' AND ft.category = ?';
                $statsParams[] = $category;
            }

            $stmt = $this->db->prepare($statsQuery);
            $stmt->execute($statsParams);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Statistiques par type
            $typeStatsQuery = '
                SELECT 
                    ft.id,
                    ft.name,
                    ft.category,
                    SUM(f.amount) as total,
                    COUNT(*) as count
                FROM finances f
                JOIN finance_types ft ON f.type_id = ft.id
                WHERE 1=1
            ';
            
            // Réinitialiser les paramètres pour les statistiques par type
            $typeStatsParams = [];
            
            if ($year) {
                $typeStatsQuery .= ' AND strftime("%Y", f.date) = ?';
                $typeStatsParams[] = $year;
            }
            if ($month) {
                $typeStatsQuery .= ' AND strftime("%m", f.date) = ?';
                $typeStatsParams[] = str_pad($month, 2, '0', STR_PAD_LEFT);
            }
            if ($type) {
                $typeStatsQuery .= ' AND f.type_id = ?';
                $typeStatsParams[] = $type;
            }
            if ($category) {
                $typeStatsQuery .= ' AND ft.category = ?';
                $typeStatsParams[] = $category;
            }
            
            $typeStatsQuery .= ' GROUP BY ft.id, ft.name, ft.category ORDER BY ft.category, total DESC';
            
            $stmt = $this->db->prepare($typeStatsQuery);
            $stmt->execute($typeStatsParams);
            $typeStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Types de finances pour le filtre
            $stmt = $this->db->query('SELECT * FROM finance_types ORDER BY category, name');
            $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return View::render('finances/index', [
                'title' => 'Gestion des Finances',
                'transactions' => $transactions,
                'types' => $types,
                'stats' => $stats,
                'typeStats' => $typeStats,
                'filters' => [
                    'year' => $year,
                    'month' => $month,
                    'type' => $type,
                    'category' => $category
                ]
            ]);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            header('Location: /dashboard');
            exit;
        }
    }

    public function store() {
        try {
            $type_id = $_POST['type_id'] ?? '';
            $amount = $_POST['amount'] ?? '';
            $date = $_POST['date'] ?? date('Y-m-d');
            $description = $_POST['description'] ?? '';
            $reference = $_POST['reference'] ?? '';

            // Validation
            if (empty($type_id) || empty($amount) || empty($date)) {
                throw new \Exception("Tous les champs obligatoires doivent être remplis.");
            }

            // Vérifier que le type existe
            $stmt = $this->db->prepare('SELECT id, category FROM finance_types WHERE id = ?');
            $stmt->execute([$type_id]);
            $type = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$type) {
                throw new \Exception("Type de finance invalide.");
            }

            // Générer une référence si non fournie
            if (empty($reference)) {
                $reference = 'TR-' . date('Ymd-') . substr(uniqid(), -4);
            }

            // Insérer la transaction
            $stmt = $this->db->prepare('
                INSERT INTO finances (type_id, amount, date, description, reference)
                VALUES (?, ?, ?, ?, ?)
            ');
            $stmt->execute([$type_id, $amount, $date, $description, $reference]);
            $transactionId = $this->db->lastInsertId();

            // Générer et télécharger le reçu si demandé
            if (isset($_POST['generate_receipt'])) {
                $stmt = $this->db->prepare('
                    SELECT f.*, ft.name as type_name, ft.category
                    FROM finances f
                    JOIN finance_types ft ON f.type_id = ft.id
                    WHERE f.id = ?
                ');
                $stmt->execute([$transactionId]);
                $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $html = Export::generateReceipt($transaction);
                Export::toPDF($html, "recu_" . $transaction['reference'] . ".pdf");
                exit;
            }

            $_SESSION['success'] = "Transaction enregistrée avec succès.";
            header('Location: /finances');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /finances/create');
            exit;
        }
    }

    public function edit($id) {
        try {
            if (!$id) {
                throw new \Exception("ID de la transaction non spécifié");
            }

            // Récupérer la transaction
            $stmt = $this->db->prepare('
                SELECT f.id, f.type_id, f.amount, f.date, f.description, f.reference, ft.name as type_name, ft.category
                FROM finances f
                JOIN finance_types ft ON f.type_id = ft.id
                WHERE f.id = ?
            ');
            $stmt->execute([$id]);
            $finance = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$finance) {
                throw new \Exception("Transaction introuvable");
            }

            // Récupérer les types de finances
            $stmt = $this->db->query('SELECT * FROM finance_types ORDER BY category, name');
            $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return View::render('finances/edit', [
                'title' => 'Modifier la transaction',
                'finance' => $finance,
                'types' => $types
            ]);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            header('Location: /finances');
            exit;
        }
    }

    public function update($id) {
        try {
            if (!$id) {
                throw new \Exception("ID de la transaction non spécifié");
            }

            $type_id = $_POST['type_id'] ?? '';
            $amount = $_POST['amount'] ?? '';
            $date = $_POST['date'] ?? '';
            $description = $_POST['description'] ?? '';
            $reference = $_POST['reference'] ?? '';

            // Validation
            if (empty($type_id) || empty($amount) || empty($date)) {
                throw new \Exception("Tous les champs obligatoires doivent être remplis");
            }

            // Vérifier que la transaction existe
            $stmt = $this->db->prepare('SELECT id FROM finances WHERE id = ?');
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                throw new \Exception("Transaction introuvable");
            }

            // Vérifier que le type existe
            $stmt = $this->db->prepare('SELECT id FROM finance_types WHERE id = ?');
            $stmt->execute([$type_id]);
            if (!$stmt->fetch()) {
                throw new \Exception("Type de finance invalide");
            }

            // Mettre à jour la transaction
            $stmt = $this->db->prepare('
                UPDATE finances 
                SET type_id = ?, 
                    amount = ?, 
                    date = ?, 
                    description = ?, 
                    reference = ?
                WHERE id = ?
            ');
            $stmt->execute([$type_id, $amount, $date, $description, $reference, $id]);

            // Générer un nouveau reçu si demandé
            if (isset($_POST['generate_receipt'])) {
                $stmt = $this->db->prepare('
                    SELECT f.id, f.type_id, f.amount, f.date, f.description, f.reference, ft.name as type_name, ft.category
                    FROM finances f
                    JOIN finance_types ft ON f.type_id = ft.id
                    WHERE f.id = ?
                ');
                $stmt->execute([$id]);
                $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $html = Export::generateReceipt($transaction);
                Export::toPDF($html, "recu_" . $transaction['reference'] . ".pdf");
                exit;
            }

            $_SESSION['success'] = "Transaction mise à jour avec succès";
            header('Location: /finances');
            exit;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            header('Location: /finances/edit/' . $id);
            exit;
        }
    }

    public function delete($id) {
        try {
            if (!$id) {
                throw new \Exception("ID de la transaction non spécifié");
            }

            // Vérifier que la transaction existe
            $stmt = $this->db->prepare('SELECT * FROM finances WHERE id = ?');
            $stmt->execute([$id]);
            $finance = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$finance) {
                throw new \Exception("Transaction introuvable");
            }

            // Supprimer la transaction
            $stmt = $this->db->prepare('DELETE FROM finances WHERE id = ?');
            $stmt->execute([$id]);

            $_SESSION['success'] = "Transaction supprimée avec succès";
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $_SESSION['error'] = "Erreur lors de la suppression de la transaction : " . $e->getMessage();
        }
        
        header('Location: /finances');
        exit;
    }

    public function generateReceipt($id) {
        try {
            if (!$id) {
                throw new \Exception("ID de la transaction non spécifié");
            }

            // Récupérer les détails de la transaction
            $stmt = $this->db->prepare('
                SELECT f.id, f.type_id, f.amount, f.date, f.description, f.reference, ft.name as type_name, ft.category
                FROM finances f
                JOIN finance_types ft ON f.type_id = ft.id
                WHERE f.id = ?
            ');
            $stmt->execute([$id]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$transaction) {
                throw new \Exception("Transaction introuvable");
            }

            // Si pas de référence, en générer une
            if (empty($transaction['reference'])) {
                $transaction['reference'] = 'TR-' . date('Ymd-') . str_pad($transaction['id'], 4, '0', STR_PAD_LEFT);
                
                // Mettre à jour la référence dans la base de données
                $stmt = $this->db->prepare('UPDATE finances SET reference = ? WHERE id = ?');
                $stmt->execute([$transaction['reference'], $id]);
            }

            // Générer le reçu
            $html = Export::generateReceipt($transaction);
            
            // Envoyer le PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="recu_' . $transaction['reference'] . '.pdf"');
            
            // Générer le PDF
            Export::toPDF($html, "recu_" . $transaction['reference'] . ".pdf");
            exit;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $_SESSION['error'] = "Erreur lors de la génération du reçu : " . $e->getMessage();
            header('Location: /finances');
            exit;
        }
    }

    public function storeType() {
        try {
            // Validation des données
            $name = $_POST['name'] ?? '';
            $category = $_POST['category'] ?? '';
            $description = $_POST['description'] ?? '';

            if (empty($name) || empty($category)) {
                throw new \Exception("Le nom et la catégorie sont obligatoires");
            }

            if (!in_array($category, ['Entrée', 'Sortie'])) {
                throw new \Exception("Catégorie invalide");
            }

            // Vérifier si le type existe déjà
            $stmt = $this->db->prepare('SELECT id FROM finance_types WHERE name = ? AND category = ?');
            $stmt->execute([$name, $category]);
            if ($stmt->fetch()) {
                throw new \Exception("Ce type existe déjà");
            }

            // Insérer le nouveau type
            $stmt = $this->db->prepare('
                INSERT INTO finance_types (name, category, description)
                VALUES (?, ?, ?)
            ');
            $stmt->execute([$name, $category, $description]);

            // Récupérer l'ID du nouveau type
            $id = $this->db->lastInsertId();

            // Retourner la réponse
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'type' => [
                    'id' => $id,
                    'name' => $name,
                    'category' => $category,
                    'description' => $description
                ]
            ]);
            exit;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    public function reports() {
        try {
            // Debug
            error_log("Début de la méthode reports");
            
            // Récupérer les paramètres
            $report_type = $_GET['report_type'] ?? 'monthly';
            $month = $_GET['month'] ?? date('Y-m');
            $year = $_GET['year'] ?? date('Y');
            $start_date = $_GET['start_date'] ?? date('Y-m-01');
            $end_date = $_GET['end_date'] ?? date('Y-m-t');
            $export = $_GET['export'] ?? null;

            error_log("Paramètres récupérés: " . json_encode([
                'report_type' => $report_type,
                'month' => $month,
                'year' => $year,
                'start_date' => $start_date,
                'end_date' => $end_date
            ]));

            // Construire la requête de base
            $query = '
                SELECT f.*, ft.name as type_name, ft.category
                FROM finances f
                JOIN finance_types ft ON f.type_id = ft.id
                WHERE 1=1
            ';
            $params = [];

            // Appliquer les filtres selon le type de rapport
            switch ($report_type) {
                case 'monthly':
                    $query .= ' AND strftime("%Y-%m", f.date) = ?';
                    $params[] = $month;
                    break;
                case 'annual':
                    $query .= ' AND strftime("%Y", f.date) = ?';
                    $params[] = $year;
                    break;
                case 'custom':
                    $query .= ' AND f.date BETWEEN ? AND ?';
                    $params[] = $start_date;
                    $params[] = $end_date;
                    break;
            }

            $query .= ' ORDER BY f.date DESC';
            error_log("Requête SQL: " . $query);
            error_log("Paramètres: " . json_encode($params));

            // Exécuter la requête
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $transactions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            error_log("Nombre de transactions: " . count($transactions));

            // Calculer les totaux
            $total_income = 0;
            $total_expense = 0;
            foreach ($transactions as $transaction) {
                if ($transaction['category'] === 'Entrée') {
                    $total_income += $transaction['amount'];
                } else {
                    $total_expense += $transaction['amount'];
                }
            }
            $balance = $total_income - $total_expense;
            error_log("Totaux calculés: " . json_encode([
                'total_income' => $total_income,
                'total_expense' => $total_expense,
                'balance' => $balance
            ]));

            // Statistiques par type
            $type_stats = [];
            $stmt = $this->db->prepare('
                SELECT ft.name as type_name, SUM(f.amount) as total
                FROM finances f
                JOIN finance_types ft ON f.type_id = ft.id
                WHERE ' . substr($query, strpos($query, 'WHERE') + 6, strpos($query, 'ORDER') - strpos($query, 'WHERE') - 6) . '
                GROUP BY ft.name
                ORDER BY total DESC
            ');
            $stmt->execute($params);
            $type_stats = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            error_log("Statistiques par type: " . json_encode($type_stats));

            // Statistiques temporelles
            $time_format = $report_type === 'annual' ? '%Y-%m' : '%Y-%m-%d';
            $stmt = $this->db->prepare('
                SELECT 
                    strftime(?, f.date) as date,
                    SUM(CASE WHEN ft.category = "Entrée" THEN f.amount ELSE 0 END) as income,
                    SUM(CASE WHEN ft.category = "Sortie" THEN f.amount ELSE 0 END) as expense
                FROM finances f
                JOIN finance_types ft ON f.type_id = ft.id
                WHERE ' . substr($query, strpos($query, 'WHERE') + 6, strpos($query, 'ORDER') - strpos($query, 'WHERE') - 6) . '
                GROUP BY strftime(?, f.date)
                ORDER BY date
            ');
            $stmt->execute(array_merge([$time_format], $params, [$time_format]));
            $time_stats = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            error_log("Statistiques temporelles: " . json_encode($time_stats));

            // Construire l'URL courante pour l'export
            $current_url = '/finances/reports?' . http_build_query([
                'report_type' => $report_type,
                'month' => $month,
                'year' => $year,
                'start_date' => $start_date,
                'end_date' => $end_date
            ]);

            // Gérer l'export si demandé
            if ($export) {
                error_log("Export demandé: " . $export);
                $data = [];
                foreach ($transactions as $transaction) {
                    $data[] = [
                        'Date' => date('d/m/Y', strtotime($transaction['date'])),
                        'Type' => $transaction['type_name'],
                        'Description' => $transaction['description'],
                        'Entrée' => $transaction['category'] === 'Entrée' ? $transaction['amount'] : '',
                        'Sortie' => $transaction['category'] === 'Sortie' ? $transaction['amount'] : '',
                        'Référence' => $transaction['reference']
                    ];
                }

                $title = 'Rapport financier - ';
                switch ($report_type) {
                    case 'monthly':
                        $title .= date('F Y', strtotime($month . '-01'));
                        break;
                    case 'annual':
                        $title .= $year;
                        break;
                    case 'custom':
                        $title .= date('d/m/Y', strtotime($start_date)) . ' au ' . date('d/m/Y', strtotime($end_date));
                        break;
                }

                if ($export === 'excel') {
                    Export::toExcel($data, $title);
                } else {
                    Export::toPDF($this->generateReportHtml($title, $data, $total_income, $total_expense), $title);
                }
            }

            error_log("Rendu de la vue avec les données: " . json_encode([
                'report_type' => $report_type,
                'month' => $month,
                'year' => $year,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'current_url' => $current_url
            ]));

            // Afficher la vue
            return View::render('finances/reports', [
                'transactions' => $transactions,
                'total_income' => $total_income,
                'total_expense' => $total_expense,
                'balance' => $balance,
                'type_stats' => $type_stats,
                'time_stats' => $time_stats,
                'report_type' => $report_type,
                'month' => $month,
                'year' => $year,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'current_url' => $current_url
            ]);
        } catch (\Exception $e) {
            error_log("Erreur dans reports: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $_SESSION['error'] = "Une erreur est survenue lors de la génération du rapport.";
            header('Location: /finances');
            exit;
        }
    }

    private function generateReportHtml($title, $data, $total_income, $total_expense) {
        $html = '
        <div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
            <h1 style="text-align: center; color: #2c3e50; margin-bottom: 30px;">' . htmlspecialchars($title) . '</h1>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
                <div style="flex: 1; margin: 0 10px; background: #4CAF50; color: white; padding: 20px; border-radius: 5px;">
                    <h3>Total Entrées</h3>
                    <p style="font-size: 24px;">' . number_format($total_income, 0, ',', ' ') . ' FCFA</p>
                </div>
                <div style="flex: 1; margin: 0 10px; background: #E91E63; color: white; padding: 20px; border-radius: 5px;">
                    <h3>Total Sorties</h3>
                    <p style="font-size: 24px;">' . number_format($total_expense, 0, ',', ' ') . ' FCFA</p>
                </div>
                <div style="flex: 1; margin: 0 10px; background: #2196F3; color: white; padding: 20px; border-radius: 5px;">
                    <h3>Balance</h3>
                    <p style="font-size: 24px;">' . number_format($total_income - $total_expense, 0, ',', ' ') . ' FCFA</p>
                </div>
            </div>
            
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background-color: #f5f5f5;">
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Date</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Type</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Description</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">Entrée</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">Sortie</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Référence</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($data as $row) {
            $html .= '
                <tr>
                    <td style="padding: 12px; border: 1px solid #ddd;">' . $row['Date'] . '</td>
                    <td style="padding: 12px; border: 1px solid #ddd;">' . htmlspecialchars($row['Type']) . '</td>
                    <td style="padding: 12px; border: 1px solid #ddd;">' . htmlspecialchars($row['Description']) . '</td>
                    <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">' . ($row['Entrée'] ? number_format($row['Entrée'], 0, ',', ' ') : '') . '</td>
                    <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">' . ($row['Sortie'] ? number_format($row['Sortie'], 0, ',', ' ') : '') . '</td>
                    <td style="padding: 12px; border: 1px solid #ddd;">' . htmlspecialchars($row['Référence']) . '</td>
                </tr>';
        }
        
        $html .= '
                </tbody>
                <tfoot>
                    <tr style="background-color: #2c3e50; color: white;">
                        <th colspan="3" style="padding: 12px; border: 1px solid #ddd;">Total</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">' . number_format($total_income, 0, ',', ' ') . '</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">' . number_format($total_expense, 0, ',', ' ') . '</th>
                        <th style="padding: 12px; border: 1px solid #ddd;"></th>
                    </tr>
                </tfoot>
            </table>
            
            <div style="text-align: center; margin-top: 30px; color: #7f8c8d;">
                <p>Rapport généré le ' . date('d/m/Y à H:i') . '</p>
            </div>
        </div>';

        return $html;
    }

    // ... autres méthodes existantes ...
}
