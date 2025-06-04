<?php
/**
 * Template Name: Property Details
 */

/**
 * @var Apex27 $apex27
 */

$text_domain = "apex27";

$options = $apex27->get_portal_options();
$details = $apex27->get_property_details();
$apex27->set_listing_details($details);

$featured = !empty($details->isFeatured);

$form_path = $apex27->get_template_path("enquiry-form");

get_header();

if(!$details) {
	?>
	<div class="apex27__property">
		<h2 class="apex27__property-heading"><?= htmlspecialchars(__("Error", $text_domain)) ?></h2>
		<p class="apex27__property-error"><?= htmlspecialchars(__("Cannot retrieve property details at this time. Please try again later.", $text_domain)) ?></p>
	</div>
	<?php
	get_footer();
	return;
}

$get_lines = static function($string) {
	$lines = explode("\n", $string);
	$lines = array_map("trim", $lines);
	return array_filter($lines);
};

$description_lines = $get_lines($details->description);
$description_line_1 = array_shift($description_lines);

$generate_media_section = static function($title, $id, $items) {
    $images = [];
    $others = [];

    // Split items into image vs non-image
    foreach ($items as $item) {
        if ($item->type === "image") {
            $images[] = $item;
        } else {
            $others[] = $item;
        }
    }

    // Only render if we have something to show
    if ($images || $others) {
        ?>
        <h4 class="apex27__property-subheading"
            id="property-details-<?= htmlspecialchars($id) ?>">
            <?= htmlspecialchars($title) ?>
        </h4>
        <div class="apex27__property-media-gallery">
        <?php
    }

    // Render each image as one visible thumbnail + lightbox link
    foreach ($images as $item) {
        // skip if no thumbnail
        if (empty($item->thumbnailUrl)) {
            continue;
        }
        ?>
        <a href="<?= htmlspecialchars($item->url) ?>"
           data-fslightbox="<?= htmlspecialchars($id) ?>"
           data-type="image"
           class="apex27__property-media-thumbnail">
            <img src="<?= htmlspecialchars($item->thumbnailUrl) ?>"
                 alt="<?= htmlspecialchars($item->name) ?>" />
        </a>
        <?php
    }

    // Render non-image files as a list
    if ($others) {
        ?>
        <ul class="apex27__property-media-list">
            <?php foreach ($others as $item): ?>
                <li class="apex27__property-media-list-item">
                    <a href="<?= htmlspecialchars($item->url) ?>"
                       target="_blank"
                       class="apex27__property-media-link">
                        <?= htmlspecialchars($item->name) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }

    // Close gallery wrapper if we opened it
    if ($images || $others) {
        ?></div><?php
    }
};



