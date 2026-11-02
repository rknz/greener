# 🌿 A Greener Tomorrow (`greener2morow.org`) — Complete Website & Admin User Guide
**Release Version:** Version 2 (`v2.0`)  
**Target Audience:** Website Administrators, Editorial Staff, Registered Members, and Daily Operators

---

## 📖 Table of Contents
1. [Quick Access & Default Login Credentials](#1-quick-access--default-login-credentials)
2. [Understanding User Roles & Permissions](#2-understanding-user-roles--permissions)
3. [Public Website Navigation & Visitor Features](#3-public-website-navigation--visitor-features)
   - [Desktop & Mobile Navigation Bar](#31-desktop--mobile-navigation-bar)
   - [Home Page Overview (`index.php`)](#32-home-page-overview-indexphp)
   - [Activities, Researches & Blog Pages](#33-activities-researches--blog-pages)
   - [Dedicated Article Details Page, Metrics & Threaded Comments](#34-dedicated-article-details-page-metrics--threaded-comments)
   - [Field Photo Gallery (`gallery.php`)](#35-field-photo-gallery-galleryphp)
   - [Joining as a Member (`join.php`) & Contacting the Secretariat (`contact.php`)](#36-joining-as-a-member-joinphp--contacting-the-secretariat-contactphp)
4. [How to Sign In (Same-Page Login Portal)](#4-how-to-sign-in-same-page-login-portal)
5. [Complete Admin Dashboard Guide (Left-Sidebar Navigation)](#5-complete-admin-dashboard-guide-left-sidebar-navigation)
   - [Tab 1: Executive Overview (`#overview`)](#tab-1-executive-overview-overview)
   - [Tab 2: Content & Posts (`#content`)](#tab-2-content--posts-content)
   - [Tab 3: Hero & Media Gallery (`#media`)](#tab-3-hero--media-gallery-media)
   - [Tab 4: Comments & Inbox (`#communications`)](#tab-4-comments--inbox-communications)
   - [Tab 5: Users & Access Control (`#users`)](#tab-5-users--access-control-users)
   - [Tab 6: Brand & Site Settings (`#settings`)](#tab-6-brand--site-settings-settings)
   - [Tab 7: Performance & Logs (`#performance`)](#tab-7-performance--logs-performance)
6. [Step-by-Step Cheat Sheets for Common Daily Tasks](#6-step-by-step-cheat-sheets-for-common-daily-tasks)
7. [Namecheap Hosting & Database Maintenance Guide](#7-namecheap-hosting--database-maintenance-guide)

---

## 1. Quick Access & Default Login Credentials

| Page / Portal | Relative URL Path | Purpose |
| :--- | :--- | :--- |
| **Public Homepage** | `/index.php` | Main landing page with Hero Slider, Impact Counters, and Featured Posts |
| **Field Activities** | `/activities.php` | Environmental field campaigns, tree planting, and cleanup drives |
| **Scientific Researches** | `/researches.php` | Peer-reviewed ecology studies, biodiversity audits, and policy reports |
| **Blog & Video Vlogs** | `/blog.php` | Editorial articles, youth opinions, and embedded documentary vlogs |
| **Field Gallery** | `/gallery.php` | Filterable high-resolution WebP photography with Lightbox viewer |
| **Join the Movement** | `/join.php` | Public registration form to become a Registered Member / Volunteer |
| **Contact Secretariat** | `/contact.php` | Direct inquiry form connected to the Admin Dashboard Inbox |
| **Member & Admin Login** | `/login.php` | Same-page sign-in portal with inline status notifications |
| **Admin Governance Suite** | `/admin/index.php` | Full 7-tab left-sidebar control center for managing the entire website |

### 🔐 Default Pre-Configured Accounts
> **Important Security Note:** After deploying your live website, go to **Admin Dashboard → Users & Access Control (`#users`)** and click **🔑 Reset Pass** to update these default passwords.

| Role | Email Address | Default Password | Access Level |
| :--- | :--- | :--- | :--- |
| **Master Administrator** | `admin@greener2morow.org` | `Greener@2026!` | Full access to all 7 Admin Dashboard tabs, settings, users, and content |
| **Editorial Lead** | `editor@greener2morow.org` | `Editor@2026!` | Can submit articles/posts and post member comments & replies |
| **Registered Member** | `member@greener2morow.org` | `Member@2026!` | Can submit community posts, comment on articles, and reply to comments |

---

## 2. Understanding User Roles & Permissions

The website uses a three-tier role system so you can safely involve volunteers and editors without exposing sensitive site settings:

1. **Public Visitor (Not Signed In):**
   - Can browse all published Activities, Researches, Blogs, Vlogs, and Gallery photos.
   - Can view readership metrics and read all member comments and replies on article detail pages.
   - Can register for an account on `/join.php` or send an inquiry on `/contact.php`.
2. **Registered Member / Editor (`member` / `editor`):**
   - Everything a Public Visitor can do, **plus**:
   - Can post **Comments** and **Direct Replies** on any Activity, Research, Blog, or Vlog details page.
   - Can access the **Community Submission Workspace** (`/join.php#submit-post`) to submit new articles or field reports (which enter the `pending` moderation queue until an Administrator approves them).
3. **Administrator (`admin`):**
   - Full access to the **Admin Governance Suite** (`/admin/index.php`).
   - Can publish content immediately (auto-approved), moderate pending submissions, manage Hero Slider banners, upload Gallery photos, moderate comments, read Contact Inbox messages, manage user accounts & passwords, update homepage counters, and adjust performance settings.

---

## 3. Public Website Navigation & Visitor Features

### 3.1 Desktop & Mobile Navigation Bar
- **Desktop View (`992px` and wider):** Displays the official brand emblem on the left, horizontal page links in the center (`Home`, `Activities`, `Researches`, `Blog & Vlogs`, `Gallery`, `Join Us`, `Contact`), and action buttons (`Sign In` or `Dashboard` + `Logout`) on the right.
- **Mobile & Tablet View (Below `992px`):**
  - Tap the **3-bar Hamburger Button (`☰`)** in the top-right corner of the header to slide open the **Mobile Navigation Drawer**.
  - Inside the drawer, tap any page link to navigate, or tap the **`✕` Close Button** (or the dark backdrop) to close the menu smoothly.

### 3.2 Home Page Overview (`index.php`)
The homepage is organized into dynamic sections that update automatically as you manage content in the Admin Dashboard:
1. **Full-Screen Hero Slider:** Cycles through active slides with a high-contrast black editorial overlay, customizable badge text, headline, sub-caption, and dual action buttons. Visitors can use the left/right arrows or bottom indicator dots to switch slides.
2. **Live Environmental Impact Bar:** Displays 4 animated counters (*Trees Planted*, *Plastic Removed in kg*, *Active Volunteers*, and *Communities Reached*). You can edit these numbers anytime in **Admin → Brand & Site Settings**.
3. **Core Pillars & Featured Initiatives:** Showcases highlighted Activities, Researches, and Blog/Vlog articles marked as **Featured (`★`)** in the Admin Dashboard.
4. **Interactive Field Gallery Preview:** Displays recent field photography with instant lightbox zoom.

### 3.3 Activities, Researches & Blog Pages
- **Search & Category Filtering:** At the top of `activities.php`, `researches.php`, and `blog.php`, visitors can type keywords or click category pills to filter articles in real time.
- **Opening an Article:** Clicking **"Read Full Report"**, **"Read Full Article"**, or the card title opens that post's **Dedicated Details Page** (`?slug=post-slug-name`).

### 3.4 Dedicated Article Details Page, Metrics & Threaded Comments
Every Activity, Research paper, Blog post, and Video Vlog has its own dedicated reading view containing:
1. **Readership Telemetry Bar:** Located right below the article header, showing:
   - **Total Views** (incremented automatically as readers visit the page)
   - **Verified Readers** (estimated engaged readership)
   - **Reading Time** (in minutes)
   - **Member Opinions** (total count of comments and replies on the post)
2. **Rich Article Body & Video Player:** Displays formatted headings, paragraphs, bullet lists, and (for Vlogs) an embedded responsive video player.
3. **Social Sharing Bar:** Quick buttons to copy the article link or share via email/social channels.
4. **Community Discussion & Member Opinions (Threaded Comments Section):**
   - **If Not Signed In:** Visitors see all existing comments and replies, plus a prompt with buttons to **Sign In to Comment** (`/login.php`) or **Become a Member** (`/join.php`).
   - **If Signed In:** A comment composer box appears at the top of the discussion section.
   - **Replying to a Specific Comment:** Any signed-in user can click the **`↩ Reply`** button beneath any top-level comment to open an inline reply box and post a nested response directly under that comment.
   - **Deleting Comments:** Users can delete their own comments (and Administrators can delete any comment) by clicking the **`Delete`** button next to the comment timestamp.

### 3.5 Field Photo Gallery (`gallery.php`)
- Visitors can filter photos by category (*All*, *Reforestation*, *Water Conservation*, *Clean Energy*, *Summit & Youth*, etc.) using the filter buttons at the top.
- Clicking any photo opens the **Full-Screen Lightbox** showing the high-resolution WebP image and caption.

### 3.6 Joining as a Member (`join.php`) & Contacting the Secretariat (`contact.php`)
- **Joining (`join.php`):** New volunteers fill out their Full Name, Email, Password, Phone/Location, Area of Interest, and Motivation. Submitting this form automatically creates their **Member Account**, logs their application in the Admin Inbox, and allows them to sign in immediately.
- **Submitting Community Articles (`join.php#submit-post`):** Signed-in members see a **Submit a Community Article / Field Report** form on the Join page where they can upload a cover photo and submit an article for admin review.
- **Contact Us (`contact.php`):** Visitors can send general, partnership, or media inquiries directly to the **Secretariat Contact Inbox** in the Admin Dashboard.

---

## 4. How to Sign In (Same-Page Login Portal)

1. Navigate to **`/login.php`** (click **Sign In** in the top navigation bar).
2. Enter your **Email Address** and **Password**, then click **Sign In to Portal**.
3. **Same-Page Notification Behavior (No Automatic Redirect):**
   - **If the email or password is incorrect:** The page stays right where it is and displays a red error alert **directly below the Sign In button** explaining that the credentials did not match.
   - **If the email and password are correct:** The page stays on `/login.php`, displays a green confirmation banner **directly below the Sign In button**, updates the status badge to **Signed In**, and reveals quick navigation buttons (**Open Admin Dashboard**, **Community Workspace**, **Return to Home Page**, or **Sign Out**) so you can choose where to go next.

---

## 5. Complete Admin Dashboard Guide (Left-Sidebar Navigation)

Once signed in as an **Administrator** (`admin@greener2morow.org`), click **Dashboard** in the top navigation bar (or visit `/admin/index.php`).

### How the Left Sidebar Works
- All dashboard tools are organized into **7 logical tabs** in the dark-green **Left Sidebar** (`.admin-sidebar`).
- Clicking any sidebar item switches to that section **instantly without reloading the page**, and remembers your active tab in the URL hash (e.g., `/admin/index.php#content`).
- On mobile/tablet screens, tap the **`☰` Menu Button** in the top bar of the dashboard to slide out the left sidebar.

---

### Tab 1: Executive Overview (`#overview`)
**Where to find it:** Left Sidebar → *Overview & Analytics* → **Executive Overview**

- **What it shows:**
  - **KPI Summary Cards:** Real-time counts of *Published Posts*, *Pending Review Queue*, *Registered Users*, *Gallery Assets*, *Total Article Views*, *Member Comments*, and *Unread Inbox Messages*.
  - **Quick-Action Launchpad:** One-click shortcuts to jump straight to publishing a post, uploading a hero slide, moderating comments, or updating site settings.
  - **Recent Platform Activity Table:** Shows the latest posts along with their type, author, view count, status badge (`Approved` / `Pending`), and a quick link to view them live.

---

### Tab 2: Content & Posts (`#content`)
**Where to find it:** Left Sidebar → *Overview & Analytics* → **Content & Posts**

This tab is split into two sub-panels:

#### A. Publish New Post / Research / Blog / Vlog (Top Form)
Use this form to publish new content directly onto the website:
1. **Post Title:** Enter a descriptive headline (e.g., *"Coastal Mangrove Restoration Report 2026"*). The URL slug is generated automatically.
2. **Content Type:** Choose where the post will appear:
   - `Activity` → Appears on `/activities.php`
   - `Research` → Appears on `/researches.php`
   - `Blog` → Appears on `/blog.php`
   - `Vlog (Video)` → Appears on `/blog.php` with a video badge and embedded video player
3. **Topic Category:** Enter a category label (e.g., *Reforestation*, *Climate Policy*, *Clean Water*, *Youth Action*).
4. **Read Time (Minutes):** Estimated reading time shown in the article's telemetry bar (e.g., `5`).
5. **Video Embed URL (Optional, for Vlogs):** Paste a YouTube embed URL (e.g., `https://www.youtube.com/embed/...`) if publishing a Vlog.
6. **Cover Image Upload:** Select a `.jpg`, `.png`, or `.webp` image from your computer. The system automatically optimizes and converts it to high-speed `.webp` format.
7. **Short Summary / Excerpt:** A 1–2 sentence preview shown on grid cards.
8. **Full Article Body:** Write the complete article text. You can use plain paragraphs or clean HTML formatting (`<h3>Subheading</h3>`, `<p>Paragraph</p>`, `<ul><li>Bullet item</li></ul>`).
9. **Feature on Homepage Checkbox:** Check this box if you want this post highlighted on the Homepage (`index.php`).
10. Click **Publish Content Now**. (Posts created by Administrators go live immediately!)

#### B. All Posts & Moderation Queue (Bottom Table)
- Use the **Filter Pills** (*All*, *Pending*, *Approved*, *Activity*, *Research*, *Blog/Vlog*) or the **Search Input** at the top of the table to find any post.
- **Available Row Actions:**
  - **`✓ Approve`:** Publishes a pending member/editor submission so it becomes visible to the public.
  - **`⏳ Set Pending`:** Hides a published post from public pages and returns it to the draft/review queue.
  - **`★ Feature` / `☆ Unfeature`:** Toggles whether the post is highlighted on the Homepage.
  - **`↗ View`:** Opens the live dedicated details page for that post in a new tab.
  - **`🗑 Delete`:** Permanently removes the post.

---

### Tab 3: Hero & Media Gallery (`#media`)
**Where to find it:** Left Sidebar → *Overview & Analytics* → **Hero & Media Gallery**

#### A. Homepage Hero Slider Manager (Left/Top Panel)
Controls the full-screen rotating banner on `/index.php`:
- **To Add a New Slide:**
  1. Fill in the **Badge Label** (small uppercase pill above the title, e.g., `2026 Action Initiative`).
  2. Enter the **Main Headline** and **Sub-Caption**.
  3. Set the **Primary Button Text & Link** (e.g., `Explore Activities` → `activities.php`) and **Secondary Button Text & Link** (e.g., `Join Us` → `join.php`).
  4. Set the **Sort Order** (`1` appears first, `2` second, etc.).
  5. Upload a high-resolution **Slide Background Image** and click **Add Hero Slide**.
- **To Manage Existing Slides:**
  - Click **`Hide` / `Activate`** on any slide card to temporarily disable or re-enable a slide without deleting it.
  - Click **`Delete`** to remove a slide permanently.

#### B. Field Photo Gallery Manager (Right/Bottom Panel)
Controls the photos displayed on `/gallery.php` and the Homepage gallery preview:
- **To Upload a Gallery Photo:** Enter a **Photo Title / Caption**, select or type a **Category** (e.g., *Reforestation*, *River Cleanup*, *Solar Energy*, *Biodiversity*), choose an image file, and click **Upload to Gallery**.
- **To Delete a Gallery Photo:** Click the red **`Delete`** button below any photo thumbnail in the gallery grid.

---

### Tab 4: Comments & Inbox (`#communications`)
**Where to find it:** Left Sidebar → *Community & Access* → **Comments & Inbox**

#### A. Member Comments & Replies Moderation (Top Section)
Displays every comment and nested reply posted across all Blog, Research, and Activity pages:
- Shows the **Author Name & Email**, whether it is a **Top-Level Comment** or a **Nested Reply (`↳ Reply`)**, the **Article Title** (clickable to view in context), the **Comment Text**, and the **Timestamp**.
- Click **`🗑 Delete`** on any row to remove spam or off-topic comments. (Deleting a parent comment also removes its nested replies automatically.)

#### B. Secretariat Contact Inbox (Bottom Section)
Displays all messages submitted via `/contact.php` and volunteer applications from `/join.php`:
- **Unread messages** are highlighted with an `Unread` badge and increase the red notification counter in the sidebar.
- **Actions on Each Message:**
  - **`✓ Mark Read`:** Marks an unread inquiry as handled.
  - **`✉ Reply`:** Opens your default email client pre-addressed to the sender with the subject line filled in.
  - **`🗑 Delete`:** Removes the message from the inbox.

---

### Tab 5: Users & Access Control (`#users`)
**Where to find it:** Left Sidebar → *Community & Access* → **Users & Access Control**

- **Register New User (Left Form):** Create a new account manually by entering Full Name, Email, Password, Role (`Registered Member`, `Editor`, or `Administrator`), and Bio, then clicking **Create User Account**.
- **Manage Existing Users (Right Table):**
  - **Change Role:** Use the dropdown menu in the **Role** column to promote or demote any user between `Member`, `Editor`, and `Admin` (changes save immediately).
  - **Suspend / Activate Account:** Click **`Ban`** to suspend a user (preventing them from signing in or commenting), or click **`Activate`** to restore their access.
  - **Reset Password (`🔑 Reset Pass`):** Click **`🔑 Reset Pass`** on any user row to open the Password Reset Modal, enter a new password (minimum 6 characters), and save it with Bcrypt encryption.
  - **Delete User:** Click **`🗑`** to remove a user account (note: you cannot delete the currently signed-in admin account).

---

### Tab 6: Brand & Site Settings (`#settings`)
**Where to find it:** Left Sidebar → *System & Configuration* → **Brand & Site Settings**

All changes made here apply globally across the header, homepage, contact page, and footer as soon as you click **Save All Site Settings**:
1. **Organization Identity:**
   - **Site Title:** e.g., `A Greener Tomorrow`
   - **Editorial Tagline:** e.g., `European Standard Environmental Action & Research Foundation`
   - **Mission Statement Summary:** Displayed in the about/footer summary areas.
2. **Secretariat Contact Details & Social Links:**
   - **Official Email, Phone Number, and Headquarters Address** (shown in the Contact page and Footer).
   - **Facebook, YouTube, and LinkedIn URLs** (linked in the Footer social icons).
3. **Homepage Live Impact Counters:**
   - Update the 4 numeric impact metrics: **Trees Planted**, **Plastic Removed (kg)**, **Active Volunteers**, and **Communities Reached**.
4. **Logo Placement Matrix (Checkboxes):**
   - Toggle where the official brand logo is rendered:
     - `Navbar Header Logo`
     - `Hero Slider Overlay Badge`
     - `Footer Brand Emblem`
     - `Browser Tab Favicon`
     - `Article Image Watermark`

---

### Tab 7: Performance & Logs (`#performance`)
**Where to find it:** Left Sidebar → *System & Configuration* → **Performance & Logs**

1. **Dynamic Optimization Sliders:**
   - **WebP Compression Quality (`40%` – `95%`):** Controls the file size vs. visual sharpness of newly uploaded images (default `82%` is ideal).
   - **Browser Static Cache TTL (`1` – `90` Days):** Controls how long visitor browsers cache CSS, JS, and images.
   - **Lazy-Load Viewport Margin (`50px` – `600px`):** Controls how early below-the-fold images start loading as a visitor scrolls.
   - Click **Save Performance Configuration** to apply changes.
2. **Server Maintenance & Database Backup Utilities:**
   - **Purge Runtime Cache:** Clears temporary cached data so recent edits appear immediately.
   - **Create Database Backup Snapshot:** Creates a timestamped copy of your SQLite database inside `assets/backups/`.
   - **Environment Diagnostics Table:** Displays your PHP version, active database driver (`SQLite` or `MySQL`), GD/WebP image engine status, and directory write permissions.
3. **Recent Visitor Telemetry Log:** Shows recent page visits, anonymized IP addresses, requested URLs, and timestamps.

---

## 6. Step-by-Step Cheat Sheets for Common Daily Tasks

### Task A: How to Publish a New Blog Post or Research Paper
1. Sign in at `/login.php` with your Admin credentials and click **Open Admin Dashboard**.
2. Click **Content & Posts** in the left sidebar.
3. Fill in the **Post Title**, select `Blog` or `Research` under **Content Type**, choose a **Cover Image**, write your **Excerpt** and **Full Article Body**, and check **Feature on Homepage** if desired.
4. Click **Publish Content Now**. Your post is immediately live with its own details page, readership metrics, and comment section!

### Task B: How to Approve a Post Submitted by a Volunteer Member
1. Open **Admin Dashboard → Content & Posts (`#content`)**.
2. In the **All Posts & Moderation Queue** table, click the **`Pending`** filter button at the top.
3. Review the submitted post and click **`✓ Approve`**. It will immediately publish to the live website.

### Task C: How to Change the Homepage Hero Banner Text or Photo
1. Open **Admin Dashboard → Hero & Media Gallery (`#media`)**.
2. Under **Homepage Hero Slider Manager**, upload a new background image, enter your **Headline**, **Sub-Caption**, and button links, and click **Add Hero Slide**.
3. To turn off an old slide, click **`Hide`** or **`Delete`** on that slide's card in the list next to the form.

### Task D: How to Post or Reply to a Comment on an Article
1. Sign in at `/login.php` (as a Member, Editor, or Admin).
2. Open any Activity, Research, or Blog post details page (`blog.php?slug=...`).
3. Scroll down to **Community Discussion & Member Opinions**.
4. Type your comment in the box and click **Post Opinion**, OR click **`↩ Reply`** underneath any existing comment to reply directly to that person.

---

## 7. Namecheap Hosting & Database Maintenance Guide

- **Zero-Configuration Default Mode (SQLite):** Out of the box, the website stores all posts, comments, users, slides, and settings in `database/greener.sqlite`. No MySQL setup is required for the site to work immediately upon extraction to `/public_html/`.
- **Optional MySQL Mode on Namecheap cPanel:**
  1. In cPanel, open **MySQL® Database Wizard** and create a database (e.g., `greeykdv_greener`) and user with **All Privileges**.
  2. Open **phpMyAdmin**, select your new database, click **Import**, and upload `database/greener_namecheap_mysql.sql`.
  3. Edit `config/db-credentials.php` in cPanel File Manager: set `'DB_USE_MYSQL' => true` and enter your database name, username, and password.

---
*© 2026 A Greener Tomorrow Foundation. All Rights Reserved. Version 2 — Designed & Developed by [rknz](mailto:rukonuzzaman.dev@gmail.com).*
