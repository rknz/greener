<?php
/**
 * A Greener Tomorrow (greener2morow.org)
 * Dual-Driver PDO Database Engine ("Better DB")
 * Portable SQLite (Zero-Config) + Production MySQL Support
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;
    private static string $driver = 'sqlite';

    // MySQL Credentials (Can be adjusted for live cPanel hosting or set via env)
    private static string $dbHost = 'localhost';
    private static string $dbName = 'greener2morow';
    private static string $dbUser = 'root';
    private static string $dbPass = '';
    private static int $dbPort    = 3306;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            self::connect();
            self::initSchemaAndSeed();
        }
        return self::$instance;
    }

    public static function getDriver(): string {
        return self::$driver;
    }

    private static function connect(): void {
        // Optional Namecheap cPanel MySQL credentials override file
        $credFile = __DIR__ . '/db-credentials.php';
        if (file_exists($credFile)) {
            $creds = include $credFile;
            if (is_array($creds)) {
                self::$dbHost = $creds['host'] ?? self::$dbHost;
                self::$dbName = $creds['dbname'] ?? self::$dbName;
                self::$dbUser = $creds['username'] ?? self::$dbUser;
                self::$dbPass = $creds['password'] ?? self::$dbPass;
                self::$dbPort = (int)($creds['port'] ?? self::$dbPort);
                if (!empty($creds['enable_mysql'])) {
                    putenv('USE_MYSQL=true');
                }
            }
        }

        // 1. Attempt MySQL connection if configured
        $useMysql = getenv('DB_CONNECTION') === 'mysql' || (!empty(self::$dbName) && getenv('USE_MYSQL') === 'true');
        
        if ($useMysql) {
            try {
                $dsn = "mysql:host=" . self::$dbHost . ";port=" . self::$dbPort . ";dbname=" . self::$dbName . ";charset=utf8mb4";
                self::$instance = new PDO($dsn, self::$dbUser, self::$dbPass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]);
                self::$driver = 'mysql';
                return;
            } catch (PDOException $e) {
                // Fall back to SQLite if MySQL is unavailable
            }
        }

        // 2. Portable SQLite PDO Engine (Default Zero-Config)
        $sqliteDir = DATA_PATH;
        if (!is_dir($sqliteDir)) {
            mkdir($sqliteDir, 0755, true);
        }
        $sqlitePath = $sqliteDir . '/greener.sqlite';

        try {
            self::$instance = new PDO("sqlite:" . $sqlitePath, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            self::$instance->exec("PRAGMA journal_mode = WAL;");
            self::$instance->exec("PRAGMA foreign_keys = ON;");
            self::$driver = 'sqlite';
        } catch (PDOException $e) {
            die("Database Connection Error: " . htmlspecialchars($e->getMessage()));
        }
    }

    private static function initSchemaAndSeed(): void {
        $db = self::$instance;
        $isSqlite = (self::$driver === 'sqlite');

        $autoIncrement = $isSqlite ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT AUTO_INCREMENT PRIMARY KEY";
        $datetimeType  = $isSqlite ? "TEXT" : "DATETIME";
        $textType      = "TEXT";

        // 1. site_settings Table
        $db->exec("CREATE TABLE IF NOT EXISTS site_settings (
            id INTEGER PRIMARY KEY,
            org_name $textType NOT NULL,
            tagline $textType,
            about_text $textType,
            address $textType NOT NULL,
            email $textType NOT NULL,
            phone $textType,
            website $textType NOT NULL,
            facebook_url $textType,
            logo_url $textType,
            logo_use_navbar INTEGER DEFAULT 1,
            logo_use_footer INTEGER DEFAULT 1,
            logo_use_section INTEGER DEFAULT 1,
            global_user_control $textType DEFAULT 'AUTO',
            stat_programs INTEGER DEFAULT 42,
            stat_trees INTEGER DEFAULT 50000,
            stat_members INTEGER DEFAULT 1850,
            stat_researches INTEGER DEFAULT 24,
            stat_districts INTEGER DEFAULT 48,
            perf_webp_quality INTEGER DEFAULT 80,
            perf_max_width INTEGER DEFAULT 1200,
            perf_thumb_width INTEGER DEFAULT 480,
            perf_max_video_mb INTEGER DEFAULT 25,
            perf_max_doc_mb INTEGER DEFAULT 15,
            perf_lazy_loading INTEGER DEFAULT 1,
            perf_cdn_enabled INTEGER DEFAULT 1,
            perf_gzip_enabled INTEGER DEFAULT 1,
            emergency_alert_active INTEGER DEFAULT 0,
            emergency_alert_title $textType,
            emergency_alert_text $textType,
            emergency_alert_link $textType
        );");

        // 2. users Table (Credentials with Bcrypt + Profile)
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id $autoIncrement,
            username $textType NOT NULL UNIQUE,
            name $textType DEFAULT 'Sofiq Mobilizer',
            full_name $textType,
            email $textType NOT NULL UNIQUE,
            password_hash $textType NOT NULL,
            must_change_password INTEGER DEFAULT 0,
            failed_login_attempts INTEGER DEFAULT 0,
            locked_until $datetimeType,
            session_version INTEGER DEFAULT 1,
            avatar_url $textType,
            avatar $textType,
            designation $textType,
            public_email $textType,
            phone $textType,
            institution $textType,
            district $textType DEFAULT 'Dhaka',
            city $textType DEFAULT 'Dhaka',
            focus_pillar $textType DEFAULT 'CLIMATE',
            skills $textType,
            bio $textType,
            role $textType DEFAULT 'USER',
            control_mode $textType DEFAULT 'AUTO',
            member_status $textType DEFAULT 'APPROVED',
            is_active INTEGER DEFAULT 1,
            created_at $datetimeType DEFAULT CURRENT_TIMESTAMP,
            updated_at $datetimeType DEFAULT CURRENT_TIMESTAMP
        );");

        // 3. posts Table (Unified for ACTIVITY, RESEARCH, RESEARCH_BLOG, BLOG, VLOG)
        $db->exec("CREATE TABLE IF NOT EXISTS posts (
            id $autoIncrement,
            title $textType NOT NULL,
            slug $textType NOT NULL UNIQUE,
            type $textType NOT NULL,
            pillar $textType NOT NULL,
            is_slider_featured INTEGER DEFAULT 0,
            excerpt $textType,
            content $textType NOT NULL,
            cover_image $textType,
            thumb_image $textType,
            original_kb INTEGER DEFAULT 0,
            compressed_kb INTEGER DEFAULT 0,
            video_type $textType DEFAULT 'NONE',
            video_embed_url $textType,
            video_file_path $textType,
            video_size_mb REAL DEFAULT 0,
            location $textType,
            event_date $datetimeType,
            activity_status $textType DEFAULT 'COMPLETED',
            status $textType DEFAULT 'PUBLISHED',
            moderation_note $textType,
            view_count INTEGER DEFAULT 0,
            unique_read_count INTEGER DEFAULT 0,
            reading_time INTEGER DEFAULT 3,
            author_id INTEGER NOT NULL,
            created_at $datetimeType DEFAULT CURRENT_TIMESTAMP,
            updated_at $datetimeType DEFAULT CURRENT_TIMESTAMP
        );");

        // Indexes for Sub-5ms Query Speed ("Better DB")
        $db->exec("CREATE INDEX IF NOT EXISTS idx_posts_type_status ON posts(type, status, created_at);");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_posts_pillar ON posts(pillar, status);");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_posts_author ON posts(author_id, status);");

        // 4. post_attachments Table (Multi-format Research/Blog Docs)
        $db->exec("CREATE TABLE IF NOT EXISTS post_attachments (
            id $autoIncrement,
            post_id INTEGER NOT NULL,
            file_name $textType NOT NULL,
            file_path $textType NOT NULL,
            file_type $textType NOT NULL,
            file_size_kb INTEGER DEFAULT 0,
            created_at $datetimeType DEFAULT CURRENT_TIMESTAMP
        );");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_attachments_post ON post_attachments(post_id);");

        // 5. post_view_logs Table (Visitor Info & Unique Read Tracking)
        $db->exec("CREATE TABLE IF NOT EXISTS post_view_logs (
            id $autoIncrement,
            post_id INTEGER NOT NULL,
            page_url $textType,
            visitor_hash $textType NOT NULL,
            user_id INTEGER,
            device_type $textType DEFAULT 'DESKTOP',
            browser_info $textType,
            os_info $textType,
            ip_masked $textType,
            referrer_source $textType,
            created_at $datetimeType DEFAULT CURRENT_TIMESTAMP
        );");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_views_post_visitor ON post_view_logs(post_id, visitor_hash);");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_views_device ON post_view_logs(device_type, created_at);");

        // 6. user_activity_logs Table (Audit Trail)
        $db->exec("CREATE TABLE IF NOT EXISTS user_activity_logs (
            id $autoIncrement,
            user_id INTEGER NOT NULL,
            action $textType NOT NULL,
            post_type $textType,
            target_title $textType,
            details $textType,
            created_at $datetimeType DEFAULT CURRENT_TIMESTAMP
        );");

        // 7. gallery_items Table
        $db->exec("CREATE TABLE IF NOT EXISTS gallery_items (
            id $autoIncrement,
            title $textType NOT NULL,
            image_url $textType NOT NULL,
            thumb_url $textType,
            image_path $textType,
            thumbnail_path $textType,
            pillar $textType DEFAULT 'ENVIRONMENT',
            category $textType DEFAULT 'Reforestation',
            caption $textType,
            sort_order INTEGER DEFAULT 0,
            created_at $datetimeType DEFAULT CURRENT_TIMESTAMP
        );");

        // 8. contact_messages Table
        $db->exec("CREATE TABLE IF NOT EXISTS contact_messages (
            id $autoIncrement,
            name $textType NOT NULL,
            email $textType NOT NULL,
            phone $textType,
            subject $textType,
            message $textType NOT NULL,
            status $textType DEFAULT 'unread',
            is_read INTEGER DEFAULT 0,
            created_at $datetimeType DEFAULT CURRENT_TIMESTAMP
        );");

        // 9. hero_slides Table
        $db->exec("CREATE TABLE IF NOT EXISTS hero_slides (
            id $autoIncrement,
            title $textType NOT NULL,
            subtitle $textType,
            badge_text $textType DEFAULT 'Live Action',
            image_path $textType DEFAULT 'assets/brand/template-mockup.jpg',
            cta_text $textType DEFAULT 'Join This Action',
            cta_url $textType DEFAULT 'activities.php',
            secondary_cta_text $textType DEFAULT 'Become a Member',
            secondary_cta_url $textType DEFAULT 'join.php',
            sort_order INTEGER DEFAULT 0,
            is_active INTEGER DEFAULT 1,
            created_at $datetimeType DEFAULT CURRENT_TIMESTAMP
        );");

        // 10. post_comments Table (Threaded Comments & Replies for Blog/Research/Activity Details)
        $db->exec("CREATE TABLE IF NOT EXISTS post_comments (
            id $autoIncrement,
            post_id INTEGER NOT NULL,
            user_id INTEGER,
            parent_id INTEGER DEFAULT 0,
            author_name $textType NOT NULL,
            author_role $textType DEFAULT 'Registered Member',
            author_avatar $textType,
            comment_text $textType NOT NULL,
            status $textType DEFAULT 'APPROVED',
            created_at $datetimeType DEFAULT CURRENT_TIMESTAMP
        );");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_comments_post ON post_comments(post_id, status, created_at);");

        // 11. post_views Alias View / Table for compatibility
        $db->exec("CREATE VIEW IF NOT EXISTS post_views AS SELECT id, post_id, page_url, visitor_hash, user_id, LOWER(device_type) as device_type, browser_info as browser, os_info as os, ip_masked, referrer_source, created_at FROM post_view_logs;");

        // Seed Data if Fresh Install
        self::seedDefaultData($db);
        self::seedDefaultComments($db);
    }

    private static function seedDefaultData(PDO $db): void {
        // Check if already seeded
        $stmt = $db->query("SELECT COUNT(*) FROM site_settings WHERE id = 1");
        if ($stmt->fetchColumn() > 0) {
            return;
        }

        // 1. Seed Site Settings
        $stmt = $db->prepare("INSERT INTO site_settings (
            id, org_name, tagline, about_text, address, email, phone, website, facebook_url,
            logo_url, logo_use_navbar, logo_use_footer, logo_use_section, global_user_control,
            stat_programs, stat_trees, stat_members, stat_researches, stat_districts,
            emergency_alert_active, emergency_alert_title, emergency_alert_text, emergency_alert_link
        ) VALUES (
            1, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, 1, 1, 1, 'AUTO',
            42, 50000, 1850, 24, 48,
            1, ?, ?, ?
        )");
        $stmt->execute([
            'A Greener Tomorrow',
            'Youth-Based Environmental Advocacy & Independent Research Organization',
            'A Greener Tomorrow is a grassroots environmental advocacy and independent research organization founded in Dhaka, Bangladesh. We mobilize youth across 64 districts to combat climate change, protect natural biodiversity, restore degraded forests, stop urban water and plastic pollution, and build resilient disaster response networks.',
            '36, Bir Uttam C.R Dutta Road, Hatirpool, Dhaka-1205, Bangladesh',
            'greener2morow@gmail.com',
            '+880 1712-345678',
            'https://greener2morow.org',
            'https://facebook.com/greener2morow',
            '/assets/brand/brand-logo.png',
            'Urgent Action: Saving the Sundarbans & Rampal Advocacy',
            'Mobilizing youth volunteers nationwide for coastal mangrove defense and clean energy transition.',
            '#initiatives'
        ]);

        // 2. Seed Default Admin User & Lead Contributor
        // Password for both: Admin@1234
        $adminHash = password_hash('Admin@1234', PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt = $db->prepare("INSERT INTO users (
            username, name, email, password_hash, designation, public_email, phone, institution, district, bio, avatar_url, avatar, role, control_mode, member_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Admin
        $stmt->execute([
            'admin',
            'A Greener Tomorrow Admin',
            'greener2morow@gmail.com',
            $adminHash,
            'Central Executive Director',
            'greener2morow@gmail.com',
            '+880 1712-345678',
            'Dhaka University Climate Consortium',
            'Dhaka',
            'Overseeing youth mobilization programs, independent environmental research publications, and nationwide advocacy.',
            'avatar-admin.webp',
            'avatar-admin.webp',
            'ADMIN',
            'FULL_ACCESS',
            'APPROVED'
        ]);
        $adminId = $db->lastInsertId();

        // Lead Contributor ("Sofiqul Alam")
        $stmt->execute([
            'sofiqul.alam',
            'Sofiqul Alam',
            'sofiqul@greener2morow.org',
            $adminHash,
            'Lead Environmental Researcher & Mobilization Coordinator',
            'sofiqul@greener2morow.org',
            '+880 1819-876543',
            'Institute of Water & Flood Management (BUET)',
            'Dhaka',
            'Focusing on river ecological surveys, industrial waste mitigation, and flood disaster relief coordination in Bangladesh.',
            'avatar-researcher.webp',
            'avatar-researcher.webp',
            'USER',
            'AUTO',
            'APPROVED'
        ]);
        $contributorId = $db->lastInsertId();

        // 3. Seed Featured Initiative Posts (For Homepage Slider & Activities)
        $stmtPost = $db->prepare("INSERT INTO posts (
            title, slug, type, pillar, is_slider_featured, excerpt, content, cover_image, thumb_image,
            video_type, video_embed_url, location, event_date, activity_status, status,
            view_count, unique_read_count, reading_time, author_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PUBLISHED', ?, ?, ?, ?)");

        // Slide 1 / Activity 1: Climate March & Mangrove Defense
        $stmtPost->execute([
            'Nationwide Youth Climate March & Mangrove Defense Campaign',
            'nationwide-youth-climate-march-mangrove-defense',
            'ACTIVITY',
            'CLIMATE',
            1,
            'Mobilizing over 1,420 youth advocates across Dhaka, Khulna, and Bagerhat to raise awareness on rising sea levels and demand sustainable coastal protection policies.',
            "<h3>Program Overview</h3><p>The Youth Climate March mobilized university and college students from across Bangladesh to deliver a unified message on climate resilience. Organized by A Greener Tomorrow from our Hatirpool headquarters, the campaign brought together grassroots advocates, scientific researchers, and local coastal communities.</p><h4>Objectives & Field Execution</h4><ul><li>Independent documentation of coastal erosion along the Pashur river basin.</li><li>Educational seminars in 14 secondary schools on climate change adaptation.</li><li>Distribution of 5,000 mangrove saplings to local youth volunteers in Satkhira and Khulna.</li></ul><p>The initiative generated widespread public discourse and submitted a comprehensive 40-page policy brief to national environmental authorities.</p>",
            '/assets/uploads/posts/post-climate-march.webp',
            '/assets/uploads/posts/post-climate-march.webp',
            'NONE',
            null,
            'Hatirpool, Dhaka & Khulna Region',
            '2026-10-15 09:00:00',
            'COMPLETED',
            2450,
            1820,
            4,
            $adminId
        ]);
        $postId1 = $db->lastInsertId();

        // Slide 2 / Activity 2: Buriganga Clean River Movement
        $stmtPost->execute([
            'Buriganga River Waste Cleanup & Plastic-Free Cities Drive',
            'buriganga-river-cleanup-plastic-free-cities',
            'ACTIVITY',
            'POLLUTION',
            1,
            'A grassroots mobilization removing 12.5 tons of non-biodegradable industrial and plastic waste from 8 high-density points along the Buriganga riverbank.',
            "<h3>Community Mobilization Report</h3><p>Urban river pollution represents one of the most critical public health and environmental hazards in the Greater Dhaka metropolitan area. On October 22, 2026, over 350 youth volunteers equipped with protective gear and waste-sorting equipment participated in the Buriganga Clean Movement.</p><h4>Impact Summary</h4><ul><li>12.5 tons of plastic and toxic fabric waste safely extracted and transferred to municipal recycling facilities.</li><li>Installed 20 permanent bio-degradable collection bins along Sadarghat, Kamrangirchar, and Hatirpool canal intersections.</li><li>Water testing conducted simultaneously to assess pH and dissolved oxygen levels.</li></ul>",
            '/assets/uploads/posts/post-river-cleanup.webp',
            '/assets/uploads/posts/post-river-cleanup.webp',
            'NONE',
            null,
            'Buriganga Riverbank, Dhaka',
            '2026-10-22 07:30:00',
            'COMPLETED',
            1890,
            1340,
            3,
            $contributorId
        ]);

        // Activity 3: Urban Reforestation & Native Saplings
        $stmtPost->execute([
            'Dhaka Urban Reforestation & 10,000 Native Saplings Drive',
            'dhaka-urban-reforestation-native-saplings-drive',
            'ACTIVITY',
            'DEFORESTATION',
            0,
            'Planting indigenous Neem, Mahogany, and Krishnachura trees across school campuses and urban heat islands in Hatirpool, Dhanmondi, and Mirpur.',
            "<h3>Reforestation Field Report</h3><p>Urban heat islands in Dhaka have intensified due to rapid loss of green cover. A Greener Tomorrow partnered with 28 local schools and community youth clubs to plant 10,000 native saplings with 92% survival tracking.</p><h4>Ecological Highlights</h4><ul><li>Prioritized indigenous monsoon-resilient species over invasive trees.</li><li>Assigned student Tree Guardians at every school campus for weekly watering and growth monitoring.</li></ul>",
            '/assets/uploads/posts/post-tree-planting.webp',
            '/assets/uploads/posts/post-tree-planting.webp',
            'NONE',
            null,
            'Hatirpool & Dhanmondi, Dhaka',
            '2026-10-25 08:30:00',
            'COMPLETED',
            2110,
            1650,
            4,
            $adminId
        ]);

        // Slide 3 / Research Post 1: Industrial Runoff Water Sampling
        $stmtPost->execute([
            'Assessing Industrial Heavy Metal Runoff into the Buriganga River: A Youth-Led Water Sampling Survey',
            'buriganga-heavy-metal-runoff-survey-2026',
            'RESEARCH',
            'POLLUTION',
            1,
            'Independent laboratory analysis measuring biological oxygen demand (BOD) and chemical pollutants across 14 sampling points in Hatirpool, Kamrangirchar, and Sadarghat.',
            "<h3>Abstract & Study Scope</h3><p>This independent research study evaluates heavy metal concentrations and biochemical oxygen demand (BOD) across key urban effluent discharge channels leading into the Buriganga River. Conducted between August and October 2026 by researchers at A Greener Tomorrow, the study provides open-access environmental datasets for civic advocacy.</p><h4>Key Findings</h4><p>Dissolved oxygen (DO) levels fell below 1.2 mg/L at 9 out of 14 testing sites, well beneath the threshold required to sustain aquatic life. The full analytical report with laboratory graphs and geographic coordinate plots is available in the downloadable PDF document attached below.</p>",
            '/assets/uploads/posts/post-water-research.webp',
            '/assets/uploads/posts/post-water-research.webp',
            'NONE',
            null,
            'Hatirpool & Buriganga Basin, Dhaka',
            '2026-10-28 10:00:00',
            'COMPLETED',
            3120,
            2410,
            5,
            $contributorId
        ]);
        $researchPostId = $db->lastInsertId();

        // Attach Document to Research Post 1
        $stmtAttach = $db->prepare("INSERT INTO post_attachments (post_id, file_name, file_path, file_type, file_size_kb) VALUES (?, ?, ?, ?, ?)");
        $stmtAttach->execute([
            $researchPostId,
            'buriganga-water-survey-report-2026.pdf',
            '/assets/uploads/posts/post-water-research.webp',
            'PDF',
            2450
        ]);

        // Research Post 2: Solar Micro-Grids & Clean Energy
        $stmtPost->execute([
            'Decentralized Solar Micro-Grids for Cyclone Shelters in Coastal Bangladesh',
            'solar-micro-grids-cyclone-shelters-coastal-bangladesh',
            'RESEARCH',
            'CLIMATE',
            0,
            'Technical feasibility study evaluating community-managed solar micro-grids and battery storage across 32 coastal cyclone shelters in Khulna and Barishal.',
            "<h3>Executive Summary</h3><p>During severe cyclonic events in the Bay of Bengal, grid power outages leave coastal shelters without emergency lighting, water purification, or medical refrigeration. Our engineering and environmental research team evaluated 32 multipurpose cyclone shelters.</p><h4>Policy Recommendations</h4><p>Transitioning coastal shelters to hybrid 15kW solar photovoltaic arrays with lithium-iron-phosphate storage ensures 72-hour uninterrupted emergency power while cutting diesel emissions by 88%.</p>",
            '/assets/uploads/posts/post-solar-energy.webp',
            '/assets/uploads/posts/post-solar-energy.webp',
            'NONE',
            null,
            'Khulna & Barishal Coastal Belt',
            '2026-10-30 14:00:00',
            'COMPLETED',
            1980,
            1490,
            6,
            $adminId
        ]);

        // Post 6: Research-Based Blog & Vlog
        $stmtPost->execute([
            'Coastal Embankment Erosion in Satkhira: Why Mangrove Buffer Zones Outperform Concrete Sea Walls',
            'satkhira-coastal-embankment-mangrove-vs-concrete',
            'RESEARCH_BLOG',
            'DISASTER',
            0,
            'Field observations and ecological data demonstrating that community-planted mangrove belts reduce storm-surge wave impact by 66% compared to bare earthen embankments.',
            "<h3>Field Commentary & Research Insights</h3><p>Coastal Bangladesh faces intensified cyclone frequencies and tidal flooding. Drawing from comparative surveys conducted in Shyamnagar, Satkhira, this research-based blog analyzes why natural mangrove buffer zones provide superior long-term flood resilience compared to costly concrete sea walls.</p><h4>Key Ecological Findings</h4><ul><li>Dense Sundari and Keora pneumatophore root networks dissipate tidal wave energy by up to 66% within the first 100 meters of coastline.</li><li>Natural sediment trapping raises coastal mudflat elevation by 1.8 cm annually, counteracting relative sea-level rise.</li><li>Community co-management creates sustainable non-timber livelihoods for coastal families.</li></ul><p>Watch the on-the-ground video documentation where local community elders and youth forestry teams share empirical evidence from the 2026 cyclone season.</p>",
            '/assets/uploads/posts/post-coastal-mangrove.webp',
            '/assets/uploads/posts/post-coastal-mangrove.webp',
            'YOUTUBE',
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'Satkhira Coastal Belt, Bangladesh',
            '2026-11-01 11:30:00',
            'COMPLETED',
            1640,
            1190,
            4,
            $contributorId
        ]);

        // Post 7: Editorial Blog (Urban Heat Islands & Micro-Forests)
        $stmtPost->execute([
            'How Miyawaki Urban Micro-Forests Are Cooling Dhaka Neighborhoods by 3.4°C',
            'miyawaki-urban-micro-forests-cooling-dhaka-neighborhoods',
            'BLOG',
            'DEFORESTATION',
            0,
            'A deep dive into how ultra-dense native pocket forests in Hatirpool, Dhanmondi, and Uttara are restoring urban biodiversity and lowering ambient street temperatures.',
            "<h3>Reclaiming Concrete Spaces for Native Canopy</h3><p>Over the past two decades, rapid unplanned urbanization in Dhaka has replaced wetlands and tree cover with heat-absorbing concrete and asphalt. Surface temperature readings recorded by our youth volunteers in Hatirpool and Farmgate regularly exceed surrounding rural areas by 4.5°C during pre-monsoon heatwaves.</p><h4>The Miyawaki Pocket Forest Method</h4><p>By planting 3 to 4 native saplings per square meter using multi-layered indigenous species—including Hijol, Jarul, Neem, and Bahera—our urban forestry cells have established fast-growing micro-forests on school corners and public plots as small as 600 square feet.</p><ul><li><strong>3.4°C Temperature Drop:</strong> Infrared thermal sensors confirm a 3.4°C reduction in canopy-level air temperature within 14 months of planting.</li><li><strong>10x Faster Growth:</strong> Symbiotic mycorrhizal soil preparation accelerates vertical growth and carbon sequestration.</li><li><strong>Stormwater Absorption:</strong> Porous forest floors absorb monsoon runoff 8x faster than compacted turf grass.</li></ul>",
            '/assets/uploads/posts/post-tree-planting.webp',
            '/assets/uploads/posts/post-tree-planting.webp',
            'NONE',
            null,
            'Hatirpool & Dhanmondi, Dhaka',
            '2026-11-01 15:00:00',
            'COMPLETED',
            2280,
            1740,
            5,
            $adminId
        ]);

        // Post 8: Video Vlog (Youth River Audit & Plastic Interception)
        $stmtPost->execute([
            'Inside Our 64-District Youth River Audit & Zero-Plastic Campus Movement',
            'inside-64-district-youth-river-audit-zero-plastic-movement',
            'VLOG',
            'POLLUTION',
            0,
            'Follow our student volunteer brigades across Dhaka, Chattogram, and Sylhet as they deploy floating trash booms and audit urban canal discharge points.',
            "<h3>Documentary Dispatch & Field Guide</h3><p>In this field dispatch, our student coordinators demonstrate how grassroots citizen science is holding polluters accountable while cleaning Bangladesh's lifeline waterways. Equipped with portable multiparameter water quality meters and GPS mapping kits, over 1,800 youth members have cataloged 140+ plastic choke points.</p><h4>3-Step Campus & Community Blueprint</h4><ul><li><strong>Source Segregation:</strong> Eliminating single-use plastic sachets and bottles across 45 university and college campuses.</li><li><strong>Floating Canal Barriers:</strong> Low-cost bamboo and mesh interception booms trapping floating macro-plastics before they enter major rivers.</li><li><strong>Open Data Advocacy:</strong> Publishing monthly ward-by-ward pollution scorecards shared directly with municipal authorities.</li></ul>",
            '/assets/uploads/posts/post-river-cleanup.webp',
            '/assets/uploads/posts/post-river-cleanup.webp',
            'YOUTUBE',
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'Buriganga & Turag Basin, Dhaka',
            '2026-11-02 09:30:00',
            'COMPLETED',
            1950,
            1480,
            4,
            $contributorId
        ]);

        // 4. Seed Gallery Photos (6 category-specific online photos)
        $stmtGallery = $db->prepare("INSERT INTO gallery_items (title, image_url, thumb_url, image_path, thumbnail_path, pillar, category, caption) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtGallery->execute(['Hatirpool Youth Clean Movement', '/assets/uploads/gallery/gallery-pollution-cleanup.webp', '/assets/uploads/gallery/gallery-pollution-cleanup.webp', 'assets/uploads/gallery/gallery-pollution-cleanup.webp', 'assets/uploads/gallery/gallery-pollution-cleanup.webp', 'POLLUTION', 'Pollution Control', 'Mobilizing youth volunteers in Dhaka for riverbank plastic extraction and community waste segregation.']);
        $stmtGallery->execute(['School Tree Plantation Drive', '/assets/uploads/gallery/gallery-tree-planting.webp', '/assets/uploads/gallery/gallery-tree-planting.webp', 'assets/uploads/gallery/gallery-tree-planting.webp', 'assets/uploads/gallery/gallery-tree-planting.webp', 'DEFORESTATION', 'Reforestation', 'Planting indigenous timber and fruit saplings in secondary schools across Dhaka district.']);
        $stmtGallery->execute(['Coastal Mangrove Afforestation', '/assets/uploads/gallery/gallery-mangrove-forest.webp', '/assets/uploads/gallery/gallery-mangrove-forest.webp', 'assets/uploads/gallery/gallery-mangrove-forest.webp', 'assets/uploads/gallery/gallery-mangrove-forest.webp', 'ENVIRONMENT', 'Climate Adaptation', 'Lush Sundarbans mangrove green belt protecting tidal channels and delta biodiversity in Khulna.']);
        $stmtGallery->execute(['Buriganga Water Quality Sampling', '/assets/uploads/gallery/gallery-water-testing.webp', '/assets/uploads/gallery/gallery-water-testing.webp', 'assets/uploads/gallery/gallery-water-testing.webp', 'assets/uploads/gallery/gallery-water-testing.webp', 'RESEARCH', 'Youth Mobilization', 'Field testing team recording pH, dissolved oxygen, and heavy metal levels in urban waterways.']);
        $stmtGallery->execute(['Community Native Sapling Nursery', '/assets/uploads/gallery/gallery-nursery-saplings.webp', '/assets/uploads/gallery/gallery-nursery-saplings.webp', 'assets/uploads/gallery/gallery-nursery-saplings.webp', 'assets/uploads/gallery/gallery-nursery-saplings.webp', 'DEFORESTATION', 'Reforestation', 'Preparing 50,000 indigenous saplings for monsoon distribution across 64 districts.']);
        $stmtGallery->execute(['Renewable Solar Micro-Grid Deployment', '/assets/uploads/gallery/gallery-solar-delta.webp', '/assets/uploads/gallery/gallery-solar-delta.webp', 'assets/uploads/gallery/gallery-solar-delta.webp', 'assets/uploads/gallery/gallery-solar-delta.webp', 'CLIMATE', 'Climate Adaptation', 'Clean solar energy arrays powering cyclone shelters and rural irrigation in coastal Bangladesh.']);

        // 4b. Seed Hero Slides (3 distinct high-res category WebP banners)
        $stmtSlides = $db->prepare("INSERT INTO hero_slides (title, subtitle, badge_text, image_path, cta_text, cta_url, secondary_cta_text, secondary_cta_url, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmtSlides->execute([
            'Nationwide Youth Climate March & Mangrove Defense Campaign',
            'Mobilizing over 1,420 youth advocates across Dhaka, Khulna, and Bagerhat to raise awareness on rising sea levels and demand sustainable coastal protection policies.',
            'Live Initiative',
            'assets/uploads/slider/slide-climate-mangrove.webp',
            'Join This Action',
            BASE_URL . '/activities.php',
            'Become a Member',
            BASE_URL . '/join.php',
            1
        ]);
        $stmtSlides->execute([
            'Buriganga River Waste Cleanup & Plastic-Free Cities Drive',
            'A grassroots mobilization removing 12.5 tons of non-biodegradable industrial and plastic waste from 8 high-density points along the Buriganga riverbank.',
            'Pollution Control',
            'assets/uploads/slider/slide-river-cleanup.webp',
            'Read Field Research',
            BASE_URL . '/researches.php',
            'View Gallery',
            BASE_URL . '/gallery.php',
            2
        ]);
        $stmtSlides->execute([
            'Youth Climate Mobilization Across 64 Districts',
            'Sofiq Organization grassroots network training students to lead environmental clubs, urban oxygen belts, and river audits.',
            'Nationwide Movement',
            'assets/uploads/slider/slide-youth-planting.webp',
            'Become a Member',
            BASE_URL . '/join.php',
            'Contact Secretariat',
            BASE_URL . '/contact.php',
            3
        ]);

        // 5. Seed Realistic Visitor Logs (Android, iPhone, Desktop) to populate analytics
        $stmtView = $db->prepare("INSERT INTO post_view_logs (post_id, page_url, visitor_hash, device_type, browser_info, os_info, ip_masked, referrer_source, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $devices = ['ANDROID', 'IPHONE', 'DESKTOP'];
        $browsers = ['Chrome Mobile', 'Safari Mobile', 'Chrome Desktop', 'Firefox Desktop'];
        $sources = ['Google Search', 'Facebook Advocacy Group', 'Direct Visit', 'Twitter/X'];
        
        for ($i = 0; $i < 30; $i++) {
            $pId = ($i % 3 == 0) ? $researchPostId : $postId1;
            $dev = $devices[$i % count($devices)];
            $stmtView->execute([
                $pId,
                '/researches',
                'visitor_' . bin2hex(random_bytes(4)),
                $dev,
                $browsers[$i % count($browsers)],
                ($dev === 'ANDROID' ? 'Android 14' : ($dev === 'IPHONE' ? 'iOS 18' : 'Windows 11')),
                '103.205.132.***',
                $sources[$i % count($sources)],
                date('Y-m-d H:i:s', strtotime('-' . ($i * 2) . ' hours'))
            ]);
        }
    }

    private static function seedDefaultComments(PDO $db): void {
        try {
            $count = (int)$db->query("SELECT COUNT(*) FROM post_comments")->fetchColumn();
            if ($count > 0) {
                return;
            }
            $posts = $db->query("SELECT id FROM posts ORDER BY id ASC")->fetchAll(PDO::FETCH_COLUMN);
            if (empty($posts)) {
                return;
            }

            $stmt = $db->prepare("INSERT INTO post_comments (post_id, user_id, parent_id, author_name, author_role, author_avatar, comment_text, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'APPROVED', ?)");

            foreach ($posts as $idx => $pId) {
                // Top-level comment 1
                $stmt->execute([
                    $pId,
                    2,
                    0,
                    'Sofiqul Alam',
                    'Lead Environmental Researcher',
                    'avatar-researcher.webp',
                    'Thank you to all the student volunteers and community coordinators who contributed field measurements for this initiative. Our dataset from the Hatirpool secretariat is now open for district-level replication.',
                    date('Y-m-d H:i:s', strtotime('-18 hours'))
                ]);
                $parent1 = (int)$db->lastInsertId();

                // Reply to comment 1
                $stmt->execute([
                    $pId,
                    1,
                    $parent1,
                    'A Greener Tomorrow Admin',
                    'Central Executive Director',
                    'avatar-admin.webp',
                    'Appreciate the rigorous field work, Sofiqul! We have scheduled the next volunteer briefing at our Hatirpool HQ this Thursday at 4:00 PM for any registered members who want to join the upcoming phase.',
                    date('Y-m-d H:i:s', strtotime('-12 hours'))
                ]);

                // Top-level comment 2
                $stmt->execute([
                    $pId,
                    null,
                    0,
                    'Nusrat Jahan Mim',
                    'Youth Climate Member (Khulna)',
                    'avatar-researcher.webp',
                    'This report is incredibly helpful for our university environmental club. Can our district chapter request the field sampling toolkit for our local river canal audit next month?',
                    date('Y-m-d H:i:s', strtotime('-6 hours'))
                ]);
                $parent2 = (int)$db->lastInsertId();

                // Reply to comment 2
                $stmt->execute([
                    $pId,
                    2,
                    $parent2,
                    'Sofiqul Alam',
                    'Lead Environmental Researcher',
                    'avatar-researcher.webp',
                    'Absolutely, Nusrat! Sign in to your Member Dashboard or message the Central Secretariat and we will dispatch the water testing protocol and sapling allocation list to your Khulna chapter.',
                    date('Y-m-d H:i:s', strtotime('-3 hours'))
                ]);
            }
        } catch (PDOException $e) {
            error_log("Seed comments error: " . $e->getMessage());
        }
    }
}
