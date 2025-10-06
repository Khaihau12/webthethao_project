# WebTheThao - Local PHP News Site

This project is a small PHP news site (designed for XAMPP / WAMP / any PHP + MySQL local environment). I updated the SQL sample data and PHP helpers so the site renders dynamically from the `articles` table.

Quick setup

1. Start XAMPP and ensure Apache + MySQL are running.
2. Import the database:
   - Open `phpMyAdmin` and run the SQL file `setup.sql` located at the project root, or run from a terminal:

```powershell
mysql -u root -p < .\setup.sql
```

3. Place the project in your web root (already at `c:\xampp\htdocs\webthethao_project`).
4. Open in browser: http://localhost/webthethao_project/index.php

Notes
- Sample images reference files in `assets/`. Replace them with your own images if needed.
- Database connection is in `includes/db_config.php` (username/password currently `root`/empty). Change as needed.

If you want, I can also:
- Add an admin UI to create/edit articles,
- Add pagination and category pages,
- Improve mobile styles and accessibility.
