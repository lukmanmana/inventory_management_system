# Inventory Management System (PHP/Tailwind)
Project Web Application Development
This is a small inventory management web application written in plain PHP with Tailwind CSS for UI. The project is designed to run on a local XAMPP (Apache + MySQL) setup.

## Quick overview
- Server-rendered PHP pages.
- Vanilla JavaScript for client-side interactions (search, filters, modals).
- Tailwind CSS (via CDN) and Font Awesome for icons.
- Backend controller endpoints live under `backend/controllers/`.
- Database schema: `database/schema.sql`.

## Requirements
- Windows (instructions assume Windows + XAMPP).
- XAMPP (Apache + MySQL). Recommended recent PHP 8.x version compatible with your XAMPP.
- A web browser.

## Setup using XAMPP (Windows)
1. Install XAMPP if not already installed: https://www.apachefriends.org/
2. Start Apache and MySQL via the XAMPP Control Panel.
3. Place the project folder in XAMPP's `htdocs` directory. Example path used in this workspace:

   c:\xampp\htdocs\web

4. Database import (choose one):

   - Using phpMyAdmin:
     1. Open http://localhost/phpmyadmin
     2. Create a new database (e.g., `inventory_db`).
     3. With the new DB selected, go to Import → Choose File → select `database/schema.sql` → Go.


5. Configure database credentials

   There are two config files you may need to update depending on deployment:

   - `config/database.php` (root-level config used by public pages)
   - `backend/config/database.php` or `backend/config/Environment.php` (backend controllers)

   Open these files and update the DB host, name, username and password to match the database you created (e.g., `inventory_db`, `root`, `''`).

6. Start the app

   - In your browser, open: http://localhost/web/public/ (or http://localhost/web/ if you configured the index route)


## Project structure (key files)

- `public/dashboard.php` — Main dashboard UI, client-side JS, and modals.
- `backend/controllers/` — API endpoints used by the frontend (get_categories.php, add_product.php, edit_product.php, products.php, delete_product.php, profile.php, subscriptions.php).
- `config/database.php` — Main DB connection settings for public pages.
- `backend/config/` — Backend-specific configuration files.
- `database/schema.sql` — SQL schema to create the necessary tables.
- `public/assets/js/api.js` — Shared JS helpers (if present).

## Features
- Add / Edit / Delete products
- Category management
- Search and client-side filters (all, low-stock, out-of-stock)
- Stock alert center (low stock indicators)
- CSV export (client-side and premium/full server export)
- Profile and subscription controls
## Design
<img width="1897" height="904" alt="image" src="https://github.com/user-attachments/assets/b1fcfa15-68b2-4a36-aaad-46c97cb44a1c" />
<img width="1898" height="903" alt="image" src="https://github.com/user-attachments/assets/3940e33d-5a2a-458a-ad62-708eecd1eb73" />
<img width="1900" height="903" alt="image" src="https://github.com/user-attachments/assets/f6264c5d-7f07-478a-9616-21d4b39edafe" />
<img width="1903" height="905" alt="image" src="https://github.com/user-attachments/assets/d18f6f30-60f2-4a6d-bd6f-8c97190f7fb1" />


---

