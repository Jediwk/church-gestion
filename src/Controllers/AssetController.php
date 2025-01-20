<?php
namespace Controllers;

use Core\Controller;

class AssetController extends Controller {
    /**
     * Sert les fichiers templates pour l'import
     */
    public function serveTemplate(string $file) {
        $templatesDir = ROOT_DIR . '/assets/templates/';
        $filePath = $templatesDir . basename($file);

        if (!file_exists($filePath)) {
            header("HTTP/1.0 404 Not Found");
            return;
        }

        // Vérification de sécurité
        if (strpos(realpath($filePath), realpath($templatesDir)) !== 0) {
            header("HTTP/1.0 403 Forbidden");
            return;
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'csv':
                header('Content-Type: text/csv');
                break;
            case 'xls':
                header('Content-Type: application/vnd.ms-excel');
                break;
            default:
                header('Content-Type: application/octet-stream');
        }

        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        readfile($filePath);
    }
}
