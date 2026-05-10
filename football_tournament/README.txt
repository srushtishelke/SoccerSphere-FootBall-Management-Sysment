Football Tournament Management Website
========================================
Final Year Project Submission - February 21, 2025

Overview:
- A dynamic web application for managing football tournaments with user authentication, tournament creation, team enrollment, match scheduling, and live score updates.

Setup Instructions:
1. Install XAMPP or similar with PHP and PostgreSQL.
2. Create a PostgreSQL database named 'football_tournament' and run the SQL script below.
3. Update 'includes/db_connect.php' with your PostgreSQL credentials.
4. Place this folder in your web server (e.g., htdocs).
5. Access via http://localhost/football_tournament/index.php.

Features:
- User roles: Player, Manager, Admin
- Player profiles, tournament management, team enrollment, match scheduling
- Real-time live scores via AJAX
- Responsive design with Bootstrap 5

Limitations:
- Booking system and detailed standings not fully implemented due to time constraints.
- Basic security (prepared statements used; CSRF not implemented).

SQL Script:
CREATE DATABASE football_tournament;
\c football_tournament;
CREATE TABLE users (user_id SERIAL PRIMARY KEY, username VARCHAR(50) UNIQUE NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(100) UNIQUE NOT NULL, role VARCHAR(20) NOT NULL DEFAULT 'player' CHECK (role IN ('player', 'manager', 'admin')), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE players (player_id SERIAL PRIMARY KEY, user_id INT REFERENCES users(user_id), name VARCHAR(100) NOT NULL, age INT, position VARCHAR(50), tournament_history TEXT);
CREATE TABLE tournaments (tournament_id SERIAL PRIMARY KEY, name VARCHAR(100) NOT NULL, organizer_id INT REFERENCES users(user_id), rules TEXT, start_date DATE, end_date DATE, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE teams (team_id SERIAL PRIMARY KEY, name VARCHAR(100) NOT NULL, manager_id INT REFERENCES users(user_id), tournament_id INT REFERENCES tournaments(tournament_id));
CREATE TABLE team_players (team_id INT REFERENCES teams(team_id), player_id INT REFERENCES players(player_id), PRIMARY KEY (team_id, player_id));
CREATE TABLE matches (match_id SERIAL PRIMARY KEY, tournament_id INT REFERENCES tournaments(tournament_id), team1_id INT REFERENCES teams(team_id), team2_id INT REFERENCES teams(team_id), match_date TIMESTAMP, venue VARCHAR(100), score_team1 INT DEFAULT 0, score_team2 INT DEFAULT 0, status VARCHAR(20) DEFAULT 'scheduled' CHECK (status IN ('scheduled', 'ongoing', 'completed')));
