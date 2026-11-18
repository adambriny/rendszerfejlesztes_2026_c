CREATE DATABASE IF NOT EXISTS fitlog_db CHARACTER SET utf8mb4 COLLATE utf8mb4_hungarian_ci;
USE fitlog_db;

-- 1. Felhasználók tábla
CREATE TABLE felhasznalok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    felhasznalonev VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    jelszo_hash VARCHAR(255) NOT NULL,
    letrehozas_datuma DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. Izomcsoportok tábla
CREATE TABLE izomcsoportok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nev VARCHAR(50) NOT NULL,
    szin VARCHAR(20) NOT NULL
);

-- 3. Edzések tábla
CREATE TABLE edzesek (
    id INT AUTO_INCREMENT PRIMARY KEY,
    felhasznalo_id INT NOT NULL,
    nev VARCHAR(100) NOT NULL,
    datum DATE NOT NULL,
    idotartam INT NOT NULL,
    letrehozas_datuma DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (felhasznalo_id) REFERENCES felhasznalok(id) ON DELETE CASCADE
);

-- 4. Gyakorlatok tábla
CREATE TABLE gyakorlatok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nev VARCHAR(100) NOT NULL,
    izomcsoport_id INT NOT NULL,
    leiras TEXT,
    FOREIGN KEY (izomcsoport_id) REFERENCES izomcsoportok(id) ON DELETE CASCADE
);

-- 5. Edzéshez tartozó gyakorlatok (kapcsolótábla)
CREATE TABLE edzes_gyakorlatok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    edzes_id INT NOT NULL,
    gyakorlat_id INT NOT NULL,
    sorrend INT DEFAULT 1,
    FOREIGN KEY (edzes_id) REFERENCES edzesek(id) ON DELETE CASCADE,
    FOREIGN KEY (gyakorlat_id) REFERENCES gyakorlatok(id) ON DELETE CASCADE
);

-- 6. Sorozatok tábla
CREATE TABLE sorozatok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    edzes_gyakorlat_id INT NOT NULL,
    sorozat_szama INT NOT NULL,
    ismetlesszam INT NOT NULL,
    suly DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (edzes_gyakorlat_id) REFERENCES edzes_gyakorlatok(id) ON DELETE CASCADE
);

-- Alap izomcsoportok feltöltése
INSERT INTO izomcsoportok (nev, szin) VALUES
('Mell', '#3b82f6'),
('Hát', '#60a5fa'),
('Láb', '#34d399'),
('Váll', '#f472b6'),
('Kar', '#fbbf24'),
('Has', '#a78bfa');

-- Alap gyakorlatok feltöltése
INSERT INTO gyakorlatok (nev, izomcsoport_id, leiras) VALUES
('Fekvenyomás', 1, 'Mellizom alapgyakorlat vízszintes padon.'),
('Ferde pados nyomás', 1, 'Felső mellizom erősítése ferde padon.'),
('Tárogatás', 1, 'Mellizom izolációs gyakorlat kézisúlyzóval.'),
('Húzódzkodás', 2, 'Széles hátizom saját testsúlyos alapgyakorlata.'),
('Evezés rúddal', 2, 'Hátvastagító alapgyakorlat döntött törzzsel.'),
('Guggolás', 3, 'Comb és farizom legfontosabb alapgyakorlata.'),
('Lábtoló', 3, 'Alsótest erősítése gépen.'),
('Vállból nyomás', 4, 'Deltaizom alapgyakorlata állva vagy ülve.'),
('Bicepsz curl', 5, 'Karhajlítás kézisúlyzóval vagy francia rúddal.'),
('Tolódzkodás', 5, 'Tricepsz és alsó mellizom gyakorlat.'),
('Hasprés', 6, 'Egyenes hasizom erősítése.');

-- Teszt felhasználó (Email: jozsef@fitlog.hu | Jelszó: password)
INSERT INTO felhasznalok (felhasznalonev, email, jelszo_hash) VALUES
('József', 'jozsef@fitlog.hu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Minta edzések a teszteléshez
INSERT INTO edzesek (felhasznalo_id, nev, datum, idotartam) VALUES
(1, 'Mell + Tricepsz', '2026-11-16', 68),
(1, 'Hát + Bicepsz', '2026-11-14', 72),
(1, 'Láb', '2026-11-12', 65);

INSERT INTO edzes_gyakorlatok (edzes_id, gyakorlat_id, sorrend) VALUES
(1, 1, 1),
(1, 2, 2),
(2, 4, 1),
(3, 6, 1);

INSERT INTO sorozatok (edzes_gyakorlat_id, sorozat_szama, ismetlesszam, suly) VALUES
(1, 1, 8, 50.00), (1, 2, 8, 50.00), (1, 3, 8, 50.00),
(2, 1, 10, 40.00), (2, 2, 10, 40.00),
(3, 1, 10, 0.00), (3, 2, 10, 0.00),
(4, 1, 8, 90.00), (4, 2, 8, 90.00), (4, 3, 8, 90.00);