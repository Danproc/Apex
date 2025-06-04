<?php
/**
 * Template Name: Search Results
 */

/**
 * @var Apex27 $apex27
 */

$text_domain = "apex27";

$plugin_url = $apex27->get_plugin_url();
$plugin_dir = $apex27->get_plugin_dir();

get_header();

$search_results = $apex27->get_search_results();
if (!$search_results) {
	?>
	<div class="apex27-container apex27-error-container">
		<section class="apex27-error-section">
			<h2 dir="auto" class="apex27-error-heading"><?= htmlspecialchars(__("Error", $text_domain)) ?></h2>
			<p dir="auto" class="apex27-error-text"><?= htmlspecialchars(__("Cannot retrieve properties at this time. Please try again later.", $text_domain)) ?></p>
		</section>
	</div>
	<?php
	get_footer();
	return;
}

$query = $_GET;

$page = (int) (get_query_var("page") ?: 1);
$page_size = 10;

$prev_page_query = array_merge($_GET, ["page" => $page - 1]);
$prev_page_url = "/property-search/?" . http_build_query($prev_page_query);

$next_page_query = array_merge($_GET, ["page" => $page + 1]);
$next_page_url = "/property-search/?" . http_build_query($next_page_query);

$has_listings = $search_results->listingCount > 0;

$pagination_position = $apex27->get_pagination_position();
$has_top_pagination = strpos($pagination_position, "t") !== false;
$has_bottom_pagination = strpos($pagination_position, "b") !== false;

if ($search_results->listingCount === 0) {
	$page_info = __("No properties", $text_domain);
} else if ($search_results->listingCount <= $page_size) {
	$page_info = sprintf(_n("Showing %d property", "Showing %d properties", $search_results->listingCount, $text_domain), $search_results->listingCount);
} else {
	$offset = ($page - 1) * $page_size;
	$first_item = $offset + 1;
	$last_item = $offset + $page_size;
	if ($last_item > $search_results->listingCount) {
		$last_item = $search_results->listingCount;
	}
	$page_info = sprintf(__("Showing %d-%d of %d properties", $text_domain), $first_item, $last_item, $search_results->listingCount);
}

$render_pagination = static function() use ($page, $prev_page_url, $text_domain, $search_results, $next_page_url) {
	?>
	<div class="apex27-pagination">
		<div class="apex27-pagination-prev">
			<?php if($page !== 1): ?>
				<a href="<?= htmlspecialchars($prev_page_url) ?>"><?= htmlspecialchars(__("Previous Page", $text_domain)) ?></a>
			<?php endif; ?>
		</div>
		<div class="apex27-pagination-info" dir="auto">
			<?= sprintf(__("Page %d of %d", $text_domain), $page, $search_results->pageCount) ?>
		</div>
		<div class="apex27-pagination-next" dir="auto">
			<?php if($page < $search_results->pageCount): ?>
				<a href="<?= htmlspecialchars($next_page_url) ?>"><?= htmlspecialchars(__("Next Page", $text_domain)) ?></a>
			<?php endif; ?>
		</div>
	</div>
	<?php
};
?>

