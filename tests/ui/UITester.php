<?php

class UITester {
    private $errors = [];
    private $warnings = [];
    private $templates = [];

    public function __construct() {
        $this->findTemplates();
    }

    private function findTemplates() {
        $templateDir = __DIR__ . '/../../templates';
        $this->scanDirectory($templateDir);
    }

    private function scanDirectory($dir) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->scanDirectory($path);
            } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                $this->templates[] = $path;
            }
        }
    }

    public function runTests() {
        foreach ($this->templates as $template) {
            $this->testTemplate($template);
        }

        $this->displayResults();
    }

    private function testTemplate($template) {
        $content = file_get_contents($template);
        $templateName = str_replace(__DIR__ . '/../../templates/', '', $template);

        // Test 1: Responsive Design
        $this->testResponsiveDesign($content, $templateName);

        // Test 2: Compatibilité Navigateurs
        $this->testBrowserCompatibility($content, $templateName);

        // Test 3: Performance
        $this->testPerformance($content, $templateName);
    }

    private function testResponsiveDesign($content, $template) {
        echo "\nTest Responsive Design pour $template:\n";

        // Vérifie la présence de classes Bootstrap responsives
        if (!preg_match('/class="[^"]*col-(xs|sm|md|lg|xl)-\d+/', $content)) {
            $this->warnings[] = "$template : Pas de classes de grille responsive détectées";
        }

        // Vérifie les méta viewport
        if (!preg_match('/<meta[^>]*viewport[^>]*>/', $content) && !preg_match('/\$this->setMeta\([^)]*viewport[^)]*\)/', $content)) {
            $this->errors[] = "$template : Meta viewport manquant";
        }

        // Vérifie les images responsives
        if (preg_match('/<img[^>]*>/', $content)) {
            if (!preg_match('/<img[^>]*class="[^"]*img-fluid[^"]*"[^>]*>/', $content)) {
                $this->warnings[] = "$template : Images non responsives détectées";
            }
        }

        // Vérifie les tables responsives
        if (preg_match('/<table[^>]*>/', $content)) {
            if (!preg_match('/class="[^"]*table-responsive[^"]*"/', $content)) {
                $this->warnings[] = "$template : Tables non responsives détectées";
            }
        }
    }

    private function testBrowserCompatibility($content, $template) {
        echo "\nTest Compatibilité Navigateurs pour $template:\n";

        // Vérifie les propriétés CSS modernes
        $modernProperties = [
            'display: flex' => 'Flexbox',
            'display: grid' => 'Grid',
            'transform:' => 'Transformations CSS',
            '@keyframes' => 'Animations CSS'
        ];

        foreach ($modernProperties as $property => $feature) {
            if (preg_match('/' . preg_quote($property, '/') . '/', $content)) {
                echo "✓ Utilise $feature\n";
            }
        }

        // Vérifie les API JavaScript modernes
        $modernJS = [
            'fetch(' => 'Fetch API',
            'Promise' => 'Promises',
            'async' => 'Async/Await'
        ];

        foreach ($modernJS as $api => $feature) {
            if (preg_match('/' . preg_quote($api, '/') . '/', $content)) {
                $this->warnings[] = "$template : Utilise $feature sans polyfill détecté";
            }
        }
    }

    private function testPerformance($content, $template) {
        echo "\nTest Performance pour $template:\n";

        // Taille du fichier
        $size = strlen($content);
        if ($size > 50000) {
            $this->warnings[] = "$template : Fichier volumineux (" . round($size/1024, 2) . " KB)";
        }

        // Scripts en fin de page
        if (preg_match('/<script[^>]*>[^<]*<\/script>/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $position = $matches[0][1];
            if ($position < strlen($content) / 2) {
                $this->warnings[] = "$template : Scripts détectés en début de page";
            }
        }

        // Requêtes SQL dans le template
        if (preg_match('/SELECT|INSERT|UPDATE|DELETE/i', $content)) {
            $this->errors[] = "$template : Requêtes SQL détectées dans le template";
        }

        // Images sans dimensions
        if (preg_match('/<img[^>]*(?!width|height)[^>]*>/i', $content)) {
            $this->warnings[] = "$template : Images sans dimensions spécifiées";
        }

        // CSS inline
        $inlineStyles = preg_match_all('/style="[^"]*"/', $content);
        if ($inlineStyles > 5) {
            $this->warnings[] = "$template : $inlineStyles styles CSS inline détectés";
        }
    }

    private function displayResults() {
        echo "\n=== RAPPORT DE TESTS UI ===\n\n";

        if (empty($this->errors) && empty($this->warnings)) {
            echo "✅ Tous les tests ont passé avec succès !\n";
            return;
        }

        if (!empty($this->errors)) {
            echo "❌ ERREURS :\n";
            foreach ($this->errors as $error) {
                echo "  - $error\n";
            }
            echo "\n";
        }

        if (!empty($this->warnings)) {
            echo "⚠️ AVERTISSEMENTS :\n";
            foreach ($this->warnings as $warning) {
                echo "  - $warning\n";
            }
            echo "\n";
        }
    }
}

// Exécution des tests
$tester = new UITester();
$tester->runTests();
