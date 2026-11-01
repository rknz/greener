<?php
/**
 * A Greener Tomorrow - Contributor Profile Box Partial
 * Displays verified contributor details: Name, Photo, Contact Info, Role, and Bio.
 *
 * @param array $contributor Array containing author/contributor fields
 */

if (!defined('AGY_ROOT')) {
    exit('Direct access not permitted.');
}

if (!isset($contributor) || !is_array($contributor)) {
    return;
}

$name = !empty($contributor['full_name']) ? $contributor['full_name'] : (!empty($contributor['author_name']) ? $contributor['author_name'] : (!empty($contributor['username']) ? $contributor['username'] : 'Sofiq Mobilizer'));
$role = !empty($contributor['role']) ? ucfirst($contributor['role']) : 'Environmental Action Member';
if ($role === 'Admin') $role = 'Chief Mobilization Officer (Admin)';
if ($role === 'Researcher') $role = 'Senior Climate Research Fellow';

$bio = !empty($contributor['bio']) ? $contributor['bio'] : 'Dedicated environmental activist mobilising youth for climate resilience and sustainable action across Bangladesh under the Sofiq Organization framework.';

$avatar = !empty($contributor['avatar']) ? BASE_URL . '/assets/uploads/avatars/' . htmlspecialchars($contributor['avatar'], ENT_QUOTES, 'UTF-8') : (!empty($contributor['author_avatar']) ? BASE_URL . '/assets/uploads/avatars/' . htmlspecialchars($contributor['author_avatar'], ENT_QUOTES, 'UTF-8') : BASE_URL . '/assets/brand/brand-logo.png');

$email = $contributor['email'] ?? 'contact@greener2morow.org';
$phone = $contributor['phone'] ?? '+880 1712-345678';
$city = $contributor['city'] ?? 'Dhaka, Bangladesh';
?>

<div class="contributor-profile-card">
    <div class="contributor-card-header">
        <span class="contributor-card-badge"><i class="fa-solid fa-certificate"></i> Verified Contributor</span>
    </div>
    
    <div class="contributor-card-body">
        <div class="contributor-avatar-col">
            <img src="<?= $avatar ?>" alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" class="contributor-avatar-lg" width="96" height="96" loading="lazy">
        </div>
        
        <div class="contributor-info-col">
            <h4 class="contributor-name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h4>
            <span class="contributor-role"><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?> &bull; <?= htmlspecialchars($city, ENT_QUOTES, 'UTF-8') ?></span>
            
            <p class="contributor-bio"><?= htmlspecialchars($bio, ENT_QUOTES, 'UTF-8') ?></p>
            
            <!-- Contributor Contact Information -->
            <div class="contributor-contact-details">
                <?php if (!empty($email)): ?>
                    <a href="mailto:<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" class="contributor-contact-item" title="Send Email">
                        <i class="fa-regular fa-envelope"></i> <?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endif; ?>
                
                <?php if (!empty($phone)): ?>
                    <a href="tel:<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>" class="contributor-contact-item" title="Call Contributor">
                        <i class="fa-solid fa-phone"></i> <?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

