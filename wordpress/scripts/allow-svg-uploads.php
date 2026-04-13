<?php
/**
 * Plugin Name: Local SVG Upload Support
 * Description: Allows SVG uploads for the local assignment seed content.
 */

add_filter(
    'upload_mimes',
    static function (array $mimes): array {
        $mimes['svg'] = 'image/svg+xml';

        return $mimes;
    }
);

add_filter(
    'wp_check_filetype_and_ext',
    static function (array $data, string $file, string $filename, $mimes): array {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($extension !== 'svg') {
            return $data;
        }

        return [
            'ext' => 'svg',
            'type' => 'image/svg+xml',
            'proper_filename' => $filename,
        ];
    },
    10,
    4
);
