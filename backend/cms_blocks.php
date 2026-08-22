<?php
/**
 * Section blocks for the page designer.
 *
 * A page's layout is stored as a JSON array of blocks on `cms_pages.sections`.
 * One definition table below drives three things at once: the fields the admin
 * builder shows, the validation applied on save, and the markup rendered on the
 * public page. Adding a block type means adding one entry and one renderer.
 */
declare(strict_types=1);

require_once __DIR__ . '/cms.php';

/**
 * Field types understood by the builder:
 *   text | textarea | html | url | image | select | number
 */
function cms_block_types(): array
{
    static $types = null;
    if ($types !== null) {
        return $types;
    }

    $button = [
        'button_label' => ['label' => 'Button text', 'type' => 'text'],
        'button_url' => ['label' => 'Button link', 'type' => 'url'],
    ];

    $types = [
        'rich_text' => [
            'label' => 'Text',
            'hint' => 'A heading and a block of formatted text.',
            'fields' => [
                'eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
                'heading' => ['label' => 'Heading', 'type' => 'text'],
                'body' => ['label' => 'Text', 'type' => 'html', 'rows' => 6],
                'align' => ['label' => 'Alignment', 'type' => 'select', 'options' => ['left' => 'Left', 'center' => 'Centred']],
            ],
        ],

        'image_text' => [
            'label' => 'Image + text',
            'hint' => 'A picture beside a paragraph. The classic section.',
            'fields' => [
                'image' => ['label' => 'Image', 'type' => 'image'],
                'image_alt' => ['label' => 'Image description (alt text)', 'type' => 'text'],
                'image_position' => ['label' => 'Image on the', 'type' => 'select', 'options' => ['left' => 'Left', 'right' => 'Right']],
                'eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
                'heading' => ['label' => 'Heading', 'type' => 'text'],
                'body' => ['label' => 'Text', 'type' => 'html', 'rows' => 5],
            ] + $button,
        ],

        'banner' => [
            'label' => 'Banner',
            'hint' => 'A wide image with a heading over it. Good as a page opener.',
            'fields' => [
                'image' => ['label' => 'Background image', 'type' => 'image'],
                'image_alt' => ['label' => 'Image description (alt text)', 'type' => 'text'],
                'heading' => ['label' => 'Heading', 'type' => 'text'],
                'body' => ['label' => 'Sub heading', 'type' => 'textarea', 'rows' => 2],
                'height' => ['label' => 'Height', 'type' => 'select', 'options' => ['normal' => 'Normal', 'tall' => 'Tall', 'short' => 'Short']],
            ] + $button,
        ],

        'cards' => [
            'label' => 'Card grid',
            'hint' => 'Two to four columns of cards, each with its own image or icon.',
            'fields' => [
                'eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
                'heading' => ['label' => 'Heading', 'type' => 'text'],
                'body' => ['label' => 'Intro text', 'type' => 'textarea', 'rows' => 2],
                'columns' => ['label' => 'Columns', 'type' => 'select', 'options' => ['2' => '2 across', '3' => '3 across', '4' => '4 across']],
            ],
            'items' => [
                'label' => 'Cards',
                'add_label' => 'Add card',
                'fields' => [
                    'image' => ['label' => 'Image', 'type' => 'image'],
                    'icon' => ['label' => 'Icon or symbol (used when there is no image)', 'type' => 'text'],
                    'title' => ['label' => 'Card title', 'type' => 'text'],
                    'text' => ['label' => 'Card text', 'type' => 'textarea', 'rows' => 3],
                    'link_label' => ['label' => 'Link text', 'type' => 'text'],
                    'link_url' => ['label' => 'Link address', 'type' => 'url'],
                ],
            ],
        ],

        'gallery' => [
            'label' => 'Image gallery',
            'hint' => 'A grid of pictures with optional captions.',
            'fields' => [
                'heading' => ['label' => 'Heading', 'type' => 'text'],
                'columns' => ['label' => 'Columns', 'type' => 'select', 'options' => ['2' => '2 across', '3' => '3 across', '4' => '4 across']],
            ],
            'items' => [
                'label' => 'Images',
                'add_label' => 'Add image',
                'fields' => [
                    'image' => ['label' => 'Image', 'type' => 'image'],
                    'caption' => ['label' => 'Caption', 'type' => 'text'],
                    'alt' => ['label' => 'Image description (alt text)', 'type' => 'text'],
                ],
            ],
        ],

        'stats' => [
            'label' => 'Statistics',
            'hint' => 'A row of numbers that count up as the visitor scrolls.',
            'fields' => [
                'heading' => ['label' => 'Heading', 'type' => 'text'],
            ],
            'items' => [
                'label' => 'Figures',
                'add_label' => 'Add figure',
                'fields' => [
                    'number' => ['label' => 'Number', 'type' => 'number'],
                    'suffix' => ['label' => 'Suffix', 'type' => 'text'],
                    'label' => ['label' => 'Label', 'type' => 'text'],
                ],
            ],
        ],

        'faq' => [
            'label' => 'Questions and answers',
            'hint' => 'An expandable list. Also produces FAQ structured data for Google.',
            'fields' => [
                'eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
                'heading' => ['label' => 'Heading', 'type' => 'text'],
            ],
            'items' => [
                'label' => 'Questions',
                'add_label' => 'Add question',
                'fields' => [
                    'question' => ['label' => 'Question', 'type' => 'text'],
                    'answer' => ['label' => 'Answer', 'type' => 'html', 'rows' => 3],
                ],
            ],
        ],

        'quote' => [
            'label' => 'Quote',
            'hint' => 'A pulled-out quote or client statement.',
            'fields' => [
                'body' => ['label' => 'Quote', 'type' => 'textarea', 'rows' => 3],
                'author' => ['label' => 'Attributed to', 'type' => 'text'],
                'role' => ['label' => 'Role or company', 'type' => 'text'],
                'image' => ['label' => 'Photo', 'type' => 'image'],
            ],
        ],

        'logos' => [
            'label' => 'Logo strip',
            'hint' => 'A row of client or partner logos.',
            'fields' => [
                'heading' => ['label' => 'Heading', 'type' => 'text'],
            ],
            'items' => [
                'label' => 'Logos',
                'add_label' => 'Add logo',
                'fields' => [
                    'image' => ['label' => 'Logo image', 'type' => 'image'],
                    'alt' => ['label' => 'Company name', 'type' => 'text'],
                    'url' => ['label' => 'Website link', 'type' => 'url'],
                ],
            ],
        ],

        'video' => [
            'label' => 'Video',
            'hint' => 'A YouTube video. Paste the link or just the video ID.',
            'fields' => [
                'heading' => ['label' => 'Heading', 'type' => 'text'],
                'youtube' => ['label' => 'YouTube link or ID', 'type' => 'text'],
                'caption' => ['label' => 'Caption', 'type' => 'text'],
            ],
        ],

        'cta' => [
            'label' => 'Call to action',
            'hint' => 'A panel that asks the visitor to do something.',
            'fields' => [
                'heading' => ['label' => 'Heading', 'type' => 'text'],
                'body' => ['label' => 'Text', 'type' => 'textarea', 'rows' => 2],
                'style' => ['label' => 'Style', 'type' => 'select', 'options' => ['soft' => 'Soft', 'dark' => 'Dark']],
            ] + $button,
        ],

        'divider' => [
            'label' => 'Divider / spacing',
            'hint' => 'Blank space, with or without a line.',
            'fields' => [
                'style' => ['label' => 'Style', 'type' => 'select', 'options' => ['line' => 'Line', 'space' => 'Blank space']],
                'size' => ['label' => 'Size', 'type' => 'select', 'options' => ['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large']],
            ],
        ],
    ];

    return $types;
}

/** Every block gains a soft background when this is ticked. */
function cms_block_common_fields(): array
{
    return [
        'background' => ['label' => 'Background', 'type' => 'select', 'options' => ['none' => 'Plain', 'soft' => 'Soft tint', 'dark' => 'Dark']],
    ];
}

/** Images may only be site-relative paths or absolute https URLs. */
function cms_safe_asset(?string $path): string
{
    $path = trim($path ?? '');
    if ($path === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $path) === 1) {
        return $path;
    }
    if (str_starts_with($path, '//') || preg_match('#^[a-z][a-z0-9+.\-]*:#i', $path) === 1) {
        return '';
    }

    return ltrim($path, '/');
}

/** Pulls the 11-character video id out of any YouTube URL form. */
function cms_youtube_id(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    if (preg_match('#(?:youtu\.be/|v=|embed/|shorts/)([A-Za-z0-9_-]{6,20})#', $value, $m) === 1) {
        return $m[1];
    }

    return preg_match('#^[A-Za-z0-9_-]{6,20}$#', $value) === 1 ? $value : '';
}

/**
 * Validates submitted or stored blocks against the definitions, dropping
 * unknown types and fields and sanitising every value by its declared type.
 */
function cms_sections_normalise(mixed $input): array
{
    if (is_string($input)) {
        $input = json_decode($input, true);
    }
    if (!is_array($input)) {
        return [];
    }

    $types = cms_block_types();
    $common = cms_block_common_fields();
    $clean = [];

    foreach ($input as $block) {
        if (!is_array($block)) {
            continue;
        }
        $type = (string) ($block['type'] ?? '');
        if (!isset($types[$type])) {
            continue;
        }

        $definition = $types[$type];
        $row = ['type' => $type];

        foreach (($definition['fields'] ?? []) + $common as $name => $field) {
            $row[$name] = cms_block_value($block[$name] ?? '', $field);
        }

        if (isset($definition['items'])) {
            $row['items'] = [];
            foreach ((array) ($block['items'] ?? []) as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $entry = [];
                $filled = false;
                foreach ($definition['items']['fields'] as $name => $field) {
                    $entry[$name] = cms_block_value($item[$name] ?? '', $field);
                    if ($entry[$name] !== '') {
                        $filled = true;
                    }
                }
                if ($filled) {
                    $row['items'][] = $entry;
                }
            }
        }

        $clean[] = $row;
    }

    return $clean;
}

