<?php
/**
 * Plugin Name: Apex27 Integration
 * Description: Apex27 integration. Adds property search and property details page with contact form. To get started, activate the plugin and go to the Apex27 page under Settings to enter your API key.
 * Version: 0.27
 * Author: The Northern Web
 * Author URI: https://thenorthern-web.co.uk/
 * Text Domain: apex27
 * Domain Path: /languages
 */

class Apex27 {

	const BUILD = 51;

	const KEY_APEX27_WEBSITE_URL = "apex27_website_url";
	const KEY_APEX27_API_KEY = "apex27_api_key";
	const KEY_APEX27_EXPAND_PRICE_RANGE = "apex27_expand_price_range";
	const KEY_APEX27_SHOW_OVERSEAS_DROPDOWN = "apex27_show_overseas_dropdown";
	const KEY_APEX27_ENABLE_RECAPTCHA = "apex27_enable_recaptcha";

	private $property_search_slug = "property-search";

	/** @var string Plugin directory */
	private $plugin_dir;

	/** @var string Plugin URL with trailing slash */
	private $plugin_url;

	private $portal_options;

	private $listing_details;

	public function __construct() {

		$this->plugin_dir = __DIR__;
		$this->plugin_url = plugin_dir_url(__FILE__);

		// Load template helpers
		require_once $this->plugin_dir . '/includes/template-helpers.php';
		
		// Load custom post types
		require_once $this->plugin_dir . '/includes/custom-post-types.php';

		// Admin
		if(is_admin()) {
			add_action("admin_menu", array($this, "create_plugin_settings_page"));
		}

		add_action("init", array($this, "load_text_domain"));

		add_action("admin_init", array($this, "setup_sections"));
		add_action("admin_init", array($this, "setup_fields"));

		if($this->is_configured()) {
			add_filter("query_vars", array($this, "add_query_vars"));
			add_filter("template_include", array($this, "get_template_path_by_query"));
			add_filter("init", array($this, "init_rewrite_rules"));
			add_filter("document_title_parts", array($this, "format_title_parts"));

			add_action("wp_enqueue_scripts", array($this, "add_scripts"));

			add_action("wp_head", array($this, "on_head_tag"));

			add_action("wp_ajax_property_details_contact", array($this, "handle_contact_form"));
			add_action("wp_ajax_nopriv_property_details_contact", array($this, "handle_contact_form"));

			add_action("wp_ajax_branch_contact", array($this, "handle_contact_form"));
			add_action("wp_ajax_nopriv_branch_contact", array($this, "handle_contact_form"));
		}

	}

	public function add_scripts() {
		wp_enqueue_script("apex27", sprintf( "%sassets/js/apex27.js?build=%d", $this->plugin_url, self::BUILD ), array("jquery"));
		wp_enqueue_script("fslightbox", sprintf( "%sassets/js/fslightbox.js?build=%d", $this->plugin_url, self::BUILD ) );

		// Localize script with plugin data
		wp_localize_script("apex27", "apex27_data", array(
			"plugin_url" => $this->plugin_url,
			"ajax_url" => admin_url("admin-ajax.php")
		));

		wp_enqueue_style("apex27-style", sprintf("%sassets/css/style.css?build=%d", $this->plugin_url, self::BUILD ) );
		wp_enqueue_style("font-awesome-style", "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css");

		$options = $this->get_portal_options();
		if($this->has_google_api_key()) {
			wp_enqueue_script("apex27-google-maps", "https://maps.googleapis.com/maps/api/js?key=" . $options->googleApiKey);
			wp_enqueue_script("apex27-google-maps-marker-clusterer", "https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js");
		}

		$uri = $_SERVER["REQUEST_URI"];

		$apex27_uri_prefixes = [
			"/{$this->property_search_slug}",
			"/property-details",
		];

		$is_apex27_page = false;
		foreach($apex27_uri_prefixes as $prefix) {
			if(preg_match("/^" . preg_quote($prefix, "/") . "/", $uri)) {
				$is_apex27_page = true;
			}
		}

		if($is_apex27_page && isset($options->recaptchaSiteKey) && $this->is_recaptcha_enabled()) {
			wp_enqueue_script("apex27-google-recaptcha", "https://www.google.com/recaptcha/api.js?render=" . $options->recaptchaSiteKey);
		}
	}