<div class="apex27-container">

	<!-- Search Form Section -->
	<section class="apex27-search-section">
		<?php
		require $plugin_dir . "/includes/search_form.php";
		get_template_part("search_form");
		?>
	</section>

	<!-- Properties Section -->
	<section class="apex27-properties-section">
		<h2 dir="auto" class="apex27-properties-heading"><?= htmlspecialchars(__("Properties", $text_domain)) ?></h2>

		<?php
		$properties = $search_results->listings;
		$markers = $search_results->markers;
		?>

		<div class="apex27-properties-header">
			<?php if($has_listings): ?>
				<div class="apex27-properties-info d-flex mb-3" style="align-items: center">
					<div class="flex-fill">
						<h6 class="apex27-properties-count" style="margin: 0;"><?= htmlspecialchars($page_info) ?></h6>
					</div>
					<?php if($markers && $apex27->has_google_api_key()): ?>
						<div class="apex27-map-toggle">
							<button id="apex27-toggle-map-button" type="button" class="btn apex27-map-toggle-btn">
								<?= htmlspecialchars(__("Toggle Map", $text_domain)) ?>
							</button>
						</div>
					<?php endif; ?>
				</div>
			<?php else: ?>
				<h6 class="apex27-no-properties" dir="auto"><?= htmlspecialchars(__("No properties to display.", $text_domain)) ?></h6>
			<?php endif; ?>
		</div>

		<?php if($markers && $apex27->has_google_api_key()): ?>
			<input type="hidden" id="listings-json" value="<?= htmlspecialchars(json_encode($markers)) ?>" />
			<div id="apex27-map-container" class="apex27-map-container" style="display: none;">
				<div class="apex27-map" id="apex27-map" style="height: 480px; background: rgba(0, 0, 0, .1);"></div>
			</div>
		<?php endif; ?>

		<?php if ($has_listings && $has_top_pagination) $render_pagination(); ?>

		<!-- Properties Grid -->
		<div class="apex27-properties-grid">
			<?php foreach($properties as $property):
				$thumbnail_url = $property->thumbnailUrl ?: $plugin_url . "assets/img/property.png";
				$slug = $apex27->get_property_slug($property);
				$property_url = "/property-details/{$property->transactionTypeRoute}/{$slug}/{$property->id}";
				$featured = !empty($property->isFeatured);
				$is_commercial = strpos($property->transactionTypeRoute, "commercial") === 0;
				?>
				<div class="apex27-property-card <?= $featured ? 'apex27-featured' : '' ?>">
					<div class="apex27-property-card-body">
						<div class="apex27-property-card-content d-flex">
							<a class="apex27-property-image-link" href="<?= htmlspecialchars($property_url) ?>">
								<img class="apex27-property-image img-fluid" src="<?= htmlspecialchars($thumbnail_url) ?>" alt="" />
								<?php if($property->imageOverlayText): ?>
									<span class="apex27-property-overlay-text"><?= htmlspecialchars($property->imageOverlayText) ?></span>
								<?php endif; ?>
								<?php if($property->banner): ?>
									<span class="apex27-property-banner-text"><?= htmlspecialchars($property->banner) ?></span>
								<?php endif; ?>
							</a>
							<div class="apex27-property-details">
								<div class="apex27-property-top-meta mb-3" dir="auto">
									<?php if(!$is_commercial): ?>
										<div class="apex27-property-icons text-brand" style="float: right">
											<span title="<?= htmlspecialchars(__("Bedrooms", $text_domain)) ?>"><i class="fa fa-fw fa-bed"></i> <?= htmlspecialchars($property->bedrooms) ?></span>
											<span title="<?= htmlspecialchars(__("Bathrooms", $text_domain)) ?>"><i class="fa fa-fw fa-bath"></i> <?= htmlspecialchars($property->bathrooms) ?></span>
											<span title="<?= htmlspecialchars(__("Living Rooms", $text_domain)) ?>"><i class="fa fa-fw fa-couch"></i> <?= htmlspecialchars($property->livingRooms) ?></span>
											<?php if($property->garages): ?>
												<span title="<?= htmlspecialchars(__("Garages", $text_domain)) ?>"><i class="fa fa-fw fa-car"></i> <?= htmlspecialchars($property->garages) ?></span>
											<?php endif; ?>
										</div>
									<?php endif; ?>

									<?php if($featured): ?>
										<span class="apex27-featured-badge"><?= htmlspecialchars(__("Featured Property", $text_domain)) ?></span><br />
									<?php endif; ?>

									<strong class="apex27-property-address"><?= htmlspecialchars($property->displayAddress) ?></strong>
								</div>

								<div class="apex27-property-price mb-3" dir="auto">
									<strong class="text-brand">
										<?= htmlspecialchars($property->displayPrice) ?>
										<small><?= htmlspecialchars($property->pricePrefix) ?></small>
									</strong><br />
									<?= htmlspecialchars($property->subtitle) ?>
								</div>

								<div class="apex27-property-summary flex-fill" dir="auto">
									<p><?= htmlspecialchars($property->summary) ?></p>

									<?php if($property->incomeDescription): ?>
										<p><?= htmlspecialchars(__("Gross Income", $text_domain)) ?>: <?= nl2br(htmlspecialchars($property->incomeDescription)) ?></p>
									<?php endif; ?>

									<?php if($property->grossYield): ?>
										<p><?= htmlspecialchars(__("Gross Yield", $text_domain)) ?>: <?= htmlspecialchars($property->grossYield) ?></p>
									<?php endif; ?>

									<?php if($property->saleFee && $property->saleFeePayableByBuyer): ?>
										<p><?= htmlspecialchars(__("Buyer Fee", $text_domain)) ?>: <?= htmlspecialchars($property->saleFee) ?> + VAT</p>
									<?php endif; ?>
								</div>

								<div class="apex27-property-actions" dir="auto">
									<a class="apex27-property-details-btn btn btn-brand" href="<?= htmlspecialchars($property_url) ?>">
										<?= htmlspecialchars(__("Property Details", $text_domain)) ?>
									</a>
									<a class="apex27-property-enquiry-btn btn btn-brand" href="<?= htmlspecialchars($property_url) ?>#property-details-contact-form">
										<?= htmlspecialchars(__("Make Enquiry", $text_domain)) ?>
									</a>
								</div>

							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ($has_listings && $has_bottom_pagination) $render_pagination(); ?>
	</section>

	<!-- Enquiry Form Section -->
	<section class="apex27-enquiry-section">
		<?php
		$apex27->include_template(
			$apex27->get_template_path("enquiry-form")
		);
		?>
	</section>

	<!-- Footer Section -->
	<section class="apex27-credits-section">
		<?php if($search_results->showLogo) {
			require $apex27->get_footer_path();
		} ?>
	</section>

</div>

<?php get_footer(); ?>
