<?php
/**
 * Custom Post Types for Apex27 Plugin
 * 
 * Registers Property and Agent Custom Post Types with proper configuration
 * for page builder integration and REST API access.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Apex27_Custom_Post_Types {

    /**
     * Initialize Custom Post Types
     */
    public function __construct() {
        // Register post types and taxonomies
        add_action('init', array($this, 'register_post_types'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('rest_api_init', array($this, 'register_rest_fields'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        
        // Keep minimal admin columns
        add_filter('manage_apex27_property_posts_columns', array($this, 'add_property_admin_columns'));
        add_action('manage_apex27_property_posts_custom_column', array($this, 'display_property_admin_columns'), 10, 2);
        
        // Add API sync hooks
        add_action('wp_ajax_apex27_sync_properties', array($this, 'ajax_sync_properties'));
        add_action('wp_ajax_apex27_sync_agents', array($this, 'ajax_sync_agents'));
        add_action('admin_notices', array($this, 'admin_sync_notices'));
        
        // Add automatic sync functionality
        add_action('init', array($this, 'schedule_automatic_sync'));
        add_action('apex27_hourly_sync', array($this, 'run_automatic_sync'));
        add_action('admin_init', array($this, 'maybe_run_initial_sync'));
        
        // Hook into existing plugin functionality
        add_action('wp_loaded', array($this, 'integrate_with_main_plugin'));
        
        // Flush rewrite rules on activation/updates
        register_activation_hook(__FILE__, array($this, 'flush_rewrite_rules_on_activation'));
        
        // Fix admin edit access issues
        add_action('admin_init', array($this, 'fix_admin_access_issues'));
        
        // Add universal meta field replacement for any page builder
        add_filter('the_content', array($this, 'replace_meta_placeholders'));
        add_filter('widget_text', array($this, 'replace_meta_placeholders'));
        
        // Add frontend scripts to handle dynamic replacement
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
    }

    /**
     * Register Custom Post Types
     */
    public function register_post_types() {
        $this->register_property_post_type();
        $this->register_agent_post_type();
    }

    /**
     * Register Property Custom Post Type
     */
    private function register_property_post_type() {
        $labels = array(
            'name'                  => _x('Properties', 'Post type general name', 'apex27'),
            'singular_name'         => _x('Property', 'Post type singular name', 'apex27'),
            'menu_name'             => _x('Properties', 'Admin Menu text', 'apex27'),
            'name_admin_bar'        => _x('Property', 'Add New on Toolbar', 'apex27'),
            'add_new'               => __('Add New', 'apex27'),
            'add_new_item'          => __('Add New Property', 'apex27'),
            'new_item'              => __('New Property', 'apex27'),
            'edit_item'             => __('Edit Property', 'apex27'),
            'view_item'             => __('View Property', 'apex27'),
            'all_items'             => __('All Properties', 'apex27'),
            'search_items'          => __('Search Properties', 'apex27'),
            'parent_item_colon'     => __('Parent Properties:', 'apex27'),
            'not_found'             => __('No properties found.', 'apex27'),
            'not_found_in_trash'    => __('No properties found in Trash.', 'apex27'),
            'featured_image'        => _x('Property Image', 'Overrides the "Featured Image" phrase', 'apex27'),
            'set_featured_image'    => _x('Set property image', 'Overrides the "Set featured image" phrase', 'apex27'),
            'remove_featured_image' => _x('Remove property image', 'Overrides the "Remove featured image" phrase', 'apex27'),
            'use_featured_image'    => _x('Use as property image', 'Overrides the "Use as featured image" phrase', 'apex27'),
            'archives'              => _x('Property archives', 'The post type archive label', 'apex27'),
            'insert_into_item'      => _x('Insert into property', 'Overrides the "Insert into post" phrase', 'apex27'),
            'uploaded_to_this_item' => _x('Uploaded to this property', 'Overrides the "Uploaded to this post" phrase', 'apex27'),
            'filter_items_list'     => _x('Filter properties list', 'Screen reader text for the filter links', 'apex27'),
            'items_list_navigation' => _x('Properties list navigation', 'Screen reader text for the pagination', 'apex27'),
            'items_list'            => _x('Properties list', 'Screen reader text for the items list', 'apex27'),
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __('Properties imported from Apex27 CRM', 'apex27'),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-building',
            'query_var'          => true,
            'rewrite'            => array('slug' => 'properties'),
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
            'has_archive'        => true,
            'hierarchical'       => false,
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'show_in_rest'       => true, // Enable Gutenberg and REST API
            'rest_base'          => 'properties',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'taxonomies'         => array('property_type', 'property_location', 'property_status', 'property_transaction'),
        );

        register_post_type('apex27_property', $args);
    }

    /**
     * Register Agent Custom Post Type
     */
    private function register_agent_post_type() {
        $labels = array(
            'name'                  => _x('Agents', 'Post type general name', 'apex27'),
            'singular_name'         => _x('Agent', 'Post type singular name', 'apex27'),
            'menu_name'             => _x('Agents', 'Admin Menu text', 'apex27'),
            'name_admin_bar'        => _x('Agent', 'Add New on Toolbar', 'apex27'),
            'add_new'               => __('Add New', 'apex27'),
            'add_new_item'          => __('Add New Agent', 'apex27'),
            'new_item'              => __('New Agent', 'apex27'),
            'edit_item'             => __('Edit Agent', 'apex27'),
            'view_item'             => __('View Agent', 'apex27'),
            'all_items'             => __('All Agents', 'apex27'),
            'search_items'          => __('Search Agents', 'apex27'),
            'parent_item_colon'     => __('Parent Agents:', 'apex27'),
            'not_found'             => __('No agents found.', 'apex27'),
            'not_found_in_trash'    => __('No agents found in Trash.', 'apex27'),
            'featured_image'        => _x('Agent Photo', 'Overrides the "Featured Image" phrase', 'apex27'),
            'set_featured_image'    => _x('Set agent photo', 'Overrides the "Set featured image" phrase', 'apex27'),
            'remove_featured_image' => _x('Remove agent photo', 'Overrides the "Remove featured image" phrase', 'apex27'),
            'use_featured_image'    => _x('Use as agent photo', 'Overrides the "Use as featured image" phrase', 'apex27'),
            'archives'              => _x('Agent archives', 'The post type archive label', 'apex27'),
            'insert_into_item'      => _x('Insert into agent', 'Overrides the "Insert into post" phrase', 'apex27'),
            'uploaded_to_this_item' => _x('Uploaded to this agent', 'Overrides the "Uploaded to this post" phrase', 'apex27'),
            'filter_items_list'     => _x('Filter agents list', 'Screen reader text for the filter links', 'apex27'),
            'items_list_navigation' => _x('Agents list navigation', 'Screen reader text for the pagination', 'apex27'),
            'items_list'            => _x('Agents list', 'Screen reader text for the items list', 'apex27'),
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __('Real estate agents and staff', 'apex27'),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_position'      => 21,
            'menu_icon'          => 'dashicons-groups',
            'query_var'          => true,
            'rewrite'            => array('slug' => 'agents'),
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
            'has_archive'        => true,
            'hierarchical'       => false,
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'show_in_rest'       => true, // Enable Gutenberg and REST API
            'rest_base'          => 'agents',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'taxonomies'         => array('agent_department'),
        );

        register_post_type('apex27_agent', $args);
    }

    /**
     * Register Custom Taxonomies
     */
    public function register_taxonomies() {
        $this->register_property_taxonomies();
        $this->register_agent_taxonomies();
    }

    /**
     * Register REST API fields for page builder compatibility
     */
    public function register_rest_fields() {
        // Property REST fields - comprehensive API mapping
        $property_fields = $this->get_property_field_mappings();

        foreach ($property_fields as $field => $config) {
            register_rest_field('apex27_property', $field, array(
                'get_callback' => function($post) use ($field) {
                    return get_post_meta($post['id'], '_apex27_' . $field, true);
                },
                'update_callback' => function($value, $post) use ($field, $config) {
                    if ($config['type'] === 'array') {
                        $value = is_array($value) ? $value : json_decode($value, true);
                        return update_post_meta($post->ID, '_apex27_' . $field, $value);
                    } else if ($config['sanitize'] === 'email') {
                        return update_post_meta($post->ID, '_apex27_' . $field, sanitize_email($value));
                    } else if ($config['sanitize'] === 'textarea') {
                        return update_post_meta($post->ID, '_apex27_' . $field, sanitize_textarea_field($value));
                    } else {
                        return update_post_meta($post->ID, '_apex27_' . $field, sanitize_text_field($value));
                    }
                },
                'schema' => array(
                    'description' => $config['description'],
                    'type' => $config['type'],
                    'context' => array('view', 'edit')
                )
            ));
        }

        // Agent REST fields - API mapping
        $agent_fields = $this->get_agent_field_mappings();

        foreach ($agent_fields as $field => $config) {
            register_rest_field('apex27_agent', $field, array(
                'get_callback' => function($post) use ($field) {
                    return get_post_meta($post['id'], '_apex27_' . $field, true);
                },
                'update_callback' => function($value, $post) use ($field, $config) {
                    if ($config['sanitize'] === 'email') {
                        return update_post_meta($post->ID, '_apex27_' . $field, sanitize_email($value));
                    } else if ($config['sanitize'] === 'textarea') {
                        return update_post_meta($post->ID, '_apex27_' . $field, sanitize_textarea_field($value));
                    } else {
                        return update_post_meta($post->ID, '_apex27_' . $field, sanitize_text_field($value));
                    }
                },
                'schema' => array(
                    'description' => $config['description'],
                    'type' => $config['type'],
                    'context' => array('view', 'edit')
                )
            ));
        }
    }

    /**
     * Get comprehensive property field mappings from API
     */
    private function get_property_field_mappings() {
        return array(
            // Core Property Information
            'id' => array('type' => 'integer', 'description' => 'Apex27 Property ID', 'sanitize' => 'text'),
            'reference' => array('type' => 'string', 'description' => 'Property Reference Number', 'sanitize' => 'text'),
            'full_reference' => array('type' => 'string', 'description' => 'Full Property Reference', 'sanitize' => 'text'),
            'archived' => array('type' => 'boolean', 'description' => 'Property Archived Status', 'sanitize' => 'text'),
            'transaction_type' => array('type' => 'string', 'description' => 'Transaction Type (sale, rent, etc)', 'sanitize' => 'text'),
            'status' => array('type' => 'string', 'description' => 'Property Status', 'sanitize' => 'text'),
            'website_status' => array('type' => 'string', 'description' => 'Website Display Status', 'sanitize' => 'text'),

            // Address Information
            'address1' => array('type' => 'string', 'description' => 'Address Line 1', 'sanitize' => 'text'),
            'address2' => array('type' => 'string', 'description' => 'Address Line 2', 'sanitize' => 'text'),
            'address3' => array('type' => 'string', 'description' => 'Address Line 3', 'sanitize' => 'text'),
            'address4' => array('type' => 'string', 'description' => 'Address Line 4', 'sanitize' => 'text'),
            'city' => array('type' => 'string', 'description' => 'City', 'sanitize' => 'text'),
            'county' => array('type' => 'string', 'description' => 'County', 'sanitize' => 'text'),
            'postal_code' => array('type' => 'string', 'description' => 'Postal Code', 'sanitize' => 'text'),
            'country' => array('type' => 'string', 'description' => 'Country Code', 'sanitize' => 'text'),
            'display_address' => array('type' => 'string', 'description' => 'Display Address', 'sanitize' => 'text'),
            'latitude' => array('type' => 'number', 'description' => 'Latitude', 'sanitize' => 'text'),
            'longitude' => array('type' => 'number', 'description' => 'Longitude', 'sanitize' => 'text'),

            // Property Details
            'property_type' => array('type' => 'string', 'description' => 'Property Type', 'sanitize' => 'text'),
            'display_property_type' => array('type' => 'string', 'description' => 'Display Property Type', 'sanitize' => 'text'),
            'property_sub_type' => array('type' => 'string', 'description' => 'Property Sub Type', 'sanitize' => 'text'),
            'bedrooms' => array('type' => 'integer', 'description' => 'Number of Bedrooms', 'sanitize' => 'text'),
            'bathrooms' => array('type' => 'integer', 'description' => 'Number of Bathrooms', 'sanitize' => 'text'),
            'receptions' => array('type' => 'integer', 'description' => 'Number of Reception Rooms', 'sanitize' => 'text'),
            'ensuites' => array('type' => 'integer', 'description' => 'Number of Ensuites', 'sanitize' => 'text'),
            'toilets' => array('type' => 'integer', 'description' => 'Number of Toilets', 'sanitize' => 'text'),
            'kitchens' => array('type' => 'integer', 'description' => 'Number of Kitchens', 'sanitize' => 'text'),
            'dining_rooms' => array('type' => 'integer', 'description' => 'Number of Dining Rooms', 'sanitize' => 'text'),
            'floors' => array('type' => 'integer', 'description' => 'Number of Floors', 'sanitize' => 'text'),
            'entrance_floor' => array('type' => 'string', 'description' => 'Entrance Floor', 'sanitize' => 'text'),
            'floor_number' => array('type' => 'integer', 'description' => 'Floor Number', 'sanitize' => 'text'),
            'levels_occupied' => array('type' => 'integer', 'description' => 'Levels Occupied', 'sanitize' => 'text'),

            // Financial Information
            'price' => array('type' => 'number', 'description' => 'Property Price', 'sanitize' => 'text'),
            'price_currency' => array('type' => 'string', 'description' => 'Price Currency', 'sanitize' => 'text'),
            'price_prefix' => array('type' => 'string', 'description' => 'Price Prefix (e.g., From, Guide)', 'sanitize' => 'text'),
            'rent_frequency' => array('type' => 'string', 'description' => 'Rent Frequency', 'sanitize' => 'text'),
            'council_tax_amount' => array('type' => 'number', 'description' => 'Council Tax Amount', 'sanitize' => 'text'),
            'council_tax_band' => array('type' => 'string', 'description' => 'Council Tax Band', 'sanitize' => 'text'),
            'service_charge_amount' => array('type' => 'number', 'description' => 'Service Charge Amount', 'sanitize' => 'text'),
            'ground_rent_amount' => array('type' => 'number', 'description' => 'Ground Rent Amount', 'sanitize' => 'text'),
            'gross_yield' => array('type' => 'number', 'description' => 'Gross Yield Percentage', 'sanitize' => 'text'),

            // Property Descriptions
            'summary' => array('type' => 'string', 'description' => 'Property Summary', 'sanitize' => 'textarea'),
            'description' => array('type' => 'string', 'description' => 'Property Description', 'sanitize' => 'textarea'),
            'bullets' => array('type' => 'array', 'description' => 'Property Bullet Points', 'sanitize' => 'text'),

            // Property Characteristics
            'tenure' => array('type' => 'string', 'description' => 'Property Tenure', 'sanitize' => 'text'),
            'furnished' => array('type' => 'string', 'description' => 'Furnished Status', 'sanitize' => 'text'),
            'condition' => array('type' => 'string', 'description' => 'Property Condition', 'sanitize' => 'text'),
            'age_category' => array('type' => 'string', 'description' => 'Age Category', 'sanitize' => 'text'),
            'year_built' => array('type' => 'integer', 'description' => 'Year Built', 'sanitize' => 'text'),
            'internal_area' => array('type' => 'number', 'description' => 'Internal Area', 'sanitize' => 'text'),
            'internal_area_unit' => array('type' => 'string', 'description' => 'Internal Area Unit', 'sanitize' => 'text'),
            'external_area' => array('type' => 'number', 'description' => 'External Area', 'sanitize' => 'text'),
            'external_area_unit' => array('type' => 'string', 'description' => 'External Area Unit', 'sanitize' => 'text'),

            // EPC Information
            'epc_exempt' => array('type' => 'boolean', 'description' => 'EPC Exempt', 'sanitize' => 'text'),
            'epc_ee_current' => array('type' => 'integer', 'description' => 'EPC Energy Efficiency Current', 'sanitize' => 'text'),
            'epc_ee_potential' => array('type' => 'integer', 'description' => 'EPC Energy Efficiency Potential', 'sanitize' => 'text'),
            'epc_ei_current' => array('type' => 'integer', 'description' => 'EPC Environmental Impact Current', 'sanitize' => 'text'),
            'epc_ei_potential' => array('type' => 'integer', 'description' => 'EPC Environmental Impact Potential', 'sanitize' => 'text'),
            'dts_epc_expiry' => array('type' => 'string', 'description' => 'EPC Expiry Date', 'sanitize' => 'text'),
            'epc_reference' => array('type' => 'string', 'description' => 'EPC Reference', 'sanitize' => 'text'),

            // Feature Arrays (serialized)
            'accessibility_features' => array('type' => 'array', 'description' => 'Accessibility Features', 'sanitize' => 'text'),
            'heating_features' => array('type' => 'array', 'description' => 'Heating Features', 'sanitize' => 'text'),
            'parking_features' => array('type' => 'array', 'description' => 'Parking Features', 'sanitize' => 'text'),
            'outside_space_features' => array('type' => 'array', 'description' => 'Outside Space Features', 'sanitize' => 'text'),
            'electricity_supply_features' => array('type' => 'array', 'description' => 'Electricity Supply Features', 'sanitize' => 'text'),
            'water_supply_features' => array('type' => 'array', 'description' => 'Water Supply Features', 'sanitize' => 'text'),
            'sewerage_supply_features' => array('type' => 'array', 'description' => 'Sewerage Supply Features', 'sanitize' => 'text'),
            'broadband_supply_features' => array('type' => 'array', 'description' => 'Broadband Supply Features', 'sanitize' => 'text'),
            'custom_features' => array('type' => 'array', 'description' => 'Custom Features', 'sanitize' => 'text'),

            // Relationships and Media
            'branch_id' => array('type' => 'integer', 'description' => 'Branch ID', 'sanitize' => 'text'),
            'user_id' => array('type' => 'integer', 'description' => 'Assigned Agent ID', 'sanitize' => 'text'),
            'images' => array('type' => 'array', 'description' => 'Property Images', 'sanitize' => 'text'),
            'gallery' => array('type' => 'array', 'description' => 'Property Gallery', 'sanitize' => 'text'),
            'floorplans' => array('type' => 'array', 'description' => 'Property Floorplans', 'sanitize' => 'text'),
            'brochures' => array('type' => 'array', 'description' => 'Property Brochures', 'sanitize' => 'text'),
            'virtual_tours' => array('type' => 'array', 'description' => 'Virtual Tours', 'sanitize' => 'text'),

            // Additional API Fields from Debug Log
            'branch' => array('type' => 'object', 'description' => 'Branch Information', 'sanitize' => 'text'),
            'transaction_type_route' => array('type' => 'string', 'description' => 'Transaction Type Route', 'sanitize' => 'text'),
            'thumbnail_url' => array('type' => 'string', 'description' => 'Thumbnail Image URL', 'sanitize' => 'text'),
            'area_description' => array('type' => 'string', 'description' => 'Area Description', 'sanitize' => 'textarea'),
            'display_price' => array('type' => 'string', 'description' => 'Formatted Display Price', 'sanitize' => 'text'),
            'living_rooms' => array('type' => 'integer', 'description' => 'Number of Living Rooms', 'sanitize' => 'text'),
            'garages' => array('type' => 'integer', 'description' => 'Number of Garages', 'sanitize' => 'text'),
            'parking_spaces' => array('type' => 'integer', 'description' => 'Number of Parking Spaces', 'sanitize' => 'text'),
            'date_available_from' => array('type' => 'string', 'description' => 'Date Available From', 'sanitize' => 'text'),
            'header' => array('type' => 'string', 'description' => 'Property Header', 'sanitize' => 'text'),
            'banner' => array('type' => 'string', 'description' => 'Property Banner', 'sanitize' => 'text'),
            'subtitle' => array('type' => 'string', 'description' => 'Property Subtitle', 'sanitize' => 'text'),
            'print_summary' => array('type' => 'string', 'description' => 'Print Summary', 'sanitize' => 'textarea'),
            'income_description' => array('type' => 'string', 'description' => 'Income Description', 'sanitize' => 'textarea'),
            'custom_description1' => array('type' => 'string', 'description' => 'Custom Description 1', 'sanitize' => 'textarea'),
            'custom_description2' => array('type' => 'string', 'description' => 'Custom Description 2', 'sanitize' => 'textarea'),
            'custom_description3' => array('type' => 'string', 'description' => 'Custom Description 3', 'sanitize' => 'textarea'),
            'custom_description4' => array('type' => 'string', 'description' => 'Custom Description 4', 'sanitize' => 'textarea'),
            'custom_description5' => array('type' => 'string', 'description' => 'Custom Description 5', 'sanitize' => 'textarea'),
            'custom_description6' => array('type' => 'string', 'description' => 'Custom Description 6', 'sanitize' => 'textarea'),
            'main_search_region_id' => array('type' => 'integer', 'description' => 'Main Search Region ID', 'sanitize' => 'text'),
            'sale_progression' => array('type' => 'string', 'description' => 'Sale Progression Status', 'sanitize' => 'text'),
            'image_overlay_text' => array('type' => 'string', 'description' => 'Image Overlay Text', 'sanitize' => 'text'),
            'sale_fee' => array('type' => 'number', 'description' => 'Sale Fee', 'sanitize' => 'text'),
            'sale_fee_payable_by_buyer' => array('type' => 'boolean', 'description' => 'Sale Fee Payable By Buyer', 'sanitize' => 'text'),
            'total_income_text' => array('type' => 'string', 'description' => 'Total Income Text', 'sanitize' => 'text'),
            'is_featured' => array('type' => 'boolean', 'description' => 'Is Featured Property', 'sanitize' => 'text'),
            'time_created' => array('type' => 'string', 'description' => 'Time Created', 'sanitize' => 'text'),
            'time_updated' => array('type' => 'string', 'description' => 'Time Updated', 'sanitize' => 'text'),
            'time_marketed' => array('type' => 'string', 'description' => 'Time Marketed', 'sanitize' => 'text'),
            'geolocation' => array('type' => 'object', 'description' => 'Geolocation Data', 'sanitize' => 'text'),
            'pov' => array('type' => 'object', 'description' => 'Point of View Data', 'sanitize' => 'text'),
            'energy_efficiency' => array('type' => 'object', 'description' => 'Energy Efficiency Data', 'sanitize' => 'text'),
            'environmental_impact' => array('type' => 'object', 'description' => 'Environmental Impact Data', 'sanitize' => 'text'),

            // Timestamps
            'dts_created' => array('type' => 'string', 'description' => 'Date Created', 'sanitize' => 'text'),
            'dts_updated' => array('type' => 'string', 'description' => 'Date Updated', 'sanitize' => 'text'),
        );
    }

    /**
     * Get agent field mappings from API
     */
    private function get_agent_field_mappings() {
        return array(
            'id' => array('type' => 'integer', 'description' => 'Apex27 Agent ID', 'sanitize' => 'text'),
            'email' => array('type' => 'string', 'description' => 'Agent Email', 'sanitize' => 'email'),
            'title' => array('type' => 'string', 'description' => 'Agent Title', 'sanitize' => 'text'),
            'first_name' => array('type' => 'string', 'description' => 'First Name', 'sanitize' => 'text'),
            'last_name' => array('type' => 'string', 'description' => 'Last Name', 'sanitize' => 'text'),
            'is_active' => array('type' => 'boolean', 'description' => 'Agent Active Status', 'sanitize' => 'text'),
            'job_title' => array('type' => 'string', 'description' => 'Job Title', 'sanitize' => 'text'),
            'biography' => array('type' => 'string', 'description' => 'Agent Biography', 'sanitize' => 'textarea'),
            'phone' => array('type' => 'string', 'description' => 'Phone Number', 'sanitize' => 'text'),
            'mobile' => array('type' => 'string', 'description' => 'Mobile Number', 'sanitize' => 'text'),
            'work_phone' => array('type' => 'string', 'description' => 'Work Phone', 'sanitize' => 'text'),
            'branch_id' => array('type' => 'integer', 'description' => 'Branch ID', 'sanitize' => 'text'),
            'dts_created' => array('type' => 'string', 'description' => 'Date Created', 'sanitize' => 'text'),
            'dts_updated' => array('type' => 'string', 'description' => 'Date Updated', 'sanitize' => 'text'),
        );
    }

    /**
     * Register Property Taxonomies
     */
    private function register_property_taxonomies() {
        // Property Type Taxonomy
        $labels = array(
            'name'              => _x('Property Types', 'taxonomy general name', 'apex27'),
            'singular_name'     => _x('Property Type', 'taxonomy singular name', 'apex27'),
            'search_items'      => __('Search Property Types', 'apex27'),
            'all_items'         => __('All Property Types', 'apex27'),
            'parent_item'       => __('Parent Property Type', 'apex27'),
            'parent_item_colon' => __('Parent Property Type:', 'apex27'),
            'edit_item'         => __('Edit Property Type', 'apex27'),
            'update_item'       => __('Update Property Type', 'apex27'),
            'add_new_item'      => __('Add New Property Type', 'apex27'),
            'new_item_name'     => __('New Property Type Name', 'apex27'),
            'menu_name'         => __('Property Types', 'apex27'),
        );

        register_taxonomy('property_type', array('apex27_property'), array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'property-type'),
            'show_in_rest'      => true,
        ));

        // Property Location Taxonomy
        $labels = array(
            'name'              => _x('Locations', 'taxonomy general name', 'apex27'),
            'singular_name'     => _x('Location', 'taxonomy singular name', 'apex27'),
            'search_items'      => __('Search Locations', 'apex27'),
            'all_items'         => __('All Locations', 'apex27'),
            'parent_item'       => __('Parent Location', 'apex27'),
            'parent_item_colon' => __('Parent Location:', 'apex27'),
            'edit_item'         => __('Edit Location', 'apex27'),
            'update_item'       => __('Update Location', 'apex27'),
            'add_new_item'      => __('Add New Location', 'apex27'),
            'new_item_name'     => __('New Location Name', 'apex27'),
            'menu_name'         => __('Locations', 'apex27'),
        );

        register_taxonomy('property_location', array('apex27_property'), array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'location'),
            'show_in_rest'      => true,
        ));

        // Property Status Taxonomy
        $labels = array(
            'name'              => _x('Property Status', 'taxonomy general name', 'apex27'),
            'singular_name'     => _x('Status', 'taxonomy singular name', 'apex27'),
            'search_items'      => __('Search Status', 'apex27'),
            'all_items'         => __('All Status', 'apex27'),
            'edit_item'         => __('Edit Status', 'apex27'),
            'update_item'       => __('Update Status', 'apex27'),
            'add_new_item'      => __('Add New Status', 'apex27'),
            'new_item_name'     => __('New Status Name', 'apex27'),
            'menu_name'         => __('Status', 'apex27'),
        );

        register_taxonomy('property_status', array('apex27_property'), array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'status'),
            'show_in_rest'      => true,
        ));

        // Property Transaction Type Taxonomy (Sale/Rent)
        $labels = array(
            'name'              => _x('Transaction Types', 'taxonomy general name', 'apex27'),
            'singular_name'     => _x('Transaction Type', 'taxonomy singular name', 'apex27'),
            'search_items'      => __('Search Transaction Types', 'apex27'),
            'all_items'         => __('All Transaction Types', 'apex27'),
            'edit_item'         => __('Edit Transaction Type', 'apex27'),
            'update_item'       => __('Update Transaction Type', 'apex27'),
            'add_new_item'      => __('Add New Transaction Type', 'apex27'),
            'new_item_name'     => __('New Transaction Type Name', 'apex27'),
            'menu_name'         => __('Transaction Types', 'apex27'),
        );

        register_taxonomy('property_transaction', array('apex27_property'), array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'transaction-type'),
            'show_in_rest'      => true,
            'public'            => true,
        ));
    }

    /**
     * Register Agent Taxonomies
     */
    private function register_agent_taxonomies() {
        // Agent Department Taxonomy
        $labels = array(
            'name'              => _x('Departments', 'taxonomy general name', 'apex27'),
            'singular_name'     => _x('Department', 'taxonomy singular name', 'apex27'),
            'search_items'      => __('Search Departments', 'apex27'),
            'all_items'         => __('All Departments', 'apex27'),
            'edit_item'         => __('Edit Department', 'apex27'),
            'update_item'       => __('Update Department', 'apex27'),
            'add_new_item'      => __('Add New Department', 'apex27'),
            'new_item_name'     => __('New Department Name', 'apex27'),
            'menu_name'         => __('Departments', 'apex27'),
        );

        register_taxonomy('agent_department', array('apex27_agent'), array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'department'),
            'show_in_rest'      => true,
        ));
    }

    /**
     * Add meta boxes for custom fields
     */
    public function add_meta_boxes() {
        // Property meta boxes
        add_meta_box(
            'apex27-property-details',
            __('Property Details', 'apex27'),
            array($this, 'property_details_meta_box'),
            'apex27_property',
            'normal',
            'high'
        );

        add_meta_box(
            'apex27-property-specifications',
            __('Property Specifications', 'apex27'),
            array($this, 'property_specifications_meta_box'),
            'apex27_property',
            'normal',
            'high'
        );

        add_meta_box(
            'apex27-property-financial',
            __('Financial Information', 'apex27'),
            array($this, 'property_financial_meta_box'),
            'apex27_property',
            'side',
            'default'
        );

        add_meta_box(
            'apex27-property-raw-data',
            __('Raw API Data (All Fields)', 'apex27'),
            array($this, 'property_raw_data_meta_box'),
            'apex27_property',
            'normal',
            'low'
        );

        // Agent meta boxes
        add_meta_box(
            'apex27-agent-details',
            __('Agent Details', 'apex27'),
            array($this, 'agent_details_meta_box'),
            'apex27_agent',
            'normal',
            'high'
        );

        add_meta_box(
            'apex27-agent-contact',
            __('Contact Information', 'apex27'),
            array($this, 'agent_contact_meta_box'),
            'apex27_agent',
            'side',
            'default'
        );
    }

    /**
     * Property Details Meta Box
     */
    public function property_details_meta_box($post) {
        wp_nonce_field('apex27_property_details', 'apex27_property_details_nonce');
        
        $property_id = get_post_meta($post->ID, '_apex27_property_id', true);
        $reference = get_post_meta($post->ID, '_apex27_reference', true);
        $display_price = get_post_meta($post->ID, '_apex27_display_price', true);
        $price_prefix = get_post_meta($post->ID, '_apex27_price_prefix', true);
        $transaction_type = get_post_meta($post->ID, '_apex27_transaction_type', true);
        $is_featured = get_post_meta($post->ID, '_apex27_is_featured', true);
        $is_commercial = get_post_meta($post->ID, '_apex27_is_commercial', true);

        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="apex27_property_id"><?php _e('Apex27 Property ID', 'apex27'); ?></label></th>
                <td><input type="text" id="apex27_property_id" name="apex27_property_id" value="<?php echo esc_attr($property_id); ?>" class="regular-text" readonly /></td>
            </tr>
            <tr>
                <th scope="row"><label for="apex27_reference"><?php _e('Reference Number', 'apex27'); ?></label></th>
                <td><input type="text" id="apex27_reference" name="apex27_reference" value="<?php echo esc_attr($reference); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="apex27_display_price"><?php _e('Display Price', 'apex27'); ?></label></th>
                <td><input type="text" id="apex27_display_price" name="apex27_display_price" value="<?php echo esc_attr($display_price); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="apex27_price_prefix"><?php _e('Price Prefix', 'apex27'); ?></label></th>
                <td><input type="text" id="apex27_price_prefix" name="apex27_price_prefix" value="<?php echo esc_attr($price_prefix); ?>" class="regular-text" placeholder="<?php _e('e.g., From, Guide Price, POA', 'apex27'); ?>" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="apex27_transaction_type"><?php _e('Transaction Type', 'apex27'); ?></label></th>
                <td>
                    <select id="apex27_transaction_type" name="apex27_transaction_type">
                        <option value=""><?php _e('Select Transaction Type', 'apex27'); ?></option>
                        <option value="sales" <?php selected($transaction_type, 'sales'); ?>><?php _e('Sales', 'apex27'); ?></option>
                        <option value="lettings" <?php selected($transaction_type, 'lettings'); ?>><?php _e('Lettings', 'apex27'); ?></option>
                        <option value="new-homes" <?php selected($transaction_type, 'new-homes'); ?>><?php _e('New Homes', 'apex27'); ?></option>
                        <option value="land" <?php selected($transaction_type, 'land'); ?>><?php _e('Land', 'apex27'); ?></option>
                        <option value="commercial-sales" <?php selected($transaction_type, 'commercial-sales'); ?>><?php _e('Commercial Sales', 'apex27'); ?></option>
                        <option value="commercial-lettings" <?php selected($transaction_type, 'commercial-lettings'); ?>><?php _e('Commercial Lettings', 'apex27'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Property Flags', 'apex27'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="apex27_is_featured" value="1" <?php checked($is_featured, '1'); ?> />
                        <?php _e('Featured Property', 'apex27'); ?>
                    </label><br>
                    <label>
                        <input type="checkbox" name="apex27_is_commercial" value="1" <?php checked($is_commercial, '1'); ?> />
                        <?php _e('Commercial Property', 'apex27'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Property Specifications Meta Box
     */
    public function property_specifications_meta_box($post) {
        $bedrooms = get_post_meta($post->ID, '_apex27_bedrooms', true);
        $bathrooms = get_post_meta($post->ID, '_apex27_bathrooms', true);
        $living_rooms = get_post_meta($post->ID, '_apex27_living_rooms', true);
        $garages = get_post_meta($post->ID, '_apex27_garages', true);

        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="apex27_bedrooms"><?php _e('Bedrooms', 'apex27'); ?></label></th>
                <td><input type="number" id="apex27_bedrooms" name="apex27_bedrooms" value="<?php echo esc_attr($bedrooms); ?>" min="0" max="20" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="apex27_bathrooms"><?php _e('Bathrooms', 'apex27'); ?></label></th>
                <td><input type="number" id="apex27_bathrooms" name="apex27_bathrooms" value="<?php echo esc_attr($bathrooms); ?>" min="0" max="20" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="apex27_living_rooms"><?php _e('Living Rooms', 'apex27'); ?></label></th>
                <td><input type="number" id="apex27_living_rooms" name="apex27_living_rooms" value="<?php echo esc_attr($living_rooms); ?>" min="0" max="20" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="apex27_garages"><?php _e('Garages', 'apex27'); ?></label></th>
                <td><input type="number" id="apex27_garages" name="apex27_garages" value="<?php echo esc_attr($garages); ?>" min="0" max="10" /></td>
            </tr>
        </table>
        <?php
    }

    /**
     * Property Financial Meta Box
     */
    public function property_financial_meta_box($post) {
        $gross_yield = get_post_meta($post->ID, '_apex27_gross_yield', true);
        $sale_fee = get_post_meta($post->ID, '_apex27_sale_fee', true);
        $fee_payable_by_buyer = get_post_meta($post->ID, '_apex27_fee_payable_by_buyer', true);

        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="apex27_gross_yield"><?php _e('Gross Yield (%)', 'apex27'); ?></label></th>
                <td><input type="text" id="apex27_gross_yield" name="apex27_gross_yield" value="<?php echo esc_attr($gross_yield); ?>" placeholder="<?php _e('e.g., 8.5%', 'apex27'); ?>" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="apex27_sale_fee"><?php _e('Sale Fee', 'apex27'); ?></label></th>
                <td><input type="text" id="apex27_sale_fee" name="apex27_sale_fee" value="<?php echo esc_attr($sale_fee); ?>" placeholder="<?php _e('e.g., £1,000', 'apex27'); ?>" /></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Fee Options', 'apex27'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="apex27_fee_payable_by_buyer" value="1" <?php checked($fee_payable_by_buyer, '1'); ?> />
                        <?php _e('Fee Payable by Buyer', 'apex27'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Agent Details Meta Box
     */
    public function agent_details_meta_box($post) {
        wp_nonce_field('apex27_agent_details', 'apex27_agent_details_nonce');
        
        $agent_id = get_post_meta($post->ID, '_apex27_agent_id', true);
        $job_title = get_post_meta($post->ID, '_apex27_job_title', true);
        $biography = get_post_meta($post->ID, '_apex27_biography', true);

        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="apex27_agent_id"><?php _e('Apex27 Agent ID', 'apex27'); ?></label></th>
                <td><input type="text" id="apex27_agent_id" name="apex27_agent_id" value="<?php echo esc_attr($agent_id); ?>" class="regular-text" readonly /></td>
            </tr>
            <tr>
                <th scope="row"><label for="apex27_job_title"><?php _e('Job Title', 'apex27'); ?></label></th>
                <td><input type="text" id="apex27_job_title" name="apex27_job_title" value="<?php echo esc_attr($job_title); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="apex27_biography"><?php _e('Biography', 'apex27'); ?></label></th>
                <td><textarea id="apex27_biography" name="apex27_biography" rows="5" class="large-text"><?php echo esc_textarea($biography); ?></textarea></td>
            </tr>
        </table>
        <?php
    }

    /**
     * Agent Contact Meta Box
     */
    public function agent_contact_meta_box($post) {
        $email = get_post_meta($post->ID, '_apex27_email', true);
        $phone = get_post_meta($post->ID, '_apex27_phone', true);
        $mobile = get_post_meta($post->ID, '_apex27_mobile', true);

        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="apex27_email"><?php _e('Email Address', 'apex27'); ?></label></th>
                <td><input type="email" id="apex27_email" name="apex27_email" value="<?php echo esc_attr($email); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="apex27_phone"><?php _e('Phone Number', 'apex27'); ?></label></th>
                <td><input type="tel" id="apex27_phone" name="apex27_phone" value="<?php echo esc_attr($phone); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="apex27_mobile"><?php _e('Mobile Number', 'apex27'); ?></label></th>
                <td><input type="tel" id="apex27_mobile" name="apex27_mobile" value="<?php echo esc_attr($mobile); ?>" class="regular-text" /></td>
            </tr>
        </table>
        <?php
    }

    /**
     * Raw API data meta box - shows all populated fields
     */
    public function property_raw_data_meta_box($post) {
        echo '<div style="background: #f9f9f9; padding: 15px; border-radius: 5px;">';
        echo '<h4>Complete API Data Mapping (for Bricks/ACF usage)</h4>';
        echo '<p><em>All fields from the Apex27 API are captured and accessible in Bricks builder</em></p>';
        
        // Get all meta fields that start with _apex27_
        $all_meta = get_post_meta($post->ID);
        $apex27_fields = array();
        $raw_fields = array();
        $full_response = null;
        
        foreach ($all_meta as $key => $value) {
            $meta_value = is_array($value) ? $value[0] : $value;
            
            if (strpos($key, '_apex27_raw_') === 0) {
                $field_name = str_replace('_apex27_raw_', '', $key);
                $raw_fields[$field_name] = $meta_value;
            } elseif (strpos($key, '_apex27_full_api_response') === 0) {
                $full_response = maybe_unserialize($meta_value);
            } elseif (strpos($key, '_apex27_') === 0) {
                $field_name = str_replace('_apex27_', '', $key);
                $apex27_fields[$field_name] = $meta_value;
            }
        }
        
        if (empty($apex27_fields) && empty($raw_fields)) {
            echo '<p><strong>No API data found.</strong> Run a property sync to populate fields.</p>';
        } else {
            // Create tabbed interface
            echo '<div style="margin-bottom: 15px;">';
            echo '<button type="button" onclick="showTab(\'mapped\', this)" class="apex27-tab-button active" style="padding: 8px 16px; margin-right: 5px; background: #0073aa; color: white; border: none; cursor: pointer;">Mapped Fields (' . count($apex27_fields) . ')</button>';
            echo '<button type="button" onclick="showTab(\'raw\', this)" class="apex27-tab-button" style="padding: 8px 16px; margin-right: 5px; background: #666; color: white; border: none; cursor: pointer;">Additional Fields (' . count($raw_fields) . ')</button>';
            if ($full_response) {
                echo '<button type="button" onclick="showTab(\'full\', this)" class="apex27-tab-button" style="padding: 8px 16px; background: #666; color: white; border: none; cursor: pointer;">Full API Response</button>';
            }
            echo '</div>';
            
            // Mapped fields tab
            echo '<div id="mapped-tab" class="apex27-tab-content">';
            echo '<h4>Structured Fields (Ready for Bricks)</h4>';
            $this->render_fields_table($apex27_fields, '_apex27_');
            echo '</div>';
            
            // Raw fields tab
            echo '<div id="raw-tab" class="apex27-tab-content" style="display: none;">';
            echo '<h4>Additional API Fields</h4>';
            echo '<p><em>These are extra fields from the API that weren\'t in the predefined mapping</em></p>';
            $this->render_fields_table($raw_fields, '_apex27_raw_');
            echo '</div>';
            
            // Full response tab
            if ($full_response) {
                echo '<div id="full-tab" class="apex27-tab-content" style="display: none;">';
                echo '<h4>Complete API Response</h4>';
                echo '<div style="max-height: 500px; overflow: auto; background: white; padding: 15px; border: 1px solid #ddd; font-family: monospace; font-size: 12px;">';
                echo '<pre>' . esc_html(json_encode($full_response, JSON_PRETTY_PRINT)) . '</pre>';
                echo '</div>';
                echo '</div>';
            }
            
            $total_fields = count($apex27_fields) + count($raw_fields);
            echo '<p style="margin-top: 15px;"><strong>Total: ' . $total_fields . ' API fields captured</strong> - All accessible in Bricks builder</p>';
        }
        
        echo '</div>';
        
        // Add JavaScript for tabs
        echo '<script>
        function showTab(tabName, button) {
            // Hide all tabs
            var tabs = document.querySelectorAll(".apex27-tab-content");
            tabs.forEach(function(tab) { tab.style.display = "none"; });
            
            // Remove active class from all buttons
            var buttons = document.querySelectorAll(".apex27-tab-button");
            buttons.forEach(function(btn) { 
                btn.style.background = "#666"; 
                btn.classList.remove("active");
            });
            
            // Show selected tab and activate button
            document.getElementById(tabName + "-tab").style.display = "block";
            button.style.background = "#0073aa";
            button.classList.add("active");
        }
        </script>';
        
        // Add a section showing all available Bricks meta keys
        echo '<div style="margin-top: 20px; padding: 15px; background: #e8f4f8; border-left: 4px solid #0073aa;">';
        echo '<h4>🧱 Quick Reference: All Available Bricks Meta Keys</h4>';
        echo '<p><em>Copy and paste these exact keys into your Bricks templates:</em></p>';
        echo '<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 10px;">';
        
        $all_field_mappings = $this->get_property_field_mappings();
        foreach ($all_field_mappings as $field_name => $config) {
            echo '<code style="background: white; padding: 4px 8px; border: 1px solid #ddd; font-size: 11px; display: block;">_apex27_' . esc_html($field_name) . '</code>';
        }
        
        echo '</div>';
        echo '<div style="margin-top: 10px; padding: 10px; background: #fff; border: 2px solid #0073aa; border-radius: 4px;">';
        echo '<h5 style="margin: 0 0 5px 0; color: #0073aa;">📝 How to Use in Bricks Builder:</h5>';
        echo '<ol style="margin: 5px 0; padding-left: 20px; font-size: 12px;">';
        echo '<li>Copy any meta key above (e.g., <code>_apex27_display_price</code>)</li>';
        echo '<li>In Bricks, add a Text element</li>';
        echo '<li>Type: <code>{_apex27_display_price}</code> (with curly braces)</li>';
        echo '<li>The field will automatically populate on the frontend</li>';
        echo '</ol>';
        echo '<p style="margin: 5px 0 0 0; font-size: 11px; color: #666;"><strong>✅ Works with:</strong> Text elements, headings, buttons, any text field in Bricks</p>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Helper function to render fields table
     */
    private function render_fields_table($fields, $prefix) {
        if (empty($fields)) {
            echo '<p>No fields found.</p>';
            return;
        }
        
        echo '<div style="max-height: 400px; overflow-y: auto; background: white; padding: 10px; border: 1px solid #ddd;">';
        echo '<table style="width: 100%; border-collapse: collapse;">';
        echo '<tr style="background: #f0f0f0;">';
        echo '<th style="padding: 8px; text-align: left; border: 1px solid #ddd; width: 25%;">Field Name</th>';
        echo '<th style="padding: 8px; text-align: left; border: 1px solid #ddd; width: 30%;">Bricks Meta Key</th>';
        echo '<th style="padding: 8px; text-align: left; border: 1px solid #ddd; width: 15%;">Type</th>';
        echo '<th style="padding: 8px; text-align: left; border: 1px solid #ddd; width: 30%;">Value Preview</th>';
        echo '</tr>';
        
        foreach ($fields as $field => $value) {
            $display_value = $value;
            $field_type = 'string';
            
            // Handle serialized data
            if (is_serialized($value)) {
                $unserialized = maybe_unserialize($value);
                if (is_array($unserialized)) {
                    $field_type = 'array (' . count($unserialized) . ' items)';
                    $display_value = '<details><summary>Array data</summary><pre style="font-size: 10px; max-height: 150px; overflow: auto;">' . esc_html(print_r($unserialized, true)) . '</pre></details>';
                } elseif (is_object($unserialized)) {
                    $field_type = 'object';
                    $display_value = '<details><summary>Object data</summary><pre style="font-size: 10px; max-height: 150px; overflow: auto;">' . esc_html(print_r($unserialized, true)) . '</pre></details>';
                } else {
                    $display_value = esc_html($unserialized);
                }
            } else {
                if (is_numeric($value)) {
                    $field_type = 'number';
                } elseif (is_bool($value)) {
                    $field_type = 'boolean';
                    $display_value = $value ? 'true' : 'false';
                } else {
                    $display_value = esc_html(substr($value, 0, 100) . (strlen($value) > 100 ? '...' : ''));
                }
            }
            
            echo '<tr>';
            echo '<td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">' . esc_html($field) . '</td>';
            echo '<td style="padding: 8px; border: 1px solid #ddd; font-family: monospace; color: #0073aa; background: #f8f8f8;">' . esc_html($prefix . $field) . '</td>';
            echo '<td style="padding: 8px; border: 1px solid #ddd; font-size: 11px; color: #666;">' . $field_type . '</td>';
            echo '<td style="padding: 8px; border: 1px solid #ddd; max-width: 200px; overflow: hidden;">' . $display_value . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        echo '</div>';
    }

    /**
     * Save meta box data
     */
    public function save_meta_boxes($post_id) {
        // Check if user has permission to edit the post
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save property meta data
        if (get_post_type($post_id) === 'apex27_property') {
            $this->save_property_meta($post_id);
        }

        // Save agent meta data
        if (get_post_type($post_id) === 'apex27_agent') {
            $this->save_agent_meta($post_id);
        }
    }

    /**
     * Save property meta data
     */
    private function save_property_meta($post_id) {
        // Verify nonce
        if (!isset($_POST['apex27_property_details_nonce']) || 
            !wp_verify_nonce($_POST['apex27_property_details_nonce'], 'apex27_property_details')) {
            return;
        }

        // Property details
        $fields = array(
            'apex27_property_id', 'apex27_reference', 'apex27_display_price', 
            'apex27_price_prefix', 'apex27_transaction_type', 'apex27_bedrooms',
            'apex27_bathrooms', 'apex27_living_rooms', 'apex27_garages',
            'apex27_gross_yield', 'apex27_sale_fee'
        );

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
            }
        }

        // Handle checkboxes
        $checkbox_fields = array('apex27_is_featured', 'apex27_is_commercial', 'apex27_fee_payable_by_buyer');
        foreach ($checkbox_fields as $field) {
            update_post_meta($post_id, '_' . $field, isset($_POST[$field]) ? '1' : '0');
        }
    }

    /**
     * Save agent meta data
     */
    private function save_agent_meta($post_id) {
        // Verify nonce
        if (!isset($_POST['apex27_agent_details_nonce']) || 
            !wp_verify_nonce($_POST['apex27_agent_details_nonce'], 'apex27_agent_details')) {
            return;
        }

        // Agent details
        $fields = array(
            'apex27_agent_id', 'apex27_job_title', 'apex27_email', 
            'apex27_phone', 'apex27_mobile'
        );

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                if ($field === 'apex27_email') {
                    update_post_meta($post_id, '_' . $field, sanitize_email($_POST[$field]));
                } else {
                    update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
                }
            }
        }

        // Handle textarea
        if (isset($_POST['apex27_biography'])) {
            update_post_meta($post_id, '_apex27_biography', sanitize_textarea_field($_POST['apex27_biography']));
        }
    }

    /**
     * AJAX handler for syncing properties from API
     */
    public function ajax_sync_properties() {
        check_ajax_referer('apex27_sync_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Call API directly with parameters to get ALL properties
        global $apex27;
        
        if (!$apex27 || !$apex27->is_configured()) {
            wp_send_json(array('error' => 'Apex27 plugin not configured'));
            return;
        }
        
        $created = 0;
        $updated = 0;
        
        // Call API for sale properties
        $sale_response = $apex27->api_call("get-listings", [
            "search" => 1,
            "transaction_type" => "sale",
            "include_sstc" => 1,
            "pageSize" => 1000
        ]);
        
        // Call API for rent properties  
        $rent_response = $apex27->api_call("get-listings", [
            "search" => 1,
            "transaction_type" => "rent", 
            "include_sstc" => 1,
            "pageSize" => 1000
        ]);
        
        $all_properties = array();
        
        // Process sale properties
        if ($sale_response) {
            $sale_data = json_decode($sale_response);
            if ($sale_data && isset($sale_data->listings)) {
                error_log('Apex27 Debug - Found ' . count($sale_data->listings) . ' sale properties');
                $all_properties = array_merge($all_properties, $sale_data->listings);
            } else {
                error_log('Apex27 Debug - No sale properties found. Response: ' . substr($sale_response, 0, 200));
            }
        }
        
        // Process rent properties
        if ($rent_response) {
            $rent_data = json_decode($rent_response);
            if ($rent_data && isset($rent_data->listings)) {
                error_log('Apex27 Debug - Found ' . count($rent_data->listings) . ' rent properties');
                $all_properties = array_merge($all_properties, $rent_data->listings);
            } else {
                error_log('Apex27 Debug - No rent properties found. Response: ' . substr($rent_response, 0, 200));
            }
        }
        
        // Process all properties through the populate function
        foreach ($all_properties as $property_data) {
            $result = $this->create_or_update_property((array) $property_data);
            if ($result['success']) {
                if ($result['action'] === 'created') {
                    $created++;
                } else {
                    $updated++;
                }
            }
        }
        
        $total_processed = count($all_properties);
        $result = array(
            'success' => true,
            'created' => $created,
            'updated' => $updated,
            'total_processed' => $total_processed,
            'message' => "Manual sync completed. Found $total_processed properties (sale + rent). Created: $created, Updated: $updated"
        );
        
        wp_send_json($result);
    }

    /**
     * AJAX handler for syncing agents from API
     */
    public function ajax_sync_agents() {
        check_ajax_referer('apex27_sync_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $result = $this->sync_agents_from_api();
        wp_send_json($result);
    }

    /**
     * Sync properties from Apex27 API
     */
    /**
     * Sync properties using the main plugin's working method
     */
    public function sync_properties_using_main_plugin_method() {
        global $apex27;
        
        if (!$apex27 || !$apex27->is_configured()) {
            return array('error' => 'Apex27 plugin not configured');
        }

        $created = 0;
        $updated = 0;
        $errors = array();

        // Use the main plugin's working API call method with search parameters
        $api_response = $apex27->api_call('get-listings', array(
            'search' => 1,
            'includeImages' => 1,
            'includeFloorplans' => 1,
            'includeBrochures' => 1,
            'includeGallery' => 1,
            'includeVirtualTours' => 1,
            'pageSize' => 1000,
            'archived' => 1
        ));

        if (!$api_response) {
            return array('error' => 'Failed to fetch properties from API');
        }

        error_log('Apex27 Debug - Raw API response: ' . substr($api_response, 0, 500));
        
        $data = json_decode($api_response);
        error_log('Apex27 Debug - Decoded API data structure: ' . print_r($data, true));
        
        if (!$data) {
            return array('error' => 'Failed to decode JSON response: ' . substr($api_response, 0, 200));
        }
        
        // Check different possible response structures
        $properties = null;
        if (isset($data->listings)) {
            $properties = $data->listings;
            error_log('Apex27 Debug - Found properties in data->listings: ' . count($properties));
        } elseif (is_array($data)) {
            $properties = $data;
            error_log('Apex27 Debug - Found properties as array: ' . count($properties));
        } elseif (isset($data->data)) {
            $properties = $data->data;
            error_log('Apex27 Debug - Found properties in data->data: ' . count($properties));
        } else {
            error_log('Apex27 Debug - Available data keys: ' . implode(', ', array_keys((array)$data)));
            return array('error' => 'No properties found in API response. Available keys: ' . implode(', ', array_keys((array)$data)));
        }

        if (!$properties || count($properties) === 0) {
            return array('error' => 'No properties returned from API');
        }

        foreach ($properties as $property_data) {
            $result = $this->create_or_update_property((array) $property_data);
            if ($result['success']) {
                if ($result['action'] === 'created') {
                    $created++;
                } else {
                    $updated++;
                }
            } else {
                $errors[] = $result['error'];
                error_log('Apex27 Debug - Property creation failed: ' . $result['error']);
            }
        }

        return array(
            'success' => true,
            'created' => $created,
            'updated' => $updated,
            'total_processed' => count($properties),
            'errors' => $errors,
            'message' => "Processed " . count($properties) . " properties. Created: $created, Updated: $updated"
        );
    }

    public function sync_properties_from_api($params = array()) {
        global $apex27;
        
        if (!$apex27 || !$apex27->is_configured()) {
            return array('error' => 'Apex27 plugin not configured');
        }

        // Get properties from API using existing method (Apex27 uses "get-listings" endpoint)
        // Remove ALL filtering to get absolutely every property regardless of status
        $api_params = array('pageSize' => 1000);
        error_log('Apex27 Debug - API call parameters: ' . print_r($api_params, true));
        error_log('Apex27 Debug - API URL configured: ' . get_option('apex27_website_url'));
        error_log('Apex27 Debug - API Key configured: ' . (get_option('apex27_api_key') ? 'Yes' : 'No'));
        
        $api_response = $apex27->api_call('get-listings', $api_params);

        if (!$api_response) {
            return array('error' => 'Failed to fetch properties from API');
        }

        $properties = json_decode($api_response, true);
        if (!$properties || !is_array($properties)) {
            return array('error' => 'Invalid API response: ' . substr($api_response, 0, 200));
        }

        $created = 0;
        $updated = 0;
        $errors = array();

        // Check the actual response structure
        if (is_array($properties) && isset($properties['listings'])) {
            $actual_listings = $properties['listings'];
            error_log('Apex27 Debug - Found ' . count($actual_listings) . ' listings in response');
            if (empty($actual_listings)) {
                error_log('Apex27 Debug - Empty listings array. Full response: ' . print_r($properties, true));
                return array(
                    'success' => true,
                    'created' => 0,
                    'updated' => 0,
                    'total_processed' => 0,
                    'errors' => array('No properties found - listings array is empty'),
                    'message' => "No properties found in API response. The API returned an empty listings array."
                );
            }
        } else {
            $actual_listings = $properties;
        }

        foreach ($actual_listings as $property_data) {
            $result = $this->create_or_update_property($property_data);
            if ($result['success']) {
                if ($result['action'] === 'created') {
                    $created++;
                } else {
                    $updated++;
                }
            } else {
                $errors[] = $result['error'];
                error_log('Apex27 Debug - Property creation failed: ' . $result['error']);
            }
        }

        return array(
            'success' => true,
            'created' => $created,
            'updated' => $updated,
            'total_processed' => count($properties),
            'errors' => $errors,
            'message' => "Processed " . count($properties) . " properties. Created: $created, Updated: $updated"
        );
    }

    /**
     * Sync agents from Apex27 API
     */
    public function sync_agents_from_api($params = array()) {
        global $apex27;
        
        if (!$apex27 || !$apex27->is_configured()) {
            return array('error' => 'Apex27 plugin not configured');
        }

        // Get agents from API (Apex27 uses "get-users" endpoint)
        $api_response = $apex27->api_call('get-users', $params);

        if (!$api_response) {
            return array('error' => 'Failed to fetch agents from API');
        }

        $agents = json_decode($api_response, true);
        if (!$agents || !is_array($agents)) {
            return array('error' => 'Invalid API response');
        }

        $created = 0;
        $updated = 0;
        $errors = array();

        foreach ($agents as $agent_data) {
            $result = $this->create_or_update_agent($agent_data);
            if ($result['success']) {
                if ($result['action'] === 'created') {
                    $created++;
                } else {
                    $updated++;
                }
            } else {
                $errors[] = $result['error'];
            }
        }

        return array(
            'success' => true,
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors
        );
    }

    /**
     * Create or update property from API data
     */
    private function create_or_update_property($property_data) {
        if (!is_array($property_data)) {
            error_log('Apex27 Debug - Invalid property data (not array): ' . print_r($property_data, true));
            return array('success' => false, 'error' => 'Property data invalid - not an array');
        }

        // Try multiple possible ID field names
        $apex27_id = null;
        $possible_id_fields = ['id', 'listingId', 'ID', 'propertyId', 'apex27Id'];
        
        foreach ($possible_id_fields as $field) {
            if (isset($property_data[$field]) && !empty($property_data[$field])) {
                $apex27_id = $property_data[$field];
                error_log('Apex27 Debug - Found ID in field "' . $field . '": ' . $apex27_id);
                break;
            }
        }
        
        if (!$apex27_id) {
            error_log('Apex27 Debug - No ID found in property data. Available fields: ' . implode(', ', array_keys($property_data)));
            error_log('Apex27 Debug - Full property data: ' . print_r($property_data, true));
            return array('success' => false, 'error' => 'Property ID missing - tried fields: ' . implode(', ', $possible_id_fields));
        }
        error_log('Apex27 Debug - Processing property ID: ' . $apex27_id);
        
        // Check if property already exists
        $existing_posts = get_posts(array(
            'post_type' => 'apex27_property',
            'meta_query' => array(
                array(
                    'key' => '_apex27_id',
                    'value' => $apex27_id,
                    'compare' => '='
                )
            ),
            'posts_per_page' => 1
        ));

        $is_update = !empty($existing_posts);
        $post_id = $is_update ? $existing_posts[0]->ID : 0;

        // Ensure post type is registered
        if (!post_type_exists('apex27_property')) {
            error_log('Apex27 Debug - Post type apex27_property not registered!');
            return array('success' => false, 'error' => 'Post type apex27_property not registered');
        }

        // Prepare post data
        $post_data = array(
            'post_type' => 'apex27_property',
            'post_status' => 'publish',
            'post_title' => $property_data['displayAddress'] ?? 'Property ' . $apex27_id,
            'post_content' => $property_data['description'] ?? '',
            'post_excerpt' => $property_data['summary'] ?? '',
            'post_author' => 1, // Ensure we have a valid author
        );

        error_log('Apex27 Debug - Post data: ' . print_r($post_data, true));

        if ($is_update) {
            $post_data['ID'] = $post_id;
            $result = wp_update_post($post_data, true);
            error_log('Apex27 Debug - Update result: ' . print_r($result, true));
        } else {
            $result = wp_insert_post($post_data, true);
            error_log('Apex27 Debug - Insert result: ' . print_r($result, true));
            if (is_wp_error($result)) {
                error_log('Apex27 Debug - Insert Error: ' . $result->get_error_message());
                return array('success' => false, 'error' => 'Failed to create post: ' . $result->get_error_message());
            }
            $post_id = $result;
        }

        if (is_wp_error($result)) {
            error_log('Apex27 Debug - WP Error: ' . $result->get_error_message());
            return array('success' => false, 'error' => 'Failed to save post: ' . $result->get_error_message());
        }
        
        if (!$post_id || $post_id === 0) {
            error_log('Apex27 Debug - No valid post ID returned: ' . print_r($result, true));
            return array('success' => false, 'error' => 'Failed to save post: No valid post ID returned');
        }

        // CRITICAL: Verify the post was actually created with the correct post type
        global $wpdb;
        $actual_post_type = $wpdb->get_var($wpdb->prepare(
            "SELECT post_type FROM {$wpdb->posts} WHERE ID = %d",
            $post_id
        ));
        
        if ($actual_post_type !== 'apex27_property') {
            error_log('Apex27 Debug - CRITICAL: Post created with wrong type! Expected: apex27_property, Got: ' . $actual_post_type);
            // Try to fix it
            $wpdb->update(
                $wpdb->posts,
                array('post_type' => 'apex27_property'),
                array('ID' => $post_id),
                array('%s'),
                array('%d')
            );
            error_log('Apex27 Debug - Attempted to fix post type for ID: ' . $post_id);
        }
        
        // Store the Apex27 ID as meta immediately
        update_post_meta($post_id, '_apex27_id', $apex27_id);
        
        // Verify the post was created properly
        $verify_post = get_post($post_id);
        if (!$verify_post) {
            error_log('Apex27 Debug - Post verification failed for ID: ' . $post_id);
            return array('success' => false, 'error' => 'Post created but cannot be retrieved (ID: ' . $post_id . ')');
        }
        
        error_log('Apex27 Debug - Successfully created/updated post ID: ' . $post_id . ' with Apex27 ID: ' . $apex27_id);
        error_log('Apex27 Debug - Post details: ' . print_r($verify_post, true));
        error_log('Apex27 Debug - Post edit URL: ' . admin_url('post.php?post=' . $post_id . '&action=edit'));
        error_log('Apex27 Debug - Post permalink: ' . get_permalink($post_id));
        
        // Double-check the post exists in the correct post type
        global $wpdb;
        $db_check = $wpdb->get_row($wpdb->prepare(
            "SELECT ID, post_title, post_type, post_status FROM {$wpdb->posts} WHERE ID = %d",
            $post_id
        ));
        error_log('Apex27 Debug - Database check: ' . print_r($db_check, true));

        // Force WordPress to recognize the post immediately
        wp_cache_delete($post_id, 'posts');
        wp_cache_delete($post_id, 'post_meta');
        clean_post_cache($post_id);
        
        // Final verification that post can be retrieved from admin context
        $admin_post_check = get_post($post_id);
        if (!$admin_post_check || $admin_post_check->post_type !== 'apex27_property') {
            error_log('Apex27 Debug - Admin post check failed! Post: ' . print_r($admin_post_check, true));
        } else {
            error_log('Apex27 Debug - Admin post check successful for ID: ' . $post_id);
        }

        // Map and save ALL API fields as meta data (comprehensive approach)
        $field_mappings = $this->get_property_field_mappings();
        $mapped_fields = array();
        
        // First, map known fields with proper handling
        foreach ($field_mappings as $field_name => $config) {
            $api_key = $this->map_api_key_to_field($field_name);
            if (isset($property_data[$api_key])) {
                $value = $property_data[$api_key];
                
                // Handle complex data types
                if ($config['type'] === 'array' && is_array($value)) {
                    $value = maybe_serialize($value);
                } elseif ($config['type'] === 'object' && is_object($value)) {
                    $value = maybe_serialize((array) $value);
                } elseif ($config['type'] === 'object' && is_array($value)) {
                    $value = maybe_serialize($value);
                }
                
                update_post_meta($post_id, '_apex27_' . $field_name, $value);
                $mapped_fields[] = $api_key;
                error_log('Apex27 Debug - Saved known field ' . $field_name . ' (' . $api_key . ') for property ' . $post_id);
            }
        }
        
        // Then, save ALL remaining API fields to ensure nothing is missed
        foreach ($property_data as $api_field => $value) {
            if (!in_array($api_field, $mapped_fields) && !empty($value)) {
                // Skip certain system fields that aren't useful
                $skip_fields = array('api_key', 'search', 'pageSize', 'locale');
                if (in_array($api_field, $skip_fields)) {
                    continue;
                }
                
                // Handle complex data types
                if (is_array($value) || is_object($value)) {
                    $value = maybe_serialize($value);
                }
                
                // Store with original API field name for complete data capture
                update_post_meta($post_id, '_apex27_raw_' . $api_field, $value);
                error_log('Apex27 Debug - Saved additional field ' . $api_field . ' for property ' . $post_id);
            }
        }
        
        // Store the complete API response for debugging
        update_post_meta($post_id, '_apex27_full_api_response', maybe_serialize($property_data));

        // Set property type taxonomy if available
        if (isset($property_data['propertyType'])) {
            wp_set_object_terms($post_id, $property_data['propertyType'], 'property_type');
        }

        // Set location taxonomy if available
        if (isset($property_data['city'])) {
            wp_set_object_terms($post_id, $property_data['city'], 'property_location');
        }

        // Set status taxonomy if available
        if (isset($property_data['status'])) {
            wp_set_object_terms($post_id, $property_data['status'], 'property_status');
        }

        // Set transaction type taxonomy based on transactionTypeRoute
        if (isset($property_data['transactionTypeRoute'])) {
            $transaction_type_route = $property_data['transactionTypeRoute'];
            $term_name = '';
            
            error_log('Apex27 Debug - Property ' . $post_id . ' has transactionTypeRoute: ' . $transaction_type_route);
            
            // Map API transactionTypeRoute to readable terms
            if (strpos($transaction_type_route, 'sale') !== false || strpos($transaction_type_route, 'buy') !== false) {
                $term_name = 'For Sale';
            } elseif (strpos($transaction_type_route, 'rent') !== false || strpos($transaction_type_route, 'let') !== false) {
                $term_name = 'To Let';
            } elseif (strpos($transaction_type_route, 'land') !== false) {
                $term_name = 'Land For Sale';
            } elseif (strpos($transaction_type_route, 'commercial') !== false) {
                if (strpos($transaction_type_route, 'rent') !== false) {
                    $term_name = 'Commercial Rent';
                } else {
                    $term_name = 'Commercial Sale';
                }
            } else {
                // Use the route value as fallback
                $term_name = ucwords(str_replace('-', ' ', $transaction_type_route));
            }
            
            if ($term_name) {
                // Ensure the term exists before assigning it
                $term = term_exists($term_name, 'property_transaction');
                if (!$term) {
                    $term = wp_insert_term($term_name, 'property_transaction');
                    error_log('Apex27 Debug - Created transaction term: ' . $term_name);
                }
                
                $result = wp_set_object_terms($post_id, $term_name, 'property_transaction');
                error_log('Apex27 Debug - Set transaction type "' . $term_name . '" for property ' . $post_id . ' (route: ' . $transaction_type_route . ')');
            }
        } else {
            error_log('Apex27 Debug - Property ' . $post_id . ' has NO transactionTypeRoute field. Available fields: ' . implode(', ', array_keys($property_data)));
        }

        return array(
            'success' => true,
            'action' => $is_update ? 'updated' : 'created',
            'post_id' => $post_id
        );
    }

    /**
     * Create or update agent from API data
     */
    private function create_or_update_agent($agent_data) {
        if (!isset($agent_data['id'])) {
            return array('success' => false, 'error' => 'Agent ID missing');
        }

        $apex27_id = $agent_data['id'];
        
        // Check if agent already exists
        $existing_posts = get_posts(array(
            'post_type' => 'apex27_agent',
            'meta_query' => array(
                array(
                    'key' => '_apex27_id',
                    'value' => $apex27_id,
                    'compare' => '='
                )
            ),
            'posts_per_page' => 1
        ));

        $is_update = !empty($existing_posts);
        $post_id = $is_update ? $existing_posts[0]->ID : 0;

        // Create agent name
        $agent_name = trim(($agent_data['firstName'] ?? '') . ' ' . ($agent_data['lastName'] ?? ''));
        if (empty($agent_name)) {
            $agent_name = 'Agent ' . $apex27_id;
        }

        // Prepare post data
        $post_data = array(
            'post_type' => 'apex27_agent',
            'post_status' => 'publish',
            'post_title' => $agent_name,
            'post_content' => $agent_data['biography'] ?? '',
        );

        if ($is_update) {
            $post_data['ID'] = $post_id;
            $result = wp_update_post($post_data);
        } else {
            $result = wp_insert_post($post_data);
            $post_id = $result;
        }

        if (is_wp_error($result) || !$post_id) {
            return array('success' => false, 'error' => 'Failed to save post');
        }

        // Map and save all API fields as meta data
        $field_mappings = $this->get_agent_field_mappings();
        foreach ($field_mappings as $field_name => $config) {
            $api_key = $this->map_api_key_to_field($field_name);
            if (isset($agent_data[$api_key])) {
                update_post_meta($post_id, '_apex27_' . $field_name, $agent_data[$api_key]);
            }
        }

        return array(
            'success' => true,
            'action' => $is_update ? 'updated' : 'created',
            'post_id' => $post_id
        );
    }

    /**
     * Map field names to API keys (convert snake_case to camelCase)
     */
    private function map_api_key_to_field($field_name) {
        // Convert snake_case to camelCase for API
        $parts = explode('_', $field_name);
        $camel_case = $parts[0];
        for ($i = 1; $i < count($parts); $i++) {
            $camel_case .= ucfirst($parts[$i]);
        }
        return $camel_case;
    }


    /**
     * Get field mapping helper for page builders
     */
    public function get_field_mapping_for_builders() {
        return array(
            'properties' => $this->get_property_field_mappings(),
            'agents' => $this->get_agent_field_mappings()
        );
    }

    /**
     * Schedule automatic sync via WordPress cron
     */
    public function schedule_automatic_sync() {
        if (!wp_next_scheduled('apex27_hourly_sync')) {
            wp_schedule_event(time(), 'hourly', 'apex27_hourly_sync');
        }
    }

    /**
     * Run automatic sync of properties and agents
     */
    public function run_automatic_sync() {
        global $apex27;
        
        if (!$apex27 || !$apex27->is_configured()) {
            return;
        }

        // Only sync if enabled in options
        if (!get_option('apex27_auto_sync_enabled', true)) {
            return;
        }

        // Sync properties first
        $this->sync_properties_from_api();
        
        // Then sync agents
        $this->sync_agents_from_api();
        
        // Update last sync time
        update_option('apex27_last_sync_time', current_time('mysql'));
    }

    /**
     * Run initial sync when plugin is first configured
     */
    public function maybe_run_initial_sync() {
        global $apex27;
        
        if (!$apex27 || !$apex27->is_configured()) {
            return;
        }

        // Check if initial sync has been run
        if (get_option('apex27_initial_sync_completed')) {
            return;
        }

        // Run initial sync
        $this->run_automatic_sync();
        
        // Mark initial sync as completed
        update_option('apex27_initial_sync_completed', true);
        
        // Set transient to show success message
        set_transient('apex27_initial_sync_success', true, 300);
    }

    /**
     * Integrate with main plugin functionality to populate custom post types from existing API calls
     */
    public function integrate_with_main_plugin() {
        global $apex27;
        
        if (!$apex27 || !$apex27->is_configured()) {
            return;
        }

        // Hook into the existing search results to populate properties
        add_filter('apex27_search_results', array($this, 'populate_properties_from_search'));
        add_filter('apex27_property_details', array($this, 'populate_property_from_details'));
    }

    /**
     * Populate custom post types when search results are retrieved
     */
    public function populate_properties_from_search($search_results) {
        if (!$search_results || !is_object($search_results) || !isset($search_results->listings)) {
            return $search_results;
        }

        // Process each property in search results
        foreach ($search_results->listings as $property_data) {
            $this->create_or_update_property((array) $property_data);
        }

        return $search_results;
    }

    /**
     * Populate custom post type when property details are retrieved
     */
    public function populate_property_from_details($property_details) {
        if (!$property_details || !is_object($property_details)) {
            return $property_details;
        }

        // Convert object to array and create/update property
        $this->create_or_update_property((array) $property_details);

        // Also populate agent if available
        if (isset($property_details->user) && $property_details->user) {
            $this->create_or_update_agent((array) $property_details->user);
        }

        return $property_details;
    }

    /**
     * Enhanced admin notices including sync status
     */
    public function admin_sync_notices() {
        // Show initial sync success message
        if (get_transient('apex27_initial_sync_success')) {
            echo '<div class="notice notice-success is-dismissible">
                <p><strong>' . __('Apex27 Custom Post Types:', 'apex27') . '</strong> ' . 
                __('Initial sync completed! Properties and agents have been imported from your CRM.', 'apex27') . '</p>
            </div>';
            delete_transient('apex27_initial_sync_success');
        }

        // Show last sync time
        $last_sync = get_option('apex27_last_sync_time');
        if ($last_sync && current_user_can('manage_options') && isset($_GET['page']) && $_GET['page'] === 'apex27') {
            $sync_time = human_time_diff(strtotime($last_sync), current_time('timestamp')) . ' ' . __('ago', 'apex27');
            echo '<div class="notice notice-info">
                <p><strong>' . __('Last CRM Sync:', 'apex27') . '</strong> ' . $sync_time . '</p>
            </div>';
        }

        // Original sync result notices
        if (isset($_GET['apex27_sync_result'])) {
            $result = sanitize_text_field($_GET['apex27_sync_result']);
            if ($result === 'success') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Manual sync completed successfully!', 'apex27') . '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('Sync failed. Please try again.', 'apex27') . '</p></div>';
            }
        }
    }

    /**
     * Add admin columns for properties
     */
    public function add_property_admin_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['apex27_price'] = 'Price';
                $new_columns['apex27_type'] = 'Type';
                $new_columns['apex27_transaction'] = 'Transaction';
                $new_columns['apex27_fields'] = 'API Fields';
                $new_columns['apex27_id'] = 'Apex27 ID';
            }
        }
        return $new_columns;
    }

    /**
     * Display admin columns for properties
     */
    public function display_property_admin_columns($column, $post_id) {
        switch ($column) {
            case 'apex27_price':
                $price = get_post_meta($post_id, '_apex27_display_price', true);
                if (!$price) {
                    $price = get_post_meta($post_id, '_apex27_price', true);
                }
                echo $price ? esc_html($price) : '—';
                break;
                
            case 'apex27_type':
                $type = get_post_meta($post_id, '_apex27_property_type', true);
                if (!$type) {
                    $type = get_post_meta($post_id, '_apex27_display_property_type', true);
                }
                echo $type ? esc_html($type) : '—';
                break;
                
            case 'apex27_transaction':
                $terms = get_the_terms($post_id, 'property_transaction');
                if ($terms && !is_wp_error($terms)) {
                    echo esc_html($terms[0]->name);
                } else {
                    echo '—';
                }
                break;
                
            case 'apex27_fields':
                // Count all meta fields that start with _apex27_
                $all_meta = get_post_meta($post_id);
                $apex27_count = 0;
                $raw_count = 0;
                
                foreach ($all_meta as $key => $value) {
                    if (strpos($key, '_apex27_raw_') === 0) {
                        $raw_count++;
                    } elseif (strpos($key, '_apex27_') === 0 && strpos($key, '_apex27_full_api_response') === false) {
                        $apex27_count++;
                    }
                }
                
                echo '<span style="color: #0073aa; font-weight: bold;">' . $apex27_count . '</span>';
                if ($raw_count > 0) {
                    echo ' <span style="color: #666;">+' . $raw_count . '</span>';
                }
                break;
                
            case 'apex27_id':
                $apex27_id = get_post_meta($post_id, '_apex27_id', true);
                echo $apex27_id ? '<code>' . esc_html($apex27_id) . '</code>' : '—';
                break;
        }
    }

    /**
     * Flush rewrite rules when taxonomy is updated
     */
    public function flush_rewrite_rules_on_activation() {
        $this->register_post_types();
        $this->register_taxonomies();
        flush_rewrite_rules();
    }

    /**
     * Fix admin access issues for custom post types
     */
    public function fix_admin_access_issues() {
        // Only run this once WordPress is fully loaded
        if (!did_action('init')) {
            return;
        }
        
        // Check if we need to flush rewrite rules for post type access
        if (!get_option('apex27_post_type_rules_flushed')) {
            flush_rewrite_rules();
            update_option('apex27_post_type_rules_flushed', true);
            error_log('Apex27 Debug - Flushed rewrite rules for post type access');
        }

        // Ensure capabilities are properly set for post types
        $role = get_role('administrator');
        if ($role) {
            $role->add_cap('edit_apex27_property');
            $role->add_cap('edit_apex27_properties');
            $role->add_cap('edit_others_apex27_properties');
            $role->add_cap('publish_apex27_properties');
            $role->add_cap('read_private_apex27_properties');
            $role->add_cap('delete_apex27_property');
            $role->add_cap('delete_apex27_properties');
            $role->add_cap('delete_others_apex27_properties');
            
            $role->add_cap('edit_apex27_agent');
            $role->add_cap('edit_apex27_agents');
            $role->add_cap('edit_others_apex27_agents');
            $role->add_cap('publish_apex27_agents');
            $role->add_cap('read_private_apex27_agents');
            $role->add_cap('delete_apex27_agent');
            $role->add_cap('delete_apex27_agents');
            $role->add_cap('delete_others_apex27_agents');
        }
    }


    /**
     * Universal meta field replacement for any page builder
     */
    public function replace_meta_placeholders($content) {
        global $post;
        
        if (!$post || $post->post_type !== 'apex27_property') {
            return $content;
        }
        
        // Handle all {_apex27_*} patterns in content
        $pattern = '/\{(_apex27_[^}]+)\}/';
        
        return preg_replace_callback($pattern, function($matches) use ($post) {
            $meta_key = $matches[1];
            $meta_value = get_post_meta($post->ID, $meta_key, true);
            
            // Handle serialized data
            if (is_serialized($meta_value)) {
                $unserialized = maybe_unserialize($meta_value);
                if (is_array($unserialized)) {
                    return implode(', ', array_filter($unserialized));
                } elseif (is_object($unserialized)) {
                    if (isset($unserialized->name)) return $unserialized->name;
                    if (isset($unserialized->title)) return $unserialized->title;
                    if (isset($unserialized->value)) return $unserialized->value;
                    return '';
                }
            }
            
            return $meta_value ?: '';
        }, $content);
    }

    /**
     * Enqueue frontend scripts for dynamic meta replacement
     */
    public function enqueue_frontend_scripts() {
        global $post;
        
        if (!$post || $post->post_type !== 'apex27_property') {
            return;
        }
        
        // Add inline script for client-side replacement if needed
        $script = "
        document.addEventListener('DOMContentLoaded', function() {
            // Find all elements with {_apex27_*} patterns and replace them
            var elements = document.querySelectorAll('*');
            var apex27Data = " . json_encode($this->get_post_apex27_data($post->ID)) . ";
            
            elements.forEach(function(element) {
                if (element.children.length === 0) { // Only text nodes
                    var text = element.textContent;
                    if (text.includes('{_apex27_')) {
                        Object.keys(apex27Data).forEach(function(key) {
                            var searchPattern = '{' + key + '}';
                            while (text.indexOf(searchPattern) !== -1) {
                                text = text.replace(searchPattern, apex27Data[key] || '');
                            }
                        });
                        element.textContent = text;
                    }
                }
            });
        });
        ";
        
        wp_add_inline_script('jquery', $script);
    }

    /**
     * Get all Apex27 meta data for a post
     */
    private function get_post_apex27_data($post_id) {
        $all_meta = get_post_meta($post_id);
        $apex27_data = array();
        
        foreach ($all_meta as $key => $value) {
            if (strpos($key, '_apex27_') === 0) {
                $meta_value = is_array($value) ? $value[0] : $value;
                
                // Handle serialized data
                if (is_serialized($meta_value)) {
                    $unserialized = maybe_unserialize($meta_value);
                    if (is_array($unserialized)) {
                        $apex27_data[$key] = implode(', ', array_filter($unserialized));
                    } elseif (is_object($unserialized)) {
                        if (isset($unserialized->name)) $apex27_data[$key] = $unserialized->name;
                        elseif (isset($unserialized->title)) $apex27_data[$key] = $unserialized->title;
                        elseif (isset($unserialized->value)) $apex27_data[$key] = $unserialized->value;
                        else $apex27_data[$key] = '';
                    } else {
                        $apex27_data[$key] = $unserialized;
                    }
                } else {
                    $apex27_data[$key] = $meta_value;
                }
            }
        }
        
        return $apex27_data;
    }

}

// Initialize Custom Post Types
new Apex27_Custom_Post_Types();