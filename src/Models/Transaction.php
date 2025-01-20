<?php
namespace App\Models;

use Core\Model;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Transaction extends Model {
    protected string $table = 'transactions';

    /**
     * Récupère les transactions avec les détails
     */
    public function getWithDetails(array $filters = []): array {
        $sql = "SELECT t.*, 
                tc.name as category_name, 
                CONCAT(m.first_name, ' ', m.last_name) as member_name,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM {$this->table} t
                LEFT JOIN transaction_categories tc ON t.category_id = tc.id
                LEFT JOIN members m ON t.member_id = m.id
                LEFT JOIN users u ON t.created_by = u.id
                WHERE 1=1";
        $params = [];

        // Filtres
        if (!empty($filters['start_date'])) {
            $sql .= " AND t.transaction_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND t.transaction_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND t.type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND t.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['member_id'])) {
            $sql .= " AND t.member_id = :member_id";
            $params['member_id'] = $filters['member_id'];
        }

        $sql .= " ORDER BY t.transaction_date DESC, t.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Génère un rapport financier
     */
    public function generateReport(string $startDate, string $endDate): array {
        // Résumé des revenus par catégorie
        $incomeByCategory = $this->db->query(
            "SELECT tc.name, SUM(t.amount) as total
            FROM transactions t
            JOIN transaction_categories tc ON t.category_id = tc.id
            WHERE t.type = 'income'
            AND t.transaction_date BETWEEN :start AND :end
            GROUP BY tc.id, tc.name
            ORDER BY total DESC",
            ['start' => $startDate, 'end' => $endDate]
        )->fetchAll();

        // Résumé des dépenses par catégorie
        $expensesByCategory = $this->db->query(
            "SELECT tc.name, SUM(t.amount) as total
            FROM transactions t
            JOIN transaction_categories tc ON t.category_id = tc.id
            WHERE t.type = 'expense'
            AND t.transaction_date BETWEEN :start AND :end
            GROUP BY tc.id, tc.name
            ORDER BY total DESC",
            ['start' => $startDate, 'end' => $endDate]
        )->fetchAll();

        // Totaux
        $totals = $this->db->query(
            "SELECT 
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses,
                SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as balance
            FROM transactions
            WHERE transaction_date BETWEEN :start AND :end",
            ['start' => $startDate, 'end' => $endDate]
        )->fetch();

        // Évolution mensuelle
        $monthlyTrends = $this->db->query(
            "SELECT 
                DATE_FORMAT(transaction_date, '%Y-%m') as month,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expenses
            FROM transactions
            WHERE transaction_date BETWEEN :start AND :end
            GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
            ORDER BY month",
            ['start' => $startDate, 'end' => $endDate]
        )->fetchAll();

        return [
            'income_by_category' => $incomeByCategory,
            'expenses_by_category' => $expensesByCategory,
            'totals' => $totals,
            'monthly_trends' => $monthlyTrends
        ];
    }

    /**
     * Exporte les transactions vers Excel
     */
    public function exportToExcel(array $transactions): string {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes
        $headers = [
            'Date', 'Type', 'Catégorie', 'Membre', 'Montant', 
            'Méthode de paiement', 'Référence', 'Description', 'Créé par'
        ];
        $sheet->fromArray([$headers], null, 'A1');

        // Données
        $row = 2;
        foreach ($transactions as $transaction) {
            $sheet->fromArray([[
                $transaction['transaction_date'],
                $transaction['type'] === 'income' ? 'Revenu' : 'Dépense',
                $transaction['category_name'],
                $transaction['member_name'],
                $transaction['amount'],
                $this->getPaymentMethodLabel($transaction['payment_method']),
                $transaction['reference_number'],
                $transaction['description'],
                $transaction['created_by_name']
            ]], null, "A{$row}");
            $row++;
        }

        // Formatage
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Formatage des montants
        $sheet->getStyle('E2:E' . ($row-1))->getNumberFormat()
            ->setFormatCode('#,##0.00 €');

        // Style des en-têtes
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle('A1:I1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('CCCCCC');

        // Sauvegarde
        $filename = 'transactions_' . date('Y-m-d_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $path = UPLOADS_PATH . '/' . $filename;
        $writer->save($path);

        return $filename;
    }

    /**
     * Obtient les statistiques pour le dashboard
     */
    public function getDashboardStats(): array {
        // Revenus et dépenses du mois en cours
        $currentMonth = $this->db->query(
            "SELECT 
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expenses
            FROM transactions
            WHERE MONTH(transaction_date) = MONTH(CURRENT_DATE())
            AND YEAR(transaction_date) = YEAR(CURRENT_DATE())"
        )->fetch();

        // Top 5 des donateurs du mois
        $topDonors = $this->db->query(
            "SELECT m.first_name, m.last_name, SUM(t.amount) as total
            FROM transactions t
            JOIN members m ON t.member_id = m.id
            WHERE t.type = 'income'
            AND MONTH(t.transaction_date) = MONTH(CURRENT_DATE())
            AND YEAR(t.transaction_date) = YEAR(CURRENT_DATE())
            GROUP BY m.id
            ORDER BY total DESC
            LIMIT 5"
        )->fetchAll();

        // Évolution sur les 12 derniers mois
        $yearlyTrends = $this->db->query(
            "SELECT 
                DATE_FORMAT(transaction_date, '%Y-%m') as month,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expenses
            FROM transactions
            WHERE transaction_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
            ORDER BY month"
        )->fetchAll();

        return [
            'current_month' => $currentMonth,
            'top_donors' => $topDonors,
            'yearly_trends' => $yearlyTrends
        ];
    }

    /**
     * Traduit le mode de paiement
     */
    private function getPaymentMethodLabel(string $method): string {
        return [
            'cash' => 'Espèces',
            'check' => 'Chèque',
            'transfer' => 'Virement',
            'other' => 'Autre'
        ][$method] ?? $method;
    }
}
