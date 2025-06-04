<?php
/**
 * Template helper functions for Apex27 plugin
 */

/**
 * Generate a media button with consistent styling
 *
 * @param string $href Button URL
 * @param string $text Button text
 * @param string $type Button type (optional)
 * @param bool $target_blank Open in new tab
 * @return string HTML for media button
 */
function apex27_render_media_button($href, $text, $type = '', $target_blank = false) {
    $target = $target_blank ? ' target="_blank"' : '';
    $class = 'apex27__property-media-button';
    if ($type) {
        $class .= ' apex27__property-media-button--' . esc_attr($type);
    }
    
    return sprintf(
        '<a href="%s" class="%s"%s>%s</a>',
        esc_url($href),
        esc_attr($class),
        $target,
        esc_html($text)
    );
}

/**
 * Generate an iframe section for maps or street view
 *
 * @param string $title Section title
 * @param string $id Section ID
 * @param string $src Iframe source URL
 * @param int $height Iframe height in pixels
 * @return string HTML for iframe section
 */
function apex27_render_iframe_section($title, $id, $src, $height = 450) {
    ob_start();
    ?>
    <section class="apex27__property-iframe-section" id="<?= esc_attr($id) ?>">
        <h4 class="apex27__property-subheading" dir="auto">
            <?= esc_html($title) ?>
        </h4>
        <iframe 
            class="apex27__property-iframe" 
            style="border:0;" 
            width="100%" 
            height="<?= esc_attr($height) ?>" 
            src="<?= esc_url($src) ?>" 
            allowfullscreen>
        </iframe>
    </section>
    <?php
    return ob_get_clean();
}

/**
 * Generate error message section
 *
 * @param string $title Error title
 * @param string $message Error message
 * @param string $text_domain Translation text domain
 * @return string HTML for error section
 */
function apex27_render_error_section($title, $message, $text_domain = 'apex27') {
    ob_start();
    ?>
    <div class="apex27-container apex27-error-container">
        <section class="apex27-error-section">
            <h2 dir="auto" class="apex27-error-heading"><?= esc_html(__($title, $text_domain)) ?></h2>
            <p dir="auto" class="apex27-error-text"><?= esc_html(__($message, $text_domain)) ?></p>
        </section>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Generate property specification icons (beds, baths, etc.)
 *
 * @param object $property Property object with room counts
 * @param string $text_domain Translation text domain
 * @return string HTML for property specs
 */
function apex27_render_property_specs($property, $text_domain = 'apex27') {
    if ($property->isCommercial ?? false) {
        return '';
    }
    
    ob_start();
    ?>
    <p class="apex27__property-specs" dir="auto">
        <span class="apex27__property-specs-item" title="<?= esc_attr(__('Bedrooms', $text_domain)) ?>">
            <i class="fa fa-fw fa-bed"></i> <?= esc_html($property->bedrooms ?? 0) ?>
        </span>
        <span class="apex27__property-specs-item" title="<?= esc_attr(__('Bathrooms', $text_domain)) ?>">
            <i class="fa fa-fw fa-bath"></i> <?= esc_html($property->bathrooms ?? 0) ?>
        </span>
        <span class="apex27__property-specs-item" title="<?= esc_attr(__('Living Rooms', $text_domain)) ?>">
            <i class="fa fa-fw fa-couch"></i> <?= esc_html($property->livingRooms ?? 0) ?>
        </span>
        <?php if(!empty($property->garages)): ?>
            <span class="apex27__property-specs-item" title="<?= esc_attr(__('Garages', $text_domain)) ?>">
                <i class="fa fa-fw fa-car"></i> <?= esc_html($property->garages) ?>
            </span>
        <?php endif; ?>
    </p>
    <?php
    return ob_get_clean();
}