<?php

namespace App\Core;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Export {
    /**
     * Exporte les données au format CSV
     */
    public static function toCSV($data, $filename, $headers = []) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // BOM UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // En-têtes
        if (!empty($headers)) {
            fputcsv($output, $headers);
        }
        
        // Données
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
    }

    /**
     * Exporte les données au format Excel
     */
    public static function toExcel($data, $filename) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        if (empty($data)) {
            $sheet->setCellValue('A1', 'Aucune donnée disponible');
        } else {
            // En-têtes
            $headers = array_keys($data[0]);
            $col = 1;
            foreach ($headers as $header) {
                $sheet->setCellValueByColumnAndRow($col, 1, $header);
                $col++;
            }
            
            // Données
            $row = 2;
            foreach ($data as $rowData) {
                $col = 1;
                foreach ($rowData as $value) {
                    $sheet->setCellValueByColumnAndRow($col, $row, $value);
                    $col++;
                }
                $row++;
            }
            
            // Auto-dimensionner les colonnes
            foreach (range(1, count($headers)) as $col) {
                $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            }
        }
        
        // Créer le writer Excel
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        
        // Headers pour le téléchargement
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Exporte les données au format PDF
     */
    public static function toPDF($html, $filename) {
        // Comme nous n'avons pas de bibliothèque PDF, on va retourner le HTML formaté
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . htmlspecialchars($filename) . '</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 20px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                th, td {
                    padding: 10px;
                    border: 1px solid #ddd;
                }
                th {
                    background-color: #f5f5f5;
                }
                .print-button {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 10px 20px;
                    background-color: #007bff;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                }
                @media print {
                    .print-button {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            <button onclick="window.print()" class="print-button">Imprimer</button>
            ' . $html . '
        </body>
        </html>';
        exit;
    }

    /**
     * Génère un reçu au format HTML
     */
    public static function generateReceipt($transaction) {
        $html = '
        <div style="max-width: 800px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="color: #2c3e50;">Reçu de Transaction</h1>
                <p style="color: #7f8c8d;">Référence: ' . htmlspecialchars($transaction['reference']) . '</p>
            </div>
            
            <div style="margin-bottom: 30px;">
                <h2 style="color: #2c3e50;">Détails de la Transaction</h2>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>Type:</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">' . htmlspecialchars($transaction['type_name']) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>Montant:</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">' . number_format($transaction['amount'], 2, ',', ' ') . ' FCFA</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>Date:</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">' . date('d/m/Y', strtotime($transaction['date'])) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>Description:</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">' . htmlspecialchars($transaction['description']) . '</td>
                    </tr>
                </table>
            </div>
            
            <div style="text-align: center; margin-top: 50px; color: #7f8c8d;">
                <p>Ce reçu a été généré automatiquement le ' . date('d/m/Y à H:i') . '</p>
            </div>
        </div>';

        return $html;
    }

    /**
     * Génère un rapport financier au format HTML
     */
    public static function generateFinancialReport($data) {
        $html = '
        <div style="max-width: 1000px; margin: auto; padding: 20px;">
            <div style="text-align: center; margin-bottom: 30px;">
                <h1>Rapport Financier</h1>
                <p>Période: ' . $data['period'] . '</p>
            </div>
            
            <div style="margin-bottom: 30px;">
                <h2>Résumé</h2>
                <p><strong>Total des entrées:</strong> ' . number_format($data['total_income'], 0, ',', ' ') . ' FCFA</p>
                <p><strong>Total des sorties:</strong> ' . number_format($data['total_expense'], 0, ',', ' ') . ' FCFA</p>
                <p><strong>Solde:</strong> ' . number_format($data['balance'], 0, ',', ' ') . ' FCFA</p>
            </div>
            
            <div style="margin-bottom: 30px;">
                <h2>Détails par type</h2>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #ddd; padding: 8px;">Type</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">Catégorie</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">Montant</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        foreach ($data['type_stats'] as $stat) {
            $html .= '
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($stat['name']) . '</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($stat['category']) . '</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">' . number_format($stat['total'], 0, ',', ' ') . ' FCFA</td>
                        </tr>';
        }
        
        $html .= '
                    </tbody>
                </table>
            </div>
        </div>';
        
        return $html;
    }
}
