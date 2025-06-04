<?php

/**
 * @var object $property_details Property details
 */

/** @var Apex27 $apex27 */
$apex27 = $GLOBALS["apex27"];

$options = $apex27->get_portal_options();

$recaptcha_site_key = isset($options->recaptchaSiteKey) ? $options->recaptchaSiteKey : null;

$text_domain = "apex27";

if(isset($property_details)) {
	$branch_html = __("To make an enquiry for this property, please complete the form below.", $text_domain);

	if($property_details->branch->phone) {
		$branch_html = sprintf(
			__("To make an enquiry for this property, please call us on <strong>%s</strong>, or complete the form below.", $text_domain),
			htmlspecialchars($property_details->branch->phone)
		);
	}

	$listing_id = $property_details->id;

	$contact_url = admin_url("admin-ajax.php?action=property_details_contact");
}
else {
	$branches = $options->branches;

	if(empty($branches)) {
		return;
	}

	$branch_html = __("To make an enquiry, please complete the form below.", $text_domain);

	if(count($branches) === 1) {
		$branch = $branches[0];
		if($branch->phone) {
			$branch_html = sprintf(
				__("To make an enquiry, please call us on <strong>%s</strong>, or complete the form below.", $text_domain),
				htmlspecialchars($branch->phone)
			);
		}
	}

	$contact_url = admin_url("admin-ajax.php?action=branch_contact");
}


?>

<h3 class="text-brand" dir="auto"><?=htmlspecialchars(__("Enquiry", $text_domain))?></h3>

<p dir="auto">
	<?=$branch_html?>
</p>

<form id="apex27-contact-form" method="post" action="<?=htmlspecialchars($contact_url)?>" class="mb-5" data-recaptcha-site-key="<?=htmlspecialchars($recaptcha_site_key)?>">
	<?php wp_nonce_field('apex27_contact_form', 'apex27_nonce'); ?>

	<?php
	if(isset($listing_id)) {
		?>
		<input type="hidden" name="listing_id" value="<?=$listing_id?>" />
		<?php
	}

	if(isset($branches)) {
		if(count($branches) === 1) {
			$branch = $branches[0];
			?>
			<input type="hidden" name="branch_id" value="<?=$branch->id?>" />
			<?php
		}
		else {
			?>
			<div class="form-group" dir="auto">
				<label for="apex27-branch-id"><?=htmlspecialchars(__("Branch", $text_domain))?></label>
				<select name="branch_id" id="apex27-branch-id" required>
					<option value=""><?=htmlspecialchars(__("Select Branch", $text_domain))?></option>
					<?php
					foreach($branches as $branch) {
						?>
						<option value="<?=$branch->id?>">
							<?=htmlspecialchars($branch->name)?>
						</option>
						<?php
					}
					?>
				</select>
			</div>
			<?php
		}
	}

	?>

	<div class="form-group" dir="auto">
		<label for="apex27-first-name"><?=htmlspecialchars(__("First Name", $text_domain))?></label>
		<input id="apex27-first-name" type="text" name="first_name" placeholder="<?=htmlspecialchars(__("Enter first name", $text_domain))?>" required />
	</div>

	<div class="form-group" dir="auto">
		<label for="apex27-last-name"><?=htmlspecialchars(__("Last Name", $text_domain))?></label>
		<input id="apex27-last-name" type="text" name="last_name" placeholder="<?=htmlspecialchars(__("Enter last name", $text_domain))?>" required />
	</div>

	<div class="form-group" dir="auto">
		<label for="apex27-email"><?=htmlspecialchars(__("Email", $text_domain))?></label>
		<input id="apex27-email" type="email" name="email" placeholder="<?=htmlspecialchars(__("Enter email", $text_domain))?>" required />
	</div>

	<div class="form-group" dir="auto">
		<label for="apex27-phone"><?=htmlspecialchars(__("Phone", $text_domain))?></label>
		<input id="apex27-phone" type="tel" name="phone" placeholder="<?=htmlspecialchars(__("Enter phone", $text_domain))?>" />
	</div>

	<div class="form-group" dir="auto">
		<label for="apex27-message"><?=htmlspecialchars(__("Message", $text_domain))?></label>
		<textarea id="apex27-message" rows="6" name="message" placeholder="<?=htmlspecialchars(__("Enter message", $text_domain))?>"></textarea>
	</div>

	<?php
	if(isset($property_details)) {
		?>
		<div class="form-group" dir="auto">
			<input type="checkbox" name="request_listing_details" id="apex27-request-listing-details" value="1" />
			<label for="apex27-request-listing-details"><?=htmlspecialchars(__("I want more details about this property", $text_domain))?></label>
		</div>
		<div class="form-group" dir="auto">
			<input type="checkbox" name="request_viewing" id="apex27-request-viewing" value="1" />
			<label for="apex27-request-viewing"><?=htmlspecialchars(__("I want to view this property", $text_domain))?></label>
		</div>
		<?php
	}
	?>

	<div class="form-group" dir="auto">
		<input type="checkbox" name="request_valuation" id="apex27-request-valuation" value="1" />
		<label for="apex27-request-valuation"><?=htmlspecialchars(__("I want a valuation", $text_domain))?></label>
	</div>

	<?php
	if($apex27->is_recaptcha_enabled()) {
		?>
		<input type="hidden" name="token" id="apex27-recaptcha-token" value="" />
		<?php
	}
	?>

	<div class="form-group" dir="auto">
		<button class="btn btn-brand" type="submit">
			<?=htmlspecialchars(__("Submit", $text_domain))?>
		</button>
	</div>

</form>

<span id="apex27-contact-form-result">

</span>
