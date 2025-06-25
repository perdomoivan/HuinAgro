CREATE TABLE `Users` (
  `id_user` INT PRIMARY KEY AUTO_INCREMENT,
  `username` VARCHAR(255) UNIQUE NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'coordinator') NOT NULL DEFAULT 'coordinator',
  `reset_token` VARCHAR(255) DEFAULT NULL,
  `token_expiry` DATETIME DEFAULT NULL
);

CREATE TABLE `FishTypes` (
  `id_fish_type` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) UNIQUE NOT NULL
);

CREATE TABLE `WeightPresentations` (
  `id_weight_presentation` INT PRIMARY KEY AUTO_INCREMENT,
  `description` VARCHAR(255) UNIQUE NOT NULL
);

CREATE TABLE `FishingRecords` (
  `id_record` INT PRIMARY KEY AUTO_INCREMENT,
  `id_user` INT NOT NULL,
  `id_fish_type` INT NOT NULL,
  `client` VARCHAR(255) NOT NULL,
  `lake` VARCHAR(255) NOT NULL,
  `date` DATE NOT NULL,
  `total_amount` DECIMAL(10,2) DEFAULT 0.00,
  FOREIGN KEY (`id_user`) REFERENCES `Users` (`id_user`),
  FOREIGN KEY (`id_fish_type`) REFERENCES `FishTypes` (`id_fish_type`)
);

CREATE TABLE `FishingEntries` (
  `id_entry` INT PRIMARY KEY AUTO_INCREMENT,
  `id_record` INT NOT NULL,
  `id_fish_type` INT NOT NULL,
  `id_weight_presentation` INT NOT NULL,
  `baskets` INT DEFAULT 0,
  `gross_weight` DECIMAL(10,2) DEFAULT 0.00,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  FOREIGN KEY (`id_record`) REFERENCES `FishingRecords` (`id_record`),
  FOREIGN KEY (`id_fish_type`) REFERENCES `FishTypes` (`id_fish_type`),
  FOREIGN KEY (`id_weight_presentation`) REFERENCES `WeightPresentations` (`id_weight_presentation`)
);

CREATE TABLE `Reports` (
  `id_report` INT PRIMARY KEY AUTO_INCREMENT,
  `id_user` INT NOT NULL,
  `id_record` INT NULL,
  `date` DATE NOT NULL,
  `report_type` ENUM('PDF') NOT NULL,
  FOREIGN KEY (`id_user`) REFERENCES `Users` (`id_user`),
  FOREIGN KEY (`id_record`) REFERENCES `FishingRecords` (`id_record`)
);

INSERT INTO FishTypes (name)
SELECT * FROM (SELECT 'Mojarra Roja') AS tmp
WHERE NOT EXISTS (SELECT 1 FROM FishTypes WHERE name = 'Mojarra Roja') LIMIT 1;

INSERT INTO FishTypes (name)
SELECT * FROM (SELECT 'Mojarra Negra') AS tmp
WHERE NOT EXISTS (SELECT 1 FROM FishTypes WHERE name = 'Mojarra Negra') LIMIT 1;

INSERT INTO FishTypes (name)
SELECT * FROM (SELECT 'Bocachico') AS tmp
WHERE NOT EXISTS (SELECT 1 FROM FishTypes WHERE name = 'Bocachico') LIMIT 1;

INSERT INTO WeightPresentations (description)
SELECT * FROM (SELECT '3/4-libra') AS tmp
WHERE NOT EXISTS (SELECT 1 FROM WeightPresentations WHERE description = '3/4-libra') LIMIT 1;

INSERT INTO WeightPresentations (description)
SELECT * FROM (SELECT '1/2-Media') AS tmp
WHERE NOT EXISTS (SELECT 1 FROM WeightPresentations WHERE description = '1/2-Media') LIMIT 1;

INSERT INTO WeightPresentations (description)
SELECT * FROM (SELECT 'Libra') AS tmp
WHERE NOT EXISTS (SELECT 1 FROM WeightPresentations WHERE description = 'Libra') LIMIT 1;

INSERT INTO WeightPresentations (description)
SELECT * FROM (SELECT 'Especial') AS tmp
WHERE NOT EXISTS (SELECT 1 FROM WeightPresentations WHERE description = 'Especial') LIMIT 1;

INSERT INTO WeightPresentations (description)
SELECT * FROM (SELECT 'Mojarrin') AS tmp
WHERE NOT EXISTS (SELECT 1 FROM WeightPresentations WHERE description = 'Mojarrin') LIMIT 1;

INSERT INTO WeightPresentations (description)
SELECT * FROM (SELECT 'Libra-1/2') AS tmp
WHERE NOT EXISTS (SELECT 1 FROM WeightPresentations WHERE description = 'Libra-1/2') LIMIT 1;

INSERT INTO WeightPresentations (description)
SELECT * FROM (SELECT 'Libra-1/4') AS tmp
WHERE NOT EXISTS (SELECT 1 FROM WeightPresentations WHERE description = 'Libra-1/4') LIMIT 1;

INSERT INTO WeightPresentations (description)
SELECT * FROM (SELECT 'Llaverito') AS tmp
WHERE NOT EXISTS (SELECT 1 FROM WeightPresentations WHERE description = 'Llaverito') LIMIT 1;

SELECT 
    fe.id_entry,
    fr.id_record,
    fr.client,
    fr.lake,
    fr.date,
    wp.description,
    fe.gross_weight,
    fe.price AS total_entry_price,
    fr.total_amount AS total_price
FROM FishingRecords fr
JOIN FishingEntries fe ON fr.id_record = fe.id_record
JOIN WeightPresentations wp ON fe.id_weight_presentation = wp.id_weight_presentation
ORDER BY fr.id_record, fe.id_entry;huinagro