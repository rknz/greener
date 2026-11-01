<?php
/**
 * A Greener Tomorrow - Central Secretariat Contact Page
 * Address: 36, Bir Uttam C.R Dutta Road, Hatirpool, Dhaka-1205
 */

require_once __DIR__ . '/config/config.php';
require_once AGY_ROOT . '/includes/functions.php';
require_once AGY_ROOT . '/includes/Tracker.php';

// Log visitor session
Tracker::trackVisit();

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errorMsg = 'Security validation failed (CSRF token mismatch). Please refresh and try again.';
    } else {
        $name = sanitize_text($_POST['name'] ?? '');
        $email = sanitize_text($_POST['email'] ?? '');
        $phone = sanitize_text($_POST['phone'] ?? '');
        $subject = sanitize_text($_POST['subject'] ?? 'General Environmental Inquiry');
        $message = sanitize_text($_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            $errorMsg = 'Please complete all required fields (Name, Email, Message).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = 'Please enter a valid email address.';
        } else {
            $db = Database::getConnection();
            try {
                $stmt = $db->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at) VALUES (?, ?, ?, ?, ?, 'unread', datetime('now'))");
                $stmt->execute([$name, $email, $phone, $subject, $message]);
                $successMsg = 'Thank you! Your message has been received by our Hatirpool secretariat team. We will get back to you shortly.';
            } catch (PDOException $e) {
                $errorMsg = 'Database error while transmitting message. Please try again.';
                error_log("Contact message error: " . $e->getMessage());
            }
        }
    }
}

$pageTitle = 'Contact Secretariat | A Greener Tomorrow';
$pageDescription = 'Get in touch with the Sofiq Organization Central Secretariat at 36, Bir Uttam C.R Dutta Road, Hatirpool, Dhaka-1205, Bangladesh.';

require_once AGY_ROOT . '/views/partials/header.php';
?>

<div class="page-header-banner">
    <div class="container">
        <span class="badge badge-accent"><i class="fa-solid fa-headset"></i> Get In Touch</span>
        <h1 class="page-banner-title">Contact Central Secretariat</h1>
        <p class="page-banner-subtitle">Have a question regarding local mobilization, research collaborations, or youth chapters? Our Dhaka Hatirpool team is here to assist.</p>
    </div>
</div>

<div class="container py-5">
    <div class="golden-grid">
        <!-- Major Column: Contact Form (61.8%) -->
        <div class="golden-col-major">
            <?php if (!empty($successMsg)): ?>
                <div class="alert alert-success alert-dismissible mb-4">
                    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-danger alert-dismissible mb-4">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <div class="contact-form-card">
                <h3 class="mb-2">Send an Inquiry or Proposal</h3>
                <p class="text-muted mb-4">We review all messages within 24 to 48 business hours.</p>

                <form action="<?= BASE_URL ?>/contact.php" method="POST" class="site-form">
                    <?= csrf_field() ?>

                    <div class="form-row-2">
                        <div class="form-group mb-3">
                            <label for="contact_name" class="form-label">Full Name *</label>
                            <input type="text" id="contact_name" name="name" class="form-control" required placeholder="e.g. Tanvir Ahmed">
                        </div>
                        <div class="form-group mb-3">
                            <label for="contact_email" class="form-label">Email Address *</label>
                            <input type="email" id="contact_email" name="email" class="form-control" required placeholder="tanvir@example.com">
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group mb-3">
                            <label for="contact_phone" class="form-label">Phone / WhatsApp Number</label>
                            <input type="tel" id="contact_phone" name="phone" class="form-control" placeholder="+880 1712-345678">
                        </div>
                        <div class="form-group mb-3">
                            <label for="contact_subject" class="form-label">Subject</label>
                            <select id="contact_subject" name="subject" class="form-control">
                                <option value="General Environmental Inquiry">General Environmental Inquiry</option>
                                <option value="Youth Volunteer Mobilization">Youth Volunteer Mobilization</option>
                                <option value="Research & Policy Collaboration">Research & Policy Collaboration</option>
                                <option value="Tree Plantation Campaign">Tree Plantation Campaign</option>
                                <option value="Media & Press Inquiry">Media & Press Inquiry</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="contact_message" class="form-label">Message / Details *</label>
                        <textarea id="contact_message" name="message" class="form-control" rows="5" required placeholder="Describe your initiative, request, or inquiry..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-paper-plane"></i> Transmit Message
                    </button>
                </form>
            </div>
        </div>

        <!-- Minor Column: Secretariat Details & Office Info (38.2%) -->
        <div class="golden-col-minor">
            <div class="sidebar-sticky-wrap">
                <div class="hq-info-card">
                    <span class="badge badge-primary mb-3">Central Headquarters</span>
                    <h3>Sofiq Organization HQ</h3>
                    <p class="text-muted">Directing operations, field volunteers, and policy research across Bangladesh.</p>

                    <div class="hq-contact-details mt-4">
                        <div class="hq-item">
                            <i class="fa-solid fa-location-dot hq-icon"></i>
                            <div>
                                <strong>Physical Address:</strong>
                                <p>36, Bir Uttam C.R Dutta Road, Hatirpool, Dhaka-1205, Bangladesh</p>
                            </div>
                        </div>

                        <div class="hq-item">
                            <i class="fa-solid fa-phone hq-icon"></i>
                            <div>
                                <strong>Hotline:</strong>
                                <p><a href="tel:+8801712345678">+880 1712-345678</a></p>
                            </div>
                        </div>

                        <div class="hq-item">
                            <i class="fa-solid fa-envelope hq-icon"></i>
                            <div>
                                <strong>Direct Secretariat:</strong>
                                <p><a href="mailto:info@greener2morow.org">info@greener2morow.org</a></p>
                            </div>
                        </div>

                        <div class="hq-item">
                            <i class="fa-solid fa-clock hq-icon"></i>
                            <div>
                                <strong>Operating Hours:</strong>
                                <p>Sunday &ndash; Thursday: 9:00 AM &ndash; 6:00 PM (GMT+6)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="meta-sidebar-card mt-4">
                    <h4>Regional Hubs</h4>
                    <p class="text-muted small">Field offices active in Chittagong, Khulna (Sundarbans Delta), Sylhet (Tea Belt), and Rajshahi (Barind Tract).</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once AGY_ROOT . '/views/partials/footer.php'; ?>