	public function load_text_domain() {
		load_plugin_textdomain("apex27", false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function on_head_tag() {
		$options = $this->get_portal_options();
		if($options) {

			?>
			<style>
				:root {
					--brand-color: <?=htmlspecialchars($options->brandColourHex)?>;
				}
			</style>
			<?php
		}
	}

	public function format_title_parts($parts) {

		$text_domain = "apex27";

		$uri = $_SERVER["REQUEST_URI"];

		array_pop($parts);

		if(preg_match("/^\/$this->property_search_slug/", $uri)) {
			array_unshift($parts, __("Property Search", $text_domain));
		}

		if(preg_match("/^\/property-details/", $uri)) {
			array_unshift($parts, __("Property Details", $text_domain));
		}

		if($this->listing_details) {
			array_unshift($parts, $this->listing_details->displayAddress);
		}

		return $parts;
	}

	public function init_rewrite_rules() {
		add_rewrite_rule("^$this->property_search_slug/?$", 'index.php?apex27_page_name=property-search', 'top');
		add_rewrite_rule('^property-details/(sales|lettings|new-homes|land|commercial-sales|commercial-lettings)/[^/]+/([0-9]+)/?$', 'index.php?apex27_page_name=property-details&listing_id=$matches[2]', 'top');

		// Only flush rewrite rules if they haven't been set yet
		if (!get_option('apex27_rewrite_rules_flushed')) {
			flush_rewrite_rules();
			update_option('apex27_rewrite_rules_flushed', true);
		}
	}

	private function get_plugin_vars() {
		return ["apex27_page_name"];
	}

	private function get_search_vars() {
		return ["type", "property_type", "overseas", "min_price", "max_price", "city", "min_beds", "max_beds", "baths", "min_gross_yield", "include_sstc", "sort", "page"];
	}

	private function get_detail_vars() {
		return ["listing_id"];
	}

	private function get_contact_vars() {
		return ["listing_id", "branch_id", "first_name", "last_name", "email", "phone", "message", "request_listing_details", "request_viewing", "request_valuation", "token"];
	}

	public function add_query_vars($vars) {

		return array_merge(
			$vars,
			$this->get_plugin_vars(),
			$this->get_search_vars(),
			$this->get_detail_vars(),
			$this->get_contact_vars()
		);

	}

	public function get_template_path_by_query($template) {
		$page = get_query_var("apex27_page_name");

		$path = $this->get_template_path($page);

		if($path) {
			return $path;
		}

		return $template;
	}

	public function get_template_path($name) {
		$override_template = get_template_directory() . '/apex27/' . $name . ".php";
		if(file_exists($override_template)) {
			return $override_template;
		}

		$plugin_template = $this->plugin_dir . '/templates/' . $name . ".php";
		if(file_exists($plugin_template)) {
			return $plugin_template;
		}

		return null;
	}

	public function include_template($path, $parameters = []) {
		if($path && file_exists($path)) {
			extract($parameters, EXTR_OVERWRITE);
			require $path;
		}
	}

	public function create_plugin_settings_page() {
		add_options_page("Apex27", "Apex27", "manage_options", "apex27", array($this, "settings_page_content"));
	}

	/**
	 * Check if plugin is properly configured with API credentials
	 * 
	 * @return bool True if configured, false otherwise
	 */
	public function is_configured() {
		$website_url = get_option(self::KEY_APEX27_WEBSITE_URL);
		$api_key = get_option(self::KEY_APEX27_API_KEY);

		return $website_url && $api_key;
	}

	public function settings_page_content() {
		$logo_url = $this->get_plugin_url() . "assets/img/logo.png";
		$text_domain = "apex27";

		?>
		<div class="wrap">

			<h1>
				<img src="<?=htmlspecialchars($logo_url)?>" alt="Apex27" height="32" />
			</h1>

			<?php
			if(!$this->is_configured()) {
				?>
				<h2><?=htmlspecialchars(__("Welcome", $text_domain))?></h2>
				<p><?=htmlspecialchars(__("Thanks for installing the Apex27 WordPress plugin!", $text_domain))?></p>

				<p><?=htmlspecialchars(__("Please configure the following options to activate the integration.", $text_domain))?></p>

				<p><?=htmlspecialchars(__("You can find these settings under the WordPress tab in your website's Edit page on Apex27.", $text_domain))?></p>
				<?php
			}
			?>


			<form method="post" action="options.php" novalidate="novalidate">

				<?php

				settings_fields("apex27_form");
				do_settings_sections("apex27_form");
				submit_button();

				?>

			</form>

			<?php
			if($this->is_configured()) {
				?>
				<?php
				$ping_response = $this->ping();
				if($ping_response && $ping_response->success) {
					$search_url = get_site_url(null, $this->property_search_slug);
					?>

					<h2><?=htmlspecialchars(__("Status", $text_domain))?>: <span style="color: green"><?=htmlspecialchars(__("Connected", $text_domain))?></span></h2>

					<h2><?=htmlspecialchars(__("URLs", $text_domain))?></h2>

					<p>
						<?=htmlspecialchars(__("Property Search", $text_domain))?>: <a href="<?=htmlspecialchars($search_url)?>"><?=htmlspecialchars($search_url)?></a>
					</p>

					<ul>
						<li>
							<strong><?=htmlspecialchars(__("Please Note", $text_domain))?>:</strong>

							<?=htmlspecialchars(
								sprintf(
									__("This plugin will replace any page with a URL slug that matches %s with the property search page.", $text_domain),
									$this->property_search_slug
								)
							)?>
						</li>
					</ul>

					<?php
				}
				else {
					?>
					<h2><?=htmlspecialchars(__("Status", $text_domain))?>: <span style="color: red"><?=htmlspecialchars(__("Not Connected", $text_domain))?></span></h2>
					<p style="font-size: 1rem;">
						<?php
						if($ping_response) {
							?>
							<?=htmlspecialchars($ping_response->message)?>
							<?php
						}
						?>
					</p>
					<?php
				}
			}
			?>

		</div>
		<?php
		// Show CRM sync status for custom post types
		$this->show_crm_sync_status();
	}

	public function setup_sections() {
		add_settings_section("main_section", __("Main", "apex27"), array($this, "section_callback"), "apex27_form");
	}

	public function section_callback($arguments) {
		switch($arguments["id"]) {
			case "main_section":
				break;
		}
	}

	public function setup_fields() {

		$text_domain = "apex27";

		$fields = [
			[
				"id" => "apex27_website_url",
				"label" => __("Website URL", $text_domain),
				"section" => "main_section",
				"type" => "text",
				"options" => false,
				"placeholder" => "https://....apex27.co.uk",
				"helper" => "",
				"supplemental" => "",
				"default" => ""
			],
			[
				"id" => "apex27_api_key",
				"label" => __("API Key", $text_domain),
				"section" => "main_section",
				"type" => "text",
				"options" => false,
				"placeholder" => "Enter API key",
				"helper" => "",
				"supplemental" => "",
				"default" => ""
			],
			[
				"id" => "apex27_expand_price_range",
				"label" => __("Expand Price Range to £50M", $text_domain),
				"section" => "main_section",
				"type" => "checkbox",
				"options" => false,
				"helper" => "",
				"supplemental" => "",
				"default" => ""
			],
			[
				"id" => self::KEY_APEX27_SHOW_OVERSEAS_DROPDOWN,
				"label" => __("Show Overseas Dropdown", $text_domain),
				"section" => "main_section",
				"type" => "checkbox",
				"options" => false,
				"helper" => "",
				"supplemental" => "",
				"default" => true
			],
			[
				"id" => self::KEY_APEX27_ENABLE_RECAPTCHA,
				"label" => __("Enable Google ReCAPTCHA v3", $text_domain),
				"section" => "main_section",
				"type" => "checkbox",
				"options" => false,
				"helper" => "",
				"supplemental" => "",
				"default" => true
			]
		];

		foreach($fields as $field) {
			add_settings_field($field["id"], $field["label"], array($this, "field_callback"), "apex27_form", $field["section"], $field);
			register_setting("apex27_form", $field["id"]);
		}

	}

	public function field_callback($arguments) {

		$id = $arguments["id"];
		$type = $arguments["type"];
		$default = $arguments["default"];
		$placeholder = isset($arguments["placeholder"]) ? $arguments["placeholder"] : null;
		$supplemental = $arguments["supplemental"];

		$value = get_option($id);
		if($value === false) {
			$value = $default;
		}

		switch($type) {
			case "text":
				echo "<input class=\"regular-text code\" type=\"text\" name=\"$id\" id=\"$id\" value=\"$value\" placeholder=\"$placeholder\" />";
				break;

			case "checkbox":

				$checked = "";
				if($value) {
					$checked = "checked";
				}

				echo "<input type=\"checkbox\" name=\"$id\" id=\"$id\" value=\"1\" $checked />";
				break;

		}

		if($supplemental) {
			echo sprintf('<p class="description">%s</p>', $supplemental);
		}

	}

	/**
	 * @return string Plugin path
	 */
	public function get_plugin_dir() {
		return $this->plugin_dir;
	}

	/**
	 * @return string Plugin URL with trailing slash
	 */
	public function get_plugin_url() {
		return $this->plugin_url;
	}

	public function get_property_slug($property) {
		$address = $property->displayAddress ?: "no address";
		$address = mb_strtolower($address);

		$address = preg_replace("/\W/u", "-", $address);
		return preg_replace("/[-]+/", "-", $address);
	}

	/**
	 * Make API call to Apex27 service with proper error handling
	 * 
	 * @param string $endpoint API endpoint to call
	 * @param array $data Data to send with the request
	 * @return string|false Response body or false on error
	 */
	public function api_call($endpoint, $data = array()) {
		$api_url_format = "%s/api/%s";

		$website_url = get_option(self::KEY_APEX27_WEBSITE_URL);
		if(!$website_url) {
			error_log('Apex27: Website URL not configured');
			return false;
		}

		$api_key = get_option(self::KEY_APEX27_API_KEY);
		if(!$api_key) {
			error_log('Apex27: API key not configured');
			return false;
		}

		$data["api_key"] = $api_key;
		$query = http_build_query($data);
		$api_endpoint_url = sprintf($api_url_format, $website_url, $endpoint);

		$handle = curl_init($api_endpoint_url);
		if (!$handle) {
			error_log('Apex27: Failed to initialize cURL');
			return false;
		}

		curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_POST, true);
		curl_setopt($handle, CURLOPT_POSTFIELDS, $query);
		curl_setopt($handle, CURLOPT_TIMEOUT, 30);
		curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);

		$response = curl_exec($handle);
		$http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
		$error = curl_error($handle);
		curl_close($handle);

		if($error) {
			error_log('Apex27: cURL error - ' . $error);
			return false;
		}

		if ($http_code !== 200) {
			error_log('Apex27: HTTP error - ' . $http_code);
			return false;
		}

		return $response;
	}

	/**
	 * Test API connection
	 * 
	 * @return object|null Ping response or null on failure
	 */
	public function ping() {
		$response = $this->api_call("ping");
		return json_decode($response);
	}

	/**
	 * Get portal configuration options from API
	 * 
	 * @return object|false Portal options or false on failure
	 */
	public function get_portal_options() {

		if($this->portal_options) {
			return $this->portal_options;
		}

		$response = $this->api_call("get-portal-options");
		$options = json_decode($response);
		if(!$options) {
			return false;
		}

		$this->portal_options = $options;
		return $options;
	}

	/**
	 * Get search configuration options from API
	 * 
	 * @return object|null Search options or null on failure
	 */
	public function get_search_options() {
		$response = $this->api_call("get-search-options");
		return json_decode($response);
	}

	/**
	 * Get property search results based on current query parameters
	 * 
	 * @return object|null Search results or null on failure
	 */
	public function get_search_results() {
		$portal_options = $this->get_portal_options();
		$search_options = $this->get_search_options();

		$transaction_type = get_query_var("type") ?: $search_options->defaultTransactionType;
		$transaction_type = $transaction_type ?: "sale";

		$response = $this->api_call("get-listings", [
			"search" => 1,
			"property_type" => get_query_var("property_type"),
			"transaction_type" => $transaction_type,
			"overseas" => get_query_var("overseas"),
			"min_price" => get_query_var("min_price"),
			"max_price" => get_query_var("max_price"),
			"city" => get_query_var("city"),
			"min_beds" => get_query_var("min_beds"),
			"max_beds" => get_query_var("max_beds"),
			"min_gross_yield" => get_query_var("min_gross_yield"),
			"include_sstc" => get_query_var("include_sstc", (int) $portal_options->defaultIncludeSstc),
			"sort" => get_query_var("sort"),
			"page" => get_query_var("page"),
			"locale" => get_locale()
		]);
		$result = json_decode($response);
		
		// Hook for custom post types to populate from search results
		$result = apply_filters('apex27_search_results', $result);
		
		return $result;
	}

	/**
	 * Get detailed property information by listing ID
	 * 
	 * @return object|null Property details or null on failure
	 */
	public function get_property_details() {
		$listing_id = get_query_var("listing_id");

		$response = $this->api_call("get-listing", [
			"id" => $listing_id,
			"locale" => get_locale()
		]);
		$result = json_decode($response);
		
		// Hook for custom post types to populate from property details
		$result = apply_filters('apex27_property_details', $result);
		
		return $result;
	}

	/**
	 * Handle contact form submission with proper sanitization
	 */
	public function handle_contact_form() {
		// Verify nonce for security
		if (!wp_verify_nonce($_POST['apex27_nonce'] ?? '', 'apex27_contact_form')) {
			wp_send_json_error((object) [
				"message" => "Security verification failed. Please refresh the page and try again."
			]);
			return;
		}

		$fields = $this->get_contact_vars();
		$post_data = [];

		if($this->is_recaptcha_enabled()) {
			$post_data["ip"] = sanitize_text_field($_SERVER["REMOTE_ADDR"] ?? '');
			$post_data["recaptcha_site_key"] = $this->get_recaptcha_site_key();
		}

		// Sanitize all POST data
		foreach($fields as $field) {
			if (isset($_POST[$field])) {
				if ($field === 'email') {
					$post_data[$field] = sanitize_email($_POST[$field]);
				} elseif ($field === 'message') {
					$post_data[$field] = sanitize_textarea_field($_POST[$field]);
				} else {
					$post_data[$field] = sanitize_text_field($_POST[$field]);
				}
			} else {
				$post_data[$field] = "";
			}
		}

		$response = $this->api_call("contact", $post_data);
		$data = json_decode($response);
		if(!$data) {
			wp_send_json_error((object) [
				"message" => "Unable to send your enquiry at this time. Please try again later."
			]);
			return;
		}

		if($data->success) {
			wp_send_json_success($data);
		}
		else {
			wp_send_json_error($data);
		}

	}

	public function set_listing_details($details) {
		$this->listing_details = $details;
		add_action("wp_head", [$this, "add_open_graph_tags"]);
	}

	public function get_logo_url() {
		return $this->get_plugin_url() . "assets/img/logo.png";
	}

	public function get_footer_path() {
		return $this->get_plugin_dir() . "/includes/footer.php";
	}

	public function is_price_range_expanded() {
		return !empty(get_option(self::KEY_APEX27_EXPAND_PRICE_RANGE));
	}

	public function should_show_overseas_dropdown() {
		return get_option(self::KEY_APEX27_SHOW_OVERSEAS_DROPDOWN) === "1";
	}

	public function is_recaptcha_enabled() {
		return get_option(self::KEY_APEX27_ENABLE_RECAPTCHA, "1") === "1";
	}

	public function get_recaptcha_site_key() {
		$options = $this->get_portal_options();
		return isset($options->recaptchaSiteKey) ? $options->recaptchaSiteKey : null;
	}

	public function format_text($text) {
		$html = htmlspecialchars($text);
		return preg_replace_callback("/(https?:\/\/\S+)/i", static function($matches) {
			return sprintf('<a href="%1$s" target="_blank">%1$s</a>', $matches[0]);
		}, $html);
	}

	public function has_google_api_key() {
		$options = $this->get_portal_options();
		return !empty($options->googleApiKey);
	}

	public function get_pagination_position() {
		$options = $this->get_portal_options();
		if($options && isset($options->paginationPosition)) {
			return $options->paginationPosition;
		}
		return "b";
	}

	public function add_open_graph_tags() {
		if($this->listing_details) {

			$details = $this->listing_details;

			$price = $details->displayPrice;
			$header = $details->header;
			$display_address = $details->displayAddress;

			$title = sprintf("%s | %s | %s", $price, $header, $display_address);
			$description = sprintf("%s, %s", $header, $display_address);

			$images = $details->images;
			$image_url = null;
			if($images) {
				$image_url = $images[0]->url;
			}

			?>
			<meta property="og:type" content="website" />

			<meta property="og:title" content="<?=htmlspecialchars($title)?>" />
			<meta property="twitter:title" content="<?=htmlspecialchars($title)?>" />

			<meta property="description" content="<?=htmlspecialchars($description)?>" />
			<meta property="og:description" content="<?=htmlspecialchars($description)?>" />
			<meta property="twitter:description" content="<?=htmlspecialchars($description)?>" />

			<?php
			if($image_url) {
				?>
				<meta property="og:image" content="<?=htmlspecialchars($image_url)?>" />
				<meta property="og:url" content="<?=htmlspecialchars($image_url)?>" />
				<?php
			}
		}

		if(isset($this->portal_options->twitterUsername)) {
			$username = $this->portal_options->twitterUsername;
			$url = "https://twitter.com/$username";
			?>
			<meta name="twitter:card" content="summary" />
			<meta name="twitter:site" content="<?=htmlspecialchars($url)?>" />
			<meta name="twitter:creator" content="<?=htmlspecialchars($url)?>" />
			<?php
		}

	}

	/**
	 * Plugin activation callback
	 */
	public static function activate() {
		// Force rewrite rules to be flushed on activation
		delete_option('apex27_rewrite_rules_flushed');
		
		// Set default options
		if (!get_option(self::KEY_APEX27_SHOW_OVERSEAS_DROPDOWN)) {
			update_option(self::KEY_APEX27_SHOW_OVERSEAS_DROPDOWN, '1');
		}
		if (!get_option(self::KEY_APEX27_ENABLE_RECAPTCHA)) {
			update_option(self::KEY_APEX27_ENABLE_RECAPTCHA, '1');
		}
	}

	/**
	 * Plugin deactivation callback
	 */
	public static function deactivate() {
		// Clean up rewrite rules
		flush_rewrite_rules();
		delete_option('apex27_rewrite_rules_flushed');
	}

	/**
	 * Show CRM sync status on settings page
	 */
	public function show_crm_sync_status() {
		if (!$this->is_configured()) {
			return;
		}

		$property_count = wp_count_posts('apex27_property')->publish ?? 0;
		$agent_count = wp_count_posts('apex27_agent')->publish ?? 0;
		$last_sync = get_option('apex27_last_sync_time');
		$text_domain = "apex27";
		
		?>
		<div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #00a0d2;">
			<h3><?php _e('CRM Data Sync Status', 'apex27'); ?></h3>
			<p><strong><?php _e('Properties in WordPress:', 'apex27'); ?></strong> <?php echo $property_count; ?></p>
			<p><strong><?php _e('Agents in WordPress:', 'apex27'); ?></strong> <?php echo $agent_count; ?></p>
			<?php if ($last_sync): ?>
				<p><strong><?php _e('Last Sync:', 'apex27'); ?></strong> 
				   <?php echo human_time_diff(strtotime($last_sync), current_time('timestamp')) . ' ' . __('ago', 'apex27'); ?>
				</p>
			<?php endif; ?>
			
			<p>
				<button type="button" class="button button-secondary" onclick="apex27ManualSync('properties')">
					<?php _e('Sync Properties Now', 'apex27'); ?>
				</button>
				<button type="button" class="button button-secondary" onclick="apex27ManualSync('agents')" style="margin-left: 10px;">
					<?php _e('Sync Agents Now', 'apex27'); ?>
				</button>
			</p>
			
			<p><em><?php _e('Note: Data automatically syncs hourly. Properties and agents are accessible to page builders as custom post types.', 'apex27'); ?></em></p>
		</div>
		
		<script>
		function apex27ManualSync(type) {
			const button = event.target;
			const originalText = button.textContent;
			button.textContent = '<?php _e('Syncing...', 'apex27'); ?>';
			button.disabled = true;
			
			fetch(ajaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: 'action=apex27_sync_' + type + '&nonce=' + '<?php echo wp_create_nonce('apex27_sync_nonce'); ?>'
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					console.log('Apex27 Sync Result:', data);
					button.textContent = '<?php _e('Sync Complete!', 'apex27'); ?>';
					let message = data.message || '';
					if (data.errors && data.errors.length > 0) {
						message += '\n\nErrors encountered:\n' + data.errors.join('\n');
					}
					if (message) {
						alert(message);
					}
					setTimeout(() => {
						button.textContent = originalText;
						button.disabled = false;
						location.reload();
					}, 2000);
				} else {
					console.error('Apex27 Sync Error:', data);
					button.textContent = '<?php _e('Sync Failed', 'apex27'); ?>';
					if (data.message) {
						alert('Sync failed: ' + data.message);
					}
					setTimeout(() => {
						button.textContent = originalText;
						button.disabled = false;
					}, 2000);
				}
			})
			.catch(error => {
				button.textContent = '<?php _e('Error', 'apex27'); ?>';
				setTimeout(() => {
					button.textContent = originalText;
					button.disabled = false;
				}, 2000);
			});
		}
		</script>
		<?php
	}

}

// Plugin activation hook
register_activation_hook(__FILE__, array('Apex27', 'activate'));

// Plugin deactivation hook
register_deactivation_hook(__FILE__, array('Apex27', 'deactivate'));

$apex27 = new Apex27();

$GLOBALS["apex27"] = $apex27;