function cms_block_value(mixed $value, array $field): string
{
    $value = is_string($value) ? trim($value) : '';

    return match ($field['type']) {
        'html' => cms_sanitize_html($value),
        'image' => cms_safe_asset($value),
        'url' => $value === '' ? '' : cms_safe_link($value, ''),
        'number' => $value === '' ? '' : (string) (float) $value,
        'select' => isset($field['options'][$value]) ? $value : (string) array_key_first($field['options']),
        default => $value,
    };
}

/* =====================================================================
 * Rendering
 * ================================================================== */

function cms_render_sections(array $sections): string
{
    $out = [];
    foreach ($sections as $index => $block) {
        $out[] = cms_render_block($block, $index);
    }

    return implode("\n", $out);
}

function cms_render_block(array $block, int $index): string
{
    $type = (string) ($block['type'] ?? '');
    $background = (string) ($block['background'] ?? 'none');
    $classes = 'section pb-block pb-' . preg_replace('/[^a-z_]/', '', $type);
    if ($background === 'soft') {
        $classes .= ' section-soft';
    } elseif ($background === 'dark') {
        $classes .= ' pb-dark';
    }

    $body = match ($type) {
        'rich_text' => cms_block_rich_text($block),
        'image_text' => cms_block_image_text($block),
        'banner' => cms_block_banner($block),
        'cards' => cms_block_cards($block),
        'gallery' => cms_block_gallery($block),
        'stats' => cms_block_stats($block),
        'faq' => cms_block_faq($block, $index),
        'quote' => cms_block_quote($block),
        'logos' => cms_block_logos($block),
        'video' => cms_block_video($block),
        'cta' => cms_block_cta($block),
        'divider' => cms_block_divider($block),
        default => '',
    };

    if ($body === '') {
        return '';
    }
    if ($type === 'banner' || $type === 'divider') {
        return $body;
    }

    return '<section class="' . e($classes) . '"><div class="container">' . $body . '</div></section>';
}

