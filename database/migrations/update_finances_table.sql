-- Mise à jour de la table finances
ALTER TABLE finances 
RENAME COLUMN finance_type_id TO type_id,
ADD COLUMN reference VARCHAR(50) DEFAULT NULL AFTER description,
DROP COLUMN category;
