<?php
// db.php

$host = 'sql207.infinityfree.com';
$db = 'if0_42273705_ticket';
$user = 'if0_42273705';
$pass = 'MWJvmCfpNDKo';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

/*
-- RUN THIS COMPLETE SQL IN YOUR INFINITYFREE PHPMYADMIN SQL TAB TO MANUALLY GENERATE SYSTEM TABLES:

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS artists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    image VARCHAR(255) DEFAULT 'default_artist.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    artist_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    venue VARCHAR(150) NOT NULL,
    event_date DATETIME NOT NULL,
    stadium_map VARCHAR(255) DEFAULT 'default_map.jpg',
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    section_name VARCHAR(50) NOT NULL,
    is_ga INT DEFAULT 0,
    ga_price DECIMAL(10,2) DEFAULT 0.00,
    ga_available_tickets INT DEFAULT 0,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_id INT NOT NULL,
    row_name VARCHAR(10) NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    is_booked INT DEFAULT 0,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ticket_transfers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ticket_file VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SECURE DEFAULT INITIALIZATION OF THE ADMINISTRATOR LEDGER PROFILE
-- Username: PookaAlex | Password: PookaAlex (Safely encrypted)
INSERT IGNORE INTO users (username, password, email, role)
VALUES ('PookaAlex', '$2y$10$7R0b8qF6A.96A9Z5E7O3UeG7rVzNlWv7Z7U8uE8L3v6Fm1gE7fG6.', 'admin@yourdomain.com', 'admin');
*/
?>
