DROP DATABASE IF EXISTS football_tournament;
CREATE DATABASE football_tournament;
USE football_tournament;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('player', 'manager', 'admin') NOT NULL DEFAULT 'player',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE players (
    player_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    age INT,
    position VARCHAR(50),
    contact_no VARCHAR(20),
    tournament_history TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE tournaments (
    tournament_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    organizer_id INT,
    rules TEXT,
    start_date DATE,
    end_date DATE,
    status ENUM('draft', 'open', 'ongoing', 'completed') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE teams (
    team_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    manager_id INT,
    tournament_id INT,
    FOREIGN KEY (manager_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(tournament_id) ON DELETE CASCADE
);

CREATE TABLE team_players (
    team_id INT,
    player_id INT,
    PRIMARY KEY (team_id, player_id),
    FOREIGN KEY (team_id) REFERENCES teams(team_id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(player_id) ON DELETE CASCADE
);

CREATE TABLE matches (
    match_id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT,
    team1_id INT,
    team2_id INT,
    match_date DATETIME,
    venue VARCHAR(100),
    score_team1 INT DEFAULT 0,
    score_team2 INT DEFAULT 0,
    status ENUM('scheduled', 'ongoing', 'completed') DEFAULT 'scheduled',
    FOREIGN KEY (tournament_id) REFERENCES tournaments(tournament_id) ON DELETE CASCADE,
    FOREIGN KEY (team1_id) REFERENCES teams(team_id) ON DELETE SET NULL,
    FOREIGN KEY (team2_id) REFERENCES teams(team_id) ON DELETE SET NULL
);

CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE reset_tokens (
    token_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    token VARCHAR(255) NOT NULL UNIQUE,
    expiry DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE results (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT UNIQUE,
    winner_id INT,
    details TEXT,
    FOREIGN KEY (match_id) REFERENCES matches(match_id) ON DELETE CASCADE,
    FOREIGN KEY (winner_id) REFERENCES teams(team_id) ON DELETE SET NULL
);

CREATE TABLE player_stats (
    stat_id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT,
    match_id INT,
    goals_scored INT DEFAULT 0,
    assists INT DEFAULT 0,
    yellow_cards INT DEFAULT 0,
    red_cards INT DEFAULT 0,
    FOREIGN KEY (player_id) REFERENCES players(player_id) ON DELETE CASCADE,
    FOREIGN KEY (match_id) REFERENCES matches(match_id) ON DELETE CASCADE
);

CREATE TABLE match_comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT,
    user_id INT,
    comment_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(match_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Note: All sample users have password = 'password' (bcrypt hashed)
INSERT INTO users (username, password, email, role) VALUES
    ('player1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player1@example.com', 'player'),
    ('manager1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager1@example.com', 'manager'),
    ('admin1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin1@example.com', 'admin');

INSERT INTO players (user_id, first_name, last_name, age, position, tournament_history) VALUES
    (1, 'John', 'Doe', 25, 'Forward', 'Participated in Spring Cup 2024');

INSERT INTO tournaments (name, organizer_id, rules, start_date, end_date, status) VALUES
    ('Spring Cup 2025', 2, 'Standard FIFA rules, 11 vs 11', '2025-02-20', '2025-02-25', 'active');

INSERT INTO teams (name, manager_id, tournament_id) VALUES
    ('Red Dragons', 2, 1),
    ('Blue Tigers', 2, 1);

INSERT INTO team_players (team_id, player_id) VALUES
    (1, 1);

INSERT INTO matches (tournament_id, team1_id, team2_id, match_date, venue, score_team1, score_team2, status) VALUES
    (1, 1, 2, '2025-02-21 15:00:00', 'City Stadium', 2, 1, 'completed');

INSERT INTO results (match_id, winner_id) VALUES (1, 1);

INSERT INTO match_comments (match_id, user_id, comment_text) VALUES (1, 2, 'Goal by John Doe at 15''');