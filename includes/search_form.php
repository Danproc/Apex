<?php /** @noinspection PhpUnhandledExceptionInspection */

/**
 * @var $apex27
 */

$text_domain = "apex27";

$search_options = $apex27->get_search_options();
$portal_options = $apex27->get_portal_options();
if(!$portal_options) {
	throw new Exception(__("Failed to fetch website options.", $text_domain));
}

$default_sort = $portal_options->defaultSort;
$default_include_sstc = $portal_options->defaultIncludeSstc;

$sorts = $portal_options->sorts;

$currency_symbol = isset($search_options->currencySymbol) ? $search_options->currencySymbol : "£";

if(!$search_options || $search_options->success === false) {
	?>
	<p>
		<?=htmlspecialchars(__("Failed to fetch search options.", $text_domain))?>
	</p>
	<?php
	if($search_options) {
		?>
		<p><?php echo $search_options->message?></p>
		<?php
	}
}
else {

	$generate_dropdown = static function($label, $options, $selected_value, $name, $id, $placeholder = "") {
		?>
		<label for="property-search-<?=htmlspecialchars($id)?>" class="sr-only">
			<?=htmlspecialchars($label)?>
		</label>
		<select name="<?=htmlspecialchars($name)?>" id="property-search-<?=htmlspecialchars($id)?>">
			<?php

			if($placeholder) {
				?>
				<option value="">
					<?=htmlspecialchars($placeholder)?>
				</option>
				<?php
			}

			foreach($options as $option) {
				$value = $option->value;
				$display = $option->display;
				$selected = ((string) $value === (string) $selected_value) ? "selected" : "";
				?>
				<option value="<?=htmlspecialchars($value)?>" <?=$selected?>><?=htmlspecialchars($display)?></option>
				<?php
			}
			?>
		</select>
		<?php
	};

	$overseas_options = [
		(object) ["value" => 0, "display" => __("UK", $text_domain)],
		(object) ["value" => 1, "display" => __("Overseas", $text_domain)],
	];

	$include_sstc_options = [
		(object) ["value" => 0, "display" => __("Exclude Sold/Let", $text_domain)],
		(object) ["value" => 1, "display" => __("Include Sold/Let", $text_domain)],
	];

	$type = get_query_var("type");
	if(!$type && $search_options->defaultTransactionType) {
		$type = $search_options->defaultTransactionType;
	}

	$property_type = get_query_var("property_type");
	$overseas = get_query_var("overseas");
	$min_price = get_query_var("min_price");
	$max_price = get_query_var("max_price");
	$city = get_query_var("city");
	$min_beds = get_query_var("min_beds");
	$max_beds = get_query_var("max_beds");
	$min_gross_yield = get_query_var("min_gross_yield");
	$include_sstc = get_query_var("include_sstc", (int) $default_include_sstc);
	$sort = get_query_var("sort");
	if(!$sort) {
		$sort = $default_sort;
	}

	if($apex27->is_price_range_expanded()) {
		$sale_prices = array_merge(
			range(50000, 1000000, 50000),
			range(2000000, 5000000, 1000000),
			range(10000000, 50000000, 5000000)
		);
	}
	else {
		$sale_prices = array_merge(
			range(50000, 500000, 25000),
			range(550000, 1000000, 50000),
			range(1100000, 1500000, 100000),
			range(1750000, 3000000, 250000)
		);
	}


	$rent_prices = array_merge(
		range(100, 1000, 50),
		range(1250, 2000, 250),
		range(3000, 5000, 500)
	);

	$sale_price_options = [];
	foreach($sale_prices as $value) {
		$sale_price_options[] = (object) [
			"value" => $value,
			"display" => sprintf("%s%s", $currency_symbol, number_format($value))
		];
	}

	$rent_price_options = [];
	foreach($rent_prices as $value) {
		$rent_price_options[] = (object) [
			"value" => $value,
			"display" => sprintf("%s%s", $currency_symbol, number_format($value))
		];
	}

	$city_options = [];
	foreach($search_options->cities as $value) {
		$city_options[] = (object) ["value" => $value, "display" => $value];
	}

	$min_bedroom_options = [];
	foreach(range(1, 10) as $value) {
		$min_bedroom_options[] = (object) [
			"value" => $value,
			"display" => sprintf(__("At least %d beds", $text_domain), $value)
		];
	}

	$max_bedroom_options = [];
	foreach(range(1, 10) as $value) {
		$max_bedroom_options[] = (object) [
			"value" => $value,
			"display" => sprintf(__("At most %d beds", $text_domain), $value)
		];
	}

	$sort_options = [];
	foreach($sorts as $value => $display) {
		$sort_options[] = (object) ["value" => $value, "display" => __($display, $text_domain)];
	}

	$gross_yield_options = [];
	foreach($search_options->grossYields as $value) {
		$gross_yield_options[] = (object) ["value" => $value, "display" => $value . "%"];
	}

	$initial_price_options = $type === "rent" ? $rent_price_options : $sale_price_options;

	$translate_option = static function($option) use ($text_domain) {
		$option->display = __($option->display, $text_domain);
		return $option;
	};

	$translate_options = static function($options) use ($translate_option): array {
		foreach($options as $index => $option) {
			$options[$index] = $translate_option($option);
		}
		return $options;
	};

	$search_options->transactionTypes = $translate_options($search_options->transactionTypes);
	$search_options->propertyTypes = $translate_options($search_options->propertyTypes);


	?>
<h2><?=htmlspecialchars(__("Property Search", $text_domain))?></h2>

<form id="property-search-form" action="/property-search/" method="GET"
      data-sale-price-options="<?=htmlspecialchars(json_encode($sale_price_options))?>"
      data-rent-price-options="<?=htmlspecialchars(json_encode($rent_price_options))?>">

    <!-- Wrap all fields in a parent container -->
    <div class="apex27-form-fields d-flex flex-row flex-wrap">
        <div class="apex27-form-field">
            <?php $generate_dropdown(__("Type", $text_domain), $search_options->transactionTypes, $type, "type", "type"); ?>
        </div>
        <div class="apex27-form-field">
            <?php $generate_dropdown(__("Property Type", $text_domain), $search_options->propertyTypes, $property_type, "property_type", "property-type", __("Property Type", $text_domain)); ?>
        </div>
        <?php if($apex27->should_show_overseas_dropdown()) { ?>
            <div class="apex27-form-field">
                <?php $generate_dropdown(__("Overseas", $text_domain), $overseas_options, $overseas, "overseas", "overseas"); ?>
            </div>
        <?php } ?>
        <div class="apex27-form-field">
            <?php $generate_dropdown(__("Min. Price", $text_domain), $initial_price_options, $min_price, "min_price", "min-price", __("Min. Price", $text_domain)); ?>
        </div>
        <div class="apex27-form-field">
            <?php $generate_dropdown(__("Max. Price", $text_domain), $initial_price_options, $max_price, "max_price", "max-price", __("Max. Price", $text_domain)); ?>
        </div>
        <div class="apex27-form-field">
            <?php $generate_dropdown(__("City", $text_domain), $city_options, $city, "city", "city", __("Location", $text_domain)); ?>
        </div>
        <div class="apex27-form-field">
            <?php $generate_dropdown(__("Min. Bedrooms", $text_domain), $min_bedroom_options, $min_beds, "min_beds", "min-beds", __("Min. Bedrooms", $text_domain)); ?>
        </div>
        <div class="apex27-form-field">
            <?php $generate_dropdown(__("Max. Bedrooms", $text_domain), $max_bedroom_options, $max_beds, "max_beds", "max-beds", __("Max. Bedrooms", $text_domain)); ?>
        </div>
        <?php if($gross_yield_options) { ?>
            <div class="apex27-form-field">
                <?php $generate_dropdown(__("Gross Yield", $text_domain), $gross_yield_options, $min_gross_yield, "min_gross_yield", "min-gross-yield", __("Gross Yield", $text_domain)); ?>
            </div>
        <?php } ?>
        <div class="apex27-form-field">
            <?php $generate_dropdown(__("Include SSTC", $text_domain), $include_sstc_options, $include_sstc, "include_sstc", "include-sstc"); ?>
        </div>
        <div class="apex27-form-field">
            <?php $generate_dropdown(__("Sort", $text_domain), $sort_options, $sort, "sort", "sort"); ?>
        </div>
    </div>

    <!-- Place the button outside the .apex27-form-fields if you want a perfect 5x2 layout just for the fields -->
    <div class="apex27-form-actions">
        <button type="submit" class="apex27-search-submit">
            <?=htmlspecialchars(__("Update"))?>
        </button>
    </div>
</form>

	<?php
}
