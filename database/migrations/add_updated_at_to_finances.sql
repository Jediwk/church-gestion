-- Ajout de la colonne updated_at à la table finances
ALTER TABLE finances ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
