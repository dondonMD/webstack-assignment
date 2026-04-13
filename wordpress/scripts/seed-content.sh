#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

HOME_ID="$(wp post list --post_type=page --name=home --field=ID)"
ABOUT_ID="$(wp post list --post_type=page --name=about --field=ID)"
CONTACT_ID="$(wp post list --post_type=page --name=contact --field=ID)"
ADMIN_ID="$(wp user get admin --field=ID)"

wp option update page_on_front "$HOME_ID"

wp menu create "Primary Navigation" >/dev/null 2>&1 || true
wp menu item add-post primary-navigation "$HOME_ID" --title="Home" >/dev/null
wp menu item add-post primary-navigation "$ABOUT_ID" --title="About" >/dev/null
wp menu item add-post primary-navigation "$CONTACT_ID" --title="Contact" >/dev/null
wp menu location assign primary-navigation primary >/dev/null 2>&1 || true

export ADMIN_ID

php <<'PHP'
<?php
$posts = json_decode(file_get_contents('/seed/posts.json'), true, 512, JSON_THROW_ON_ERROR);
$authorId = (int) (getenv('ADMIN_ID') ?: 1);

foreach ($posts as $post) {
    $title = escapeshellarg($post['title']);
    $slug = escapeshellarg($post['slug']);
    $excerpt = escapeshellarg($post['excerpt']);
    $content = escapeshellarg($post['content']);
    $image = escapeshellarg('/seed-images/' . $post['image']);
    $existingIds = trim(shell_exec("wp post list --post_type=post --name=$slug --field=ID"));

    if ($existingIds !== '') {
        foreach (preg_split('/\s+/', $existingIds) as $existingId) {
            if ($existingId !== '') {
                shell_exec("wp post delete " . escapeshellarg($existingId) . " --force >/dev/null 2>&1");
            }
        }
    }

    $postId = trim(shell_exec(
        "wp post create --post_type=post --post_status=publish --post_author=$authorId --post_title=$title --post_name=$slug --post_excerpt=$excerpt --post_content=$content --porcelain 2>&1"
    ));

    if ($postId === '' || !ctype_digit($postId)) {
        throw new RuntimeException("Failed to create post for slug {$post['slug']}: {$postId}");
    }

    $imageId = trim(shell_exec(
        "wp media import $image --title=$title --post_id=$postId --featured_image --porcelain 2>&1"
    ));

    if ($imageId === '' || !ctype_digit($imageId)) {
        fwrite(STDERR, "Warning: failed to import featured image for {$post['slug']}: {$imageId}\n");
    }
}
PHP