/** Shared eyebrow + heading + intro used by several blocks. */
function cms_block_head(array $block, bool $center = false): string
{
    $out = '';
    if (($block['eyebrow'] ?? '') !== '') {
        $out .= '<p class="eyebrow">' . e((string) $block['eyebrow']) . '</p>';
    }
    if (($block['heading'] ?? '') !== '') {
        $out .= '<h2>' . e((string) $block['heading']) . '</h2>';
    }
    if (($block['body'] ?? '') !== '' && in_array((string) $block['type'], ['cards', 'banner', 'cta'], true)) {
        $out .= '<p class="section-copy">' . nl2br(e((string) $block['body'])) . '</p>';
    }

    return $out === '' ? '' : '<div class="section-head reveal' . ($center ? ' center' : '') . '">' . $out . '</div>';
}

function cms_block_button(array $block, string $class = 'button button-primary'): string
{
    $label = (string) ($block['button_label'] ?? '');
    $url = cms_safe_link((string) ($block['button_url'] ?? ''), '');
    if ($label === '' || $url === '') {
        return '';
    }

    return '<a class="' . e($class) . '" href="' . e($url) . '">' . e($label) . ' <span class="button-arrow" aria-hidden="true">&#8599;</span></a>';
}

function cms_block_rich_text(array $block): string
{
    $align = (string) ($block['align'] ?? 'left');
    $out = cms_block_head($block, $align === 'center');
    if (($block['body'] ?? '') !== '') {
        $out .= '<div class="post-body reveal">' . cms_sanitize_html((string) $block['body']) . '</div>';
    }

    return $out === '' ? '' : '<div class="pb-text' . ($align === 'center' ? ' pb-center' : '') . '">' . $out . '</div>';
}

function cms_block_image_text(array $block): string
{
    $image = cms_safe_asset((string) ($block['image'] ?? ''));
    $position = (string) ($block['image_position'] ?? 'left');

    $media = $image === '' ? '' :
        '<div class="pb-media"><img src="' . e($image) . '" alt="' . e((string) ($block['image_alt'] ?? '')) . '" loading="lazy" decoding="async" /></div>';

    $text = '<div class="pb-copy">' . cms_block_head($block);
    if (($block['body'] ?? '') !== '') {
        $text .= '<div class="post-body">' . cms_sanitize_html((string) $block['body']) . '</div>';
    }
    $text .= cms_block_button($block, 'button button-secondary') . '</div>';

    if ($image === '' && ($block['body'] ?? '') === '' && ($block['heading'] ?? '') === '') {
        return '';
    }

    $inner = $position === 'right' ? $text . $media : $media . $text;

    return '<div class="pb-media-text reveal' . ($position === 'right' ? ' is-reversed' : '') . '">' . $inner . '</div>';
}

function cms_block_banner(array $block): string
{
    $image = cms_safe_asset((string) ($block['image'] ?? ''));
    $heading = (string) ($block['heading'] ?? '');
    if ($image === '' && $heading === '') {
        return '';
    }

    $height = (string) ($block['height'] ?? 'normal');
    $button = cms_block_button($block);
    $text = '';
    if ($heading !== '') {
        $text .= '<h2>' . e($heading) . '</h2>';
    }
    if (($block['body'] ?? '') !== '') {
        $text .= '<p>' . nl2br(e((string) $block['body'])) . '</p>';
    }
    $text .= $button;
    $hasText = $text !== '';

    $classes = 'pb-banner pb-banner-' . $height;
    if ($image !== '') {
        $classes .= ' has-image';
    }
    if ($hasText) {
        $classes .= ' has-text';
    }

    $out = '<section class="' . e($classes) . '">';

    /* A real <img> rather than a background, so the whole picture is shown. */
    if ($image !== '') {
        $out .= '<img class="pb-banner-image" src="' . e($image) . '" alt="' . e((string) ($block['image_alt'] ?? '')) . '" loading="lazy" decoding="async" />';
        if ($hasText) {
            $out .= '<span class="pb-banner-veil" aria-hidden="true"></span>';
        }
    }
    if ($hasText) {
        $out .= '<div class="pb-banner-body"><div class="container">' . $text . '</div></div>';
    }

    return $out . '</section>';
}

function cms_block_cards(array $block): string
{
    $items = (array) ($block['items'] ?? []);
    if (!$items) {
        return '';
    }

    $columns = (string) ($block['columns'] ?? '3');
    $out = cms_block_head($block) . '<div class="pb-grid pb-cols-' . e($columns) . '">';

    foreach ($items as $index => $item) {
        $image = cms_safe_asset((string) ($item['image'] ?? ''));
        $link = cms_safe_link((string) ($item['link_url'] ?? ''), '');
        $out .= '<article class="pb-card reveal" data-delay="' . (($index % 4) * 70) . '">';
        if ($image !== '') {
            $out .= '<div class="pb-card-media"><img src="' . e($image) . '" alt="' . e((string) ($item['title'] ?? '')) . '" loading="lazy" decoding="async" /></div>';
        } elseif ((string) ($item['icon'] ?? '') !== '') {
            $out .= '<span class="service-icon" aria-hidden="true">' . e((string) $item['icon']) . '</span>';
        }
        $out .= '<div class="pb-card-body">';
        if ((string) ($item['title'] ?? '') !== '') {
            $out .= '<h3>' . e((string) $item['title']) . '</h3>';
        }
        if ((string) ($item['text'] ?? '') !== '') {
            $out .= '<p>' . nl2br(e((string) $item['text'])) . '</p>';
        }
        if ($link !== '' && (string) ($item['link_label'] ?? '') !== '') {
            $out .= '<a class="service-link" href="' . e($link) . '">' . e((string) $item['link_label']) . ' <span aria-hidden="true">&#8594;</span></a>';
        }
        $out .= '</div></article>';
    }

    return $out . '</div>';
}

function cms_block_gallery(array $block): string
{
    $items = (array) ($block['items'] ?? []);
    if (!$items) {
        return '';
    }

    $columns = (string) ($block['columns'] ?? '3');
    $out = cms_block_head($block) . '<div class="pb-gallery pb-cols-' . e($columns) . '">';
    foreach ($items as $item) {
        $image = cms_safe_asset((string) ($item['image'] ?? ''));
        if ($image === '') {
            continue;
        }
        $out .= '<figure class="reveal"><img src="' . e($image) . '" alt="' . e((string) ($item['alt'] ?? '')) . '" loading="lazy" decoding="async" />';
        if ((string) ($item['caption'] ?? '') !== '') {
            $out .= '<figcaption>' . e((string) $item['caption']) . '</figcaption>';
        }
        $out .= '</figure>';
    }

    return $out . '</div>';
}

function cms_block_stats(array $block): string
{
    $items = (array) ($block['items'] ?? []);
    if (!$items) {
        return '';
    }

    $out = cms_block_head($block, true) . '<div class="stats-grid pb-stats">';
    foreach ($items as $item) {
        $number = (string) ($item['number'] ?? '');
        $out .= '<div class="stat"><strong class="stat-number" data-count="' . e($number) . '"'
            . ((string) ($item['suffix'] ?? '') !== '' ? ' data-suffix="' . e((string) $item['suffix']) . '"' : '')
            . '>0</strong><span class="stat-label">' . e((string) ($item['label'] ?? '')) . '</span></div>';
    }

    return $out . '</div>';
}

function cms_block_faq(array $block, int $index): string
{
    $items = (array) ($block['items'] ?? []);
    if (!$items) {
        return '';
    }

    $out = cms_block_head($block) . '<div class="pb-faq">';
    foreach ($items as $position => $item) {
        if ((string) ($item['question'] ?? '') === '') {
            continue;
        }
        $out .= '<details class="pb-faq-item"' . ($position === 0 ? ' open' : '') . '>'
            . '<summary>' . e((string) $item['question']) . '</summary>'
            . '<div class="pb-faq-answer post-body">' . cms_sanitize_html((string) ($item['answer'] ?? '')) . '</div>'
            . '</details>';
    }

    return $out . '</div>';
}

/** Schema.org FAQ data, emitted once per page when an FAQ block exists. */
function cms_sections_faq_schema(array $sections): string
{
    $entities = [];
    foreach ($sections as $block) {
        if (($block['type'] ?? '') !== 'faq') {
            continue;
        }
        foreach ((array) ($block['items'] ?? []) as $item) {
            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim(strip_tags((string) ($item['answer'] ?? '')));
            if ($question === '' || $answer === '') {
                continue;
            }
            $entities[] = [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $answer],
            ];
        }
    }

    if (!$entities) {
        return '';
    }

    return (string) json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $entities,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function cms_block_quote(array $block): string
{
    if ((string) ($block['body'] ?? '') === '') {
        return '';
    }

    $image = cms_safe_asset((string) ($block['image'] ?? ''));
    $out = '<figure class="pb-quote reveal">';
    if ($image !== '') {
        $out .= '<img src="' . e($image) . '" alt="' . e((string) ($block['author'] ?? '')) . '" loading="lazy" />';
    }
    $out .= '<blockquote>' . nl2br(e((string) $block['body'])) . '</blockquote>';
    if ((string) ($block['author'] ?? '') !== '') {
        $out .= '<figcaption><strong>' . e((string) $block['author']) . '</strong>'
            . ((string) ($block['role'] ?? '') !== '' ? '<span>' . e((string) $block['role']) . '</span>' : '')
            . '</figcaption>';
    }

    return $out . '</figure>';
}

function cms_block_logos(array $block): string
{
    $items = (array) ($block['items'] ?? []);
    if (!$items) {
        return '';
    }

    $out = cms_block_head($block, true) . '<div class="pb-logos">';
    foreach ($items as $item) {
        $image = cms_safe_asset((string) ($item['image'] ?? ''));
        if ($image === '') {
            continue;
        }
        $img = '<img src="' . e($image) . '" alt="' . e((string) ($item['alt'] ?? '')) . '" loading="lazy" />';
        $url = cms_safe_link((string) ($item['url'] ?? ''), '');
        $out .= $url !== ''
            ? '<a href="' . e($url) . '" target="_blank" rel="noopener">' . $img . '</a>'
            : '<span>' . $img . '</span>';
    }

    return $out . '</div>';
}

function cms_block_video(array $block): string
{
    $id = cms_youtube_id((string) ($block['youtube'] ?? ''));
    if ($id === '') {
        return '';
    }

    $out = cms_block_head($block, true)
        . '<div class="pb-video"><iframe src="https://www.youtube-nocookie.com/embed/' . e($id) . '" '
        . 'title="' . e((string) ($block['heading'] ?? 'Video')) . '" loading="lazy" allowfullscreen '
        . 'allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe></div>';

    if ((string) ($block['caption'] ?? '') !== '') {
        $out .= '<p class="pb-video-caption">' . e((string) $block['caption']) . '</p>';
    }

    return $out;
}

function cms_block_cta(array $block): string
{
    if ((string) ($block['heading'] ?? '') === '') {
        return '';
    }

    $style = (string) ($block['style'] ?? 'soft');

    return '<div class="cta-panel reveal' . ($style === 'dark' ? ' pb-cta-dark' : '') . '">'
        . '<div><h2>' . e((string) $block['heading']) . '</h2>'
        . ((string) ($block['body'] ?? '') !== '' ? '<p>' . nl2br(e((string) $block['body'])) . '</p>' : '')
        . '</div>' . cms_block_button($block) . '</div>';
}

function cms_block_divider(array $block): string
{
    $style = (string) ($block['style'] ?? 'line');
    $size = (string) ($block['size'] ?? 'medium');

    return '<div class="pb-divider pb-divider-' . e($size) . ($style === 'line' ? ' has-line' : '') . '"><span></span></div>';
}
