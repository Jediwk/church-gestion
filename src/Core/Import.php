<?php
namespace Core;

class Import {
    /**
     * Importe des données depuis un fichier CSV
     */
    public static function fromCsv(string $file, array $requiredHeaders = []): array {
        if (!file_exists($file)) {
            throw new \Exception("Le fichier n'existe pas");
        }

        $handle = fopen($file, 'r');
        if ($handle === false) {
            throw new \Exception("Impossible d'ouvrir le fichier");
        }

        // Détection du BOM UTF-8 et skip si présent
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Lecture des en-têtes
        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            throw new \Exception("Le fichier est vide");
        }

        // Vérification des en-têtes requis
        if (!empty($requiredHeaders)) {
            $missingHeaders = array_diff($requiredHeaders, $headers);
            if (!empty($missingHeaders)) {
                fclose($handle);
                throw new \Exception("En-têtes manquants : " . implode(', ', $missingHeaders));
            }
        }

        $data = [];
        $row = 2; // Pour les messages d'erreur (ligne 1 = en-têtes)

        while (($record = fgetcsv($handle)) !== false) {
            // Vérification du nombre de colonnes
            if (count($record) !== count($headers)) {
                fclose($handle);
                throw new \Exception("Erreur à la ligne $row : nombre de colonnes incorrect");
            }

            $data[] = array_combine($headers, $record);
            $row++;
        }

        fclose($handle);
        return $data;
    }

    /**
     * Importe des données depuis un fichier Excel (HTML)
     */
    public static function fromExcel(string $file, array $requiredHeaders = []): array {
        if (!file_exists($file)) {
            throw new \Exception("Le fichier n'existe pas");
        }

        // Lecture du fichier HTML
        $html = file_get_contents($file);
        if ($html === false) {
            throw new \Exception("Impossible de lire le fichier");
        }

        // Création d'un DOMDocument pour parser le HTML
        $doc = new \DOMDocument();
        @$doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Récupération du tableau
        $table = $doc->getElementsByTagName('table')->item(0);
        if (!$table) {
            throw new \Exception("Aucun tableau trouvé dans le fichier");
        }

        // Récupération des lignes
        $rows = $table->getElementsByTagName('tr');
        if ($rows->length < 2) {
            throw new \Exception("Le tableau doit contenir au moins une ligne d'en-tête et une ligne de données");
        }

        // Lecture des en-têtes
        $headers = [];
        $headerCells = $rows->item(0)->getElementsByTagName('th');
        foreach ($headerCells as $cell) {
            $headers[] = trim($cell->textContent);
        }

        // Vérification des en-têtes requis
        if (!empty($requiredHeaders)) {
            $missingHeaders = array_diff($requiredHeaders, $headers);
            if (!empty($missingHeaders)) {
                throw new \Exception("En-têtes manquants : " . implode(', ', $missingHeaders));
            }
        }

        // Lecture des données
        $data = [];
        for ($i = 1; $i < $rows->length; $i++) {
            $row = $rows->item($i);
            $cells = $row->getElementsByTagName('td');
            
            // Vérification du nombre de colonnes
            if ($cells->length !== count($headers)) {
                throw new \Exception("Erreur à la ligne " . ($i + 1) . " : nombre de colonnes incorrect");
            }

            $rowData = [];
            foreach ($cells as $index => $cell) {
                $rowData[$headers[$index]] = trim($cell->textContent);
            }
            $data[] = $rowData;
        }

        return $data;
    }

    /**
     * Valide les données importées selon des règles spécifiées
     */
    public static function validate(array $data, array $rules): array {
        $errors = [];
        $row = 2; // Pour les messages d'erreur (ligne 1 = en-têtes)

        foreach ($data as $record) {
            foreach ($rules as $field => $rule) {
                if (!isset($record[$field])) {
                    continue;
                }

                $value = $record[$field];

                // Règle 'required'
                if (isset($rule['required']) && $rule['required'] && empty($value)) {
                    $errors[] = "Ligne $row : Le champ '$field' est requis";
                    continue;
                }

                // Règle 'type'
                if (isset($rule['type'])) {
                    switch ($rule['type']) {
                        case 'email':
                            if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                $errors[] = "Ligne $row : Le champ '$field' doit être une adresse email valide";
                            }
                            break;
                        case 'date':
                            if (!empty($value) && !strtotime($value)) {
                                $errors[] = "Ligne $row : Le champ '$field' doit être une date valide";
                            }
                            break;
                        case 'phone':
                            if (!empty($value) && !preg_match('/^[0-9+\-\s()]*$/', $value)) {
                                $errors[] = "Ligne $row : Le champ '$field' doit être un numéro de téléphone valide";
                            }
                            break;
                    }
                }

                // Règle 'min'
                if (isset($rule['min']) && strlen($value) < $rule['min']) {
                    $errors[] = "Ligne $row : Le champ '$field' doit contenir au moins {$rule['min']} caractères";
                }

                // Règle 'max'
                if (isset($rule['max']) && strlen($value) > $rule['max']) {
                    $errors[] = "Ligne $row : Le champ '$field' doit contenir au plus {$rule['max']} caractères";
                }

                // Règle 'enum'
                if (isset($rule['enum']) && !empty($value) && !in_array($value, $rule['enum'])) {
                    $errors[] = "Ligne $row : Le champ '$field' doit être l'une des valeurs suivantes : " . implode(', ', $rule['enum']);
                }
            }
            $row++;
        }

        return $errors;
    }
}
