-- Mise à jour de la table finance_types
ALTER TABLE finance_types 
DROP COLUMN category_id,
ADD COLUMN category ENUM('Entrée', 'Sortie') NOT NULL AFTER name;

-- Mise à jour de la table finances
ALTER TABLE finances 
DROP COLUMN finance_type_id,
ADD COLUMN type_id INT NOT NULL AFTER id,
ADD COLUMN reference VARCHAR(50) DEFAULT NULL AFTER description,
DROP COLUMN category;

-- Ajout des contraintes de clé étrangère
ALTER TABLE finances
ADD CONSTRAINT fk_finance_type
FOREIGN KEY (type_id) REFERENCES finance_types(id);

-- Mise à jour des données existantes
UPDATE finance_types 
SET category = CASE 
    WHEN type = 'income' THEN 'Entrée'
    WHEN type = 'expense' THEN 'Sortie'
    END;

-- Suppression de la colonne type obsolète
ALTER TABLE finance_types
DROP COLUMN type;
