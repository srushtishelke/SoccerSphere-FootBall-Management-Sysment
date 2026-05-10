# Football Tournament Management System (SoccerSphere)

A complete PHP-based web application to organize, join, and track soccer tournaments with ease. Features role-based access for Players, Managers, and Admins.

## Deployment to Render with Neon (PostgreSQL)

This project is configured to run seamlessly on [Render.com](https://render.com/) with a [Neon](https://neon.tech/) PostgreSQL database.

### 1. Setup Neon Database
1. Create a free account on Neon.tech.
2. Create a new database project.
3. Run the SQL schema found in `postgres_schema.sql` to generate the required tables.
4. Copy the connection string (it looks like `postgresql://user:password@endpoint...`).

### 2. Deploy to Render
1. Push this repository to GitHub.
2. In Render, create a new **Web Service** and connect it to your GitHub repository.
3. Choose the **Docker** runtime (Render will automatically detect the `Dockerfile`).
4. In the Render environment variables settings, add a new variable:
   - **Key**: `DATABASE_URL`
   - **Value**: `[Paste the Neon connection string here]`
5. Deploy!

## Local Development (MySQL / XAMPP)

The project is fully database-agnostic and will automatically fall back to a local MySQL connection if the `DATABASE_URL` environment variable is not present.

1. Ensure XAMPP (Apache and MySQL) is running.
2. Import `football_tournament.sql` into phpMyAdmin (`http://localhost/phpmyadmin`).
3. Place the project folder in `htdocs` or run using PHP's built-in server:
   ```bash
   php -S localhost:8000
   ```
4. Access the app at `http://localhost:8000`.

## Default Accounts
* **Admin**: `admin1` / `password`
* **Manager**: `manager1` / `password`
* **Player**: `player1` / `password`