$referer = !empty($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : null;
$show_back = strpos($referer, "search") !== false;

?>
<div class="apex27__property-details" data-featured="<?= json_encode($featured) ?>">

	<header class="apex27__property-details-header">
		<h2 class="apex27__property-details-title" dir="auto">
			<?= htmlspecialchars($details->displayAddress) ?>
		</h2>

		<div class="apex27__property-details-status-container" dir="auto">
			<?php if ($details->imageOverlayText): ?>
				<span class="apex27__property-details-status">
					<?= htmlspecialchars($details->imageOverlayText) ?>
				</span>
			<?php endif; ?>

			<?php if ($details->banner): ?>
				<span class="apex27__property-details-banner">
					<?= htmlspecialchars($details->banner) ?>
				</span>
			<?php endif; ?>
		</div>

		<?php if (!$details->isCommercial): ?>
			<p class="apex27__property-specs" dir="auto">
				<span class="apex27__property-specs-item" title="Bedrooms">
					<i class="fa fa-fw fa-bed"></i> <?= htmlspecialchars($details->bedrooms) ?>
				</span>
				<span class="apex27__property-specs-item" title="Bathrooms">
					<i class="fa fa-fw fa-bath"></i> <?= htmlspecialchars($details->bathrooms) ?>
				</span>
				<span class="apex27__property-specs-item" title="Living Rooms">
					<i class="fa fa-fw fa-couch"></i> <?= htmlspecialchars($details->livingRooms) ?>
				</span>
				<?php if($details->garages): ?>
					<span class="apex27__property-specs-item" title="<?= htmlspecialchars(__("Garages", $text_domain)) ?>">
						<i class="fa fa-fw fa-car"></i> <?= htmlspecialchars($details->garages) ?>
					</span>
				<?php endif; ?>
			</p>
		<?php endif; ?>

		<div class="apex27__property-price" dir="auto">
			<span class="apex27__property-price-amount">
				<?= htmlspecialchars($details->displayPrice) ?>
			</span>
			<small class="apex27__property-price-prefix">
				<?= htmlspecialchars($details->pricePrefix) ?>
			</small>
			<strong class="apex27__property-subtitle">
				<?= htmlspecialchars($details->subtitle) ?>
			</strong>
		</div>
	</header>

	<div class="apex27__property-details-body">
		<div class="apex27__property-details-left-column">
			<?php if ($details->images): ?>
				<section class="apex27__property-slider" id="property-details-slider">
					<?php
					$image = $details->images[0];
					$thumbnail_url = $image->thumbnailUrl;
					$name = $image->name;
					?>
					<div class="apex27__property-slider-main" id="property-details-slider-main-container">
						<img src="<?= htmlspecialchars($thumbnail_url) ?>" alt="<?= htmlspecialchars($name) ?>" />
					</div>

					<a id="property-details-slider-prev" class="apex27__property-slider-nav apex27__property-slider-nav--prev" href="#">
						<i class="fa fa-arrow-left"></i>
					</a>
					<a id="property-details-slider-next" class="apex27__property-slider-nav apex27__property-slider-nav--next" href="#">
						<i class="fa fa-arrow-right"></i>
					</a>

					<?php if ($details->imageOverlayText): ?>
						<span class="apex27__property-slider-overlay">
							<?= htmlspecialchars($details->imageOverlayText) ?>
						</span>
					<?php endif; ?>
				</section>

				<div class="apex27__property-slider-thumbnails" id="property-details-thumbnails">
					<?php
					$index = 0;
					foreach($details->images as $image) {
						$active = $index === 0 ? "active" : "";
						$url = $image->url;
						$thumbnail_url = $image->thumbnailUrl;
						$name = $image->name;
						?>
						<img class="apex27__thumbnail <?= $active ?>" 
						     src="<?= htmlspecialchars($thumbnail_url) ?>" 
						     alt="<?= htmlspecialchars($name) ?>" 
						     data-type="image" />
						<a data-fslightbox="slider" data-type="image" href="<?= htmlspecialchars($url) ?>"></a>
						<?php
						$index++;
					}

					foreach ($details->videos as $vIndex => $video) {
						$active = $vIndex === 0 ? "active" : "";
						$url = $video->url;
						$thumbnail_url = $apex27->get_plugin_url() . "assets/img/video.png";
						$caption = "Video";
						?>
						<img class="apex27__thumbnail <?= $active ?>" 
						     src="<?= htmlspecialchars($thumbnail_url) ?>" 
						     alt="<?= htmlspecialchars($caption) ?>" 
						     data-type="video" 
						     data-url="<?= htmlspecialchars($url) ?>" />
						<a data-fslightbox="slider" data-type="video" href="<?= htmlspecialchars($url) ?>"></a>
						<?php
						$index++;
					}
					?>
				</div>
			<?php endif; ?>

			<div class="apex27__property-media-buttons" id="property-details-media-buttons">
				<?php if($details->floorplans): ?>
					<a href="#property-details-floorplans" class="apex27__property-media-button" data-type="floorplans">
						<?= htmlspecialchars(__("Floorplans", $text_domain)) ?>
					</a>
				<?php endif; ?>

				<?php if($details->epcs): ?>
					<a href="#property-details-epcs" class="apex27__property-media-button" data-type="epcs">
						<?= htmlspecialchars(__("EPC", $text_domain)) ?>
					</a>
				<?php endif; ?>

				<?php if($details->brochures): ?>
					<?php $brochure_count = count($details->brochures); ?>
					<?php if($brochure_count > 1): ?>
						<a href="#property-details-brochures" class="apex27__property-media-button" data-type="brochures">
							<?= htmlspecialchars(__("Brochures", $text_domain)) ?>
						</a>
					<?php else: ?>
						<?php $brochure = $details->brochures[0]; ?>
						<a href="<?= htmlspecialchars($brochure->url) ?>" 
						   class="apex27__property-media-button" 
						   target="_blank">
							<?= htmlspecialchars(__("Brochure", $text_domain)) ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>

				<?php if($details->videos): ?>
					<?php $video_count = count($details->videos); ?>
					<?php if($video_count > 1): ?>
						<a href="#property-details-videos" class="apex27__property-media-button">
							<?= htmlspecialchars(__("Videos", $text_domain)) ?>
						</a>
					<?php else: ?>
						<?php $video = $details->videos[0]; ?>
						<a href="<?= htmlspecialchars($video->url) ?>"
						   class="apex27__property-media-button" 
						   target="_blank">
							<?= htmlspecialchars(__("Video", $text_domain)) ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>

				<?php if($details->virtualTours): ?>
					<?php $virtual_tour_count = count($details->virtualTours); ?>
					<?php if($virtual_tour_count > 1): ?>
						<a href="#property-details-virtual-tours" class="apex27__property-media-button">
							<?= htmlspecialchars(__("Virtual Tours", $text_domain)) ?>
						</a>
					<?php else: ?>
						<?php $virtual_tour = $details->virtualTours[0]; ?>
						<a href="<?= htmlspecialchars($virtual_tour->url) ?>"
						   class="apex27__property-media-button" 
						   target="_blank">
							<?= htmlspecialchars(__("Virtual Tour", $text_domain)) ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>

				<?php if($options->hasGoogleApiKey && $details->hasGeolocation): ?>
					<a href="#property-details-map" class="apex27__property-media-button">
						<?= htmlspecialchars(__("Map", $text_domain)) ?>
					</a>
				<?php endif; ?>

				<?php if($options->hasGoogleApiKey && $details->hasPov): ?>
					<a href="#property-details-street-view" class="apex27__property-media-button">
						<?= htmlspecialchars(__("Street View", $text_domain)) ?>
					</a>
				<?php endif; ?>

				<a href="#property-details-contact-form" class="apex27__property-media-button apex27__property-media-button--enquiry">
					<?= htmlspecialchars(__("Make Enquiry", $text_domain)) ?>
				</a>

				<?php if($show_back): ?>
					<a href="<?= htmlspecialchars($referer) ?>" class="apex27__property-media-button apex27__property-media-button--back">
						<?= htmlspecialchars(__("Back to Search Results", $text_domain)) ?>
					</a>
				<?php endif; ?>
			</div>

			<section class="apex27__property-description">
				<h4 class="apex27__property-subheading" dir="auto">
					<?= htmlspecialchars(__("Description", $text_domain)) ?>
				</h4>

				<?php if ($details->description): ?>
					<div class="apex27__property-description-intro">
						<p dir="auto"><?= htmlspecialchars($description_line_1) ?></p>
					</div>
					<div class="apex27__property-description-body">
						<?php foreach($description_lines as $line): ?>
							<p dir="auto"><?= $apex27->format_text($line) ?></p>
						<?php endforeach; ?>
					</div>
				<?php else: ?>
					<p dir="auto">
						<em><?= htmlspecialchars(__("No description is available for this property.", $text_domain)) ?></em>
					</p>
				<?php endif; ?>

				<?php if($details->grossYield): ?>
					<p class="apex27__property-gross-yield" dir="auto">
						<?= htmlspecialchars(__("Gross Yield", $text_domain)) ?>: 
						<?= htmlspecialchars($details->grossYield) ?>
					</p>
				<?php endif; ?>
			</section>

			<?php if($details->rooms): ?>
				<section class="apex27__property-rooms">
					<?php foreach($details->rooms as $room): ?>
						<div class="apex27__property-rooms-item">
							<h5 class="apex27__property-rooms-item-title" dir="auto">
								<?= htmlspecialchars($room->name) ?>
							</h5>
							<?php if($room->dimensions): ?>
								<div class="apex27__property-rooms-item-dimensions" dir="auto">
									<em><?= htmlspecialchars($room->dimensions) ?> (<?= htmlspecialchars($room->feetInches) ?>)</em>
								</div>
							<?php endif; ?>
							<div class="apex27__property-rooms-item-description" dir="auto">
								<?= htmlspecialchars($room->description) ?>
							</div>
						</div>
					<?php endforeach; ?>
				</section>
			<?php endif; ?>

			<div class="apex27__property-reference" dir="auto">
				<small>
					<?= htmlspecialchars(__("Reference", $text_domain)) ?>: 
					<?= htmlspecialchars($details->reference) ?>
				</small>
			</div>

			<?php if (isset($details->disclaimer)): ?>
				<div class="apex27__property-disclaimer" dir="auto">
					<small><?= htmlspecialchars($details->disclaimer) ?></small>
				</div>
			<?php endif; ?>

			<?php if($details->additionalDetails): ?>
				<section class="apex27__property-additional-details">
					<h4 class="apex27__property-subheading" dir="auto">
						<?= htmlspecialchars(__("Additional Details", $text_domain)) ?>
					</h4>
					<ul class="apex27__property-additional-list">
						<?php foreach($details->additionalDetails as $detail): ?>
							<li dir="auto" class="apex27__property-additional-list-item">
								<strong><?= htmlspecialchars($detail->label) ?>:</strong>
								<?= htmlspecialchars($detail->text) ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if($details->additionalFeatures): ?>
				<section class="apex27__property-features">
					<h4 class="apex27__property-subheading" dir="auto">
						<?= htmlspecialchars(__("Additional Features", $text_domain)) ?>
					</h4>
					<ul class="apex27__property-features-list">
						<?php foreach($details->additionalFeatures as $feature): ?>
							<li class="apex27__property-features-item" dir="auto">
								<?= htmlspecialchars($feature) ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if(isset($details->broadbandSpeeds)): ?>
				<section class="apex27__property-broadband">
					<h4 class="apex27__property-subheading" dir="auto">
						<?= htmlspecialchars(__("Broadband Speeds", $text_domain)) ?>
					</h4>
					<ul class="apex27__property-broadband-list">
						<li>
							<strong><?= htmlspecialchars(__("Download:", $text_domain)) ?></strong>
							<?= htmlspecialchars($details->broadbandSpeeds->download->min) ?>mbps - 
							<?= htmlspecialchars($details->broadbandSpeeds->download->max) ?>mbps
						</li>
						<li>
							<strong><?= htmlspecialchars(__("Upload:", $text_domain)) ?></strong>
							<?= htmlspecialchars($details->broadbandSpeeds->upload->min) ?>mbps - 
							<?= htmlspecialchars($details->broadbandSpeeds->upload->max) ?>mbps
						</li>
					</ul>
					<p class="apex27__property-broadband-note">
						<?= htmlspecialchars(__("Estimated broadband speeds provided by Ofcom for this property's postcode.", $text_domain)) ?>
					</p>
				</section>
			<?php endif; ?>

			<!-- Floorplans, EPCs, Brochures, Videos, Virtual Tours -->
			<?php if($details->floorplans): ?>
				<section class="apex27__property-floorplans">
					<?php $generate_media_section(__("Floorplans", $text_domain), "floorplans", $details->floorplans); ?>
				</section>
			<?php endif; ?>

			<?php if($details->epcs): ?>
				<section class="apex27__property-epcs">
					<?php $generate_media_section(__("EPCs", $text_domain), "epcs", $details->epcs); ?>
				</section>
			<?php endif; ?>

			<?php if($details->brochures): ?>
				<section class="apex27__property-brochures">
					<?php $generate_media_section(__("Brochures", $text_domain), "brochures", $details->brochures); ?>
				</section>
			<?php endif; ?>

			<?php if($details->videos): ?>
				<section class="apex27__property-videos">
					<?php $generate_media_section(__("Videos", $text_domain), "videos", $details->videos); ?>
				</section>
			<?php endif; ?>

			<?php if($details->virtualTours): ?>
				<section class="apex27__property-virtual-tours" id="property-details-virtual-tours">
					<h4 class="apex27__property-subheading" dir="auto">
						<?= htmlspecialchars(__("Virtual Tours", $text_domain)) ?>
					</h4>
					<ul class="apex27__property-virtual-tours-list">
						<?php foreach($details->virtualTours as $tour): ?>
							<li class="apex27__property-virtual-tours-item" dir="auto">
								<a href="<?= htmlspecialchars($tour->url) ?>" 
								   target="_blank"
								   class="apex27__property-virtual-tours-link">
									<?= htmlspecialchars(__("View Virtual Tour", $text_domain)) ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if($options->hasGoogleApiKey && $details->hasGeolocation): ?>
				<section class="apex27__property-map" id="property-details-map">
					<h4 class="apex27__property-subheading" dir="auto">
						<?= htmlspecialchars(__("Map", $text_domain)) ?>
					</h4>
					<iframe 
						class="apex27__property-map-iframe" 
						style="border:0;" 
						width="100%" 
						height="450" 
						src="<?= htmlspecialchars($details->mapEmbedUrl) ?>" 
						allowfullscreen>
					</iframe>
				</section>
			<?php endif; ?>

			<?php if($options->hasGoogleApiKey && $details->hasPov): ?>
				<section class="apex27__property-street-view" id="property-details-street-view">
					<h4 class="apex27__property-subheading" dir="auto">
						<?= htmlspecialchars(__("Street View", $text_domain)) ?>
					</h4>
					<iframe 
						class="apex27__property-street-view-iframe" 
						style="border:0;" 
						width="100%" 
						height="450" 
						src="<?= htmlspecialchars($details->streetViewEmbedUrl) ?>" 
						allowfullscreen>
					</iframe>
				</section>
			<?php endif; ?>
		</div>

		<aside class="apex27__property-details-right-column" dir="auto">
			<?php if($details->bullets): ?>
				<section class="apex27__property-features-highlights">
					<h3 class="apex27__property-subheading apex27__property-subheading--features" dir="auto">
						<?= htmlspecialchars(__("Features", $text_domain)) ?>
					</h3>
					<ul class="apex27__property-bullets-list">
						<?php foreach($details->bullets as $bullet): ?>
							<li class="apex27__property-bullets-item" dir="auto">
								<span class="apex27__property-bullet-text">
									<?= htmlspecialchars($bullet) ?>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<section class="apex27__property-contact-form" id="property-details-contact-form">
				<?php
				// Include the enquiry form template
				$apex27->include_template($form_path, [
					"property_details" => $details
				]);
				?>
			</section>
		</aside>
	</div>

	<?php
	if($details->showLogo) {
		require $apex27->get_footer_path();
	}
	?>
</div>

<?php get_footer(); ?>
