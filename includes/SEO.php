<?php
/**
 * A Greener Tomorrow (greener2morow.org)
 * Dynamic SEO Engine: Semantic Meta, OpenGraph & JSON-LD Structured Data
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';

class SEO {

    public static function render(array $opts = []): void {
        echo self::renderTags(
            (string)($opts['title'] ?? ''),
            (string)($opts['description'] ?? ''),
            (string)($opts['image'] ?? ''),
            (string)($opts['type'] ?? 'website'),
            (string)($opts['url'] ?? '')
        );
        echo "\n" . self::renderNgoSchema() . "\n";
    }

    public static function renderTags(
        string $pageTitle = '',
        string $description = '',
        string $ogImage = '',
        string $type = 'website',
        string $canonical = ''
    ): string {
        $settings = get_site_settings();

        $title = !empty($pageTitle) 
            ? htmlspecialchars($pageTitle . ' — ' . $settings['org_name'], ENT_QUOTES, 'UTF-8')
            : htmlspecialchars($settings['org_name'] . ' — ' . $settings['tagline'], ENT_QUOTES, 'UTF-8');

        $desc = htmlspecialchars($description ?: $settings['tagline'], ENT_QUOTES, 'UTF-8');
        $img = htmlspecialchars($ogImage ?: url('assets/brand/template-mockup.jpg'), ENT_QUOTES, 'UTF-8');
        $can = htmlspecialchars($canonical ?: (BASE_URL . ($_SERVER['REQUEST_URI'] ?? '/')), ENT_QUOTES, 'UTF-8');

        $html = "<!-- Primary SEO Meta Tags -->\n";
        $html .= "<title>{$title}</title>\n";
        $html .= "<meta name=\"description\" content=\"{$desc}\">\n";
        $html .= "<link rel=\"canonical\" href=\"{$can}\">\n";

        $html .= "\n<!-- OpenGraph / Facebook -->\n";
        $html .= "<meta property=\"og:type\" content=\"{$type}\">\n";
        $html .= "<meta property=\"og:url\" content=\"{$can}\">\n";
        $html .= "<meta property=\"og:title\" content=\"{$title}\">\n";
        $html .= "<meta property=\"og:description\" content=\"{$desc}\">\n";
        $html .= "<meta property=\"og:image\" content=\"{$img}\">\n";
        $html .= "<meta property=\"og:site_name\" content=\"{$settings['org_name']}\">\n";

        $html .= "\n<!-- Twitter Cards -->\n";
        $html .= "<meta name=\"twitter:card\" content=\"summary_large_image\">\n";
        $html .= "<meta name=\"twitter:title\" content=\"{$title}\">\n";
        $html .= "<meta name=\"twitter:description\" content=\"{$desc}\">\n";
        $html .= "<meta name=\"twitter:image\" content=\"{$img}\">\n";

        return $html;
    }

    public static function renderNgoSchema(): string {
        $settings = get_site_settings();
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "NGO",
            "name" => $settings['org_name'],
            "alternateName" => "Sofiq Organization Action Model",
            "url" => $settings['website'],
            "logo" => url($settings['logo_url'] ?: 'assets/brand/brand-logo.png'),
            "email" => $settings['email'],
            "telephone" => $settings['phone'],
            "address" => [
                "@type" => "PostalAddress",
                "streetAddress" => "36, Bir Uttam C.R Dutta Road, Hatirpool",
                "addressLocality" => "Dhaka",
                "postalCode" => "1205",
                "addressCountry" => "BD"
            ],
            "areaServed" => "Bangladesh",
            "sameAs" => array_filter([$settings['facebook_url']])
        ];

        return "<script type=\"application/ld+json\">\n" . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n</script>";
    }
}

