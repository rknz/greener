# Namecheap Domain & cPanel Hosting Deployment Guide

Your **A Greener Tomorrow** (`greener2morow.org`) project is pre-packaged and ready for immediate upload to **Namecheap Shared Hosting (cPanel)**.

---

## 1. Fast 2-Minute Upload (Zero-Config Mode)
1. Log in to your **Namecheap cPanel** and open **File Manager**.
2. Navigate to the `public_html` directory (or your domain's document root).
3. Click **Upload** and select **`greener-namecheap-cpanel-ready.zip`**.
4. Right-click `greener-namecheap-cpanel-ready.zip` in File Manager and click **Extract** into `public_html`.
5. **Done!** Visit your Namecheap domain immediately.
   - The platform automatically detects your domain URL (`https://yourdomain.com`) and runs out-of-the-box using the pre-seeded `/data/greener.sqlite` database with all 17 category WebP photos, posts, and threaded comments.

---

## 2. Optional: Switching to Namecheap cPanel MySQL Database
If you prefer to use a cPanel MySQL database instead of the portable SQLite engine:
1. In Namecheap cPanel, open **MySQL® Database Wizard**:
   - Create a database (e.g. `cpaneluser_greener`) and user with **ALL PRIVILEGES**.
2. Open **phpMyAdmin** in cPanel, select your new database, and click **Import** -> upload `database/greener_namecheap_mysql.sql` (the application will also auto-create and seed any missing tables on first load).
3. Edit `config/db-credentials.php` in cPanel File Manager:
   ```php
   return [
       'enable_mysql' => true,
       'host'         => 'localhost',
       'port'         => 3306,
       'dbname'       => 'cpaneluser_greener',
       'username'     => 'cpaneluser_admin',
       'password'     => 'YOUR_DB_PASSWORD',
   ];
   ```

---

## 3. Default Portal Credentials
- **Admin Governance Dashboard (`/admin/index.php`):**
  - **Username:** `admin` (or `greener2morow@gmail.com`)
  - **Password:** `Admin@1234`
- **Researcher / Member Account (`/dashboard/index.php`):**
  - **Username:** `sofiqul.alam`
  - **Password:** `Admin@1234`

