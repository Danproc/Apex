# Apex27 WordPress Plugin

WordPress integration plugin for Apex27 CRM that provides property search, property details pages, and comprehensive property data management with full page builder support.

## Features

- ✅ **Property Search Page** - Customizable property search with filters
- ✅ **Property Details Pages** - Dynamic property detail pages with contact forms
- ✅ **Custom Post Types** - Properties and agents as native WordPress content
- ✅ **Complete API Data Sync** - All fields from Apex27 CRM automatically imported
- ✅ **Page Builder Ready** - Works with Bricks, Elementor, and other builders
- ✅ **Dynamic Meta Fields** - Use `{_apex27_field_name}` syntax in any content
- ✅ **Taxonomies** - Organized by property type, location, status, and transaction type
- ✅ **Automatic Sync** - Hourly background sync with manual sync options

## Installation

1. Upload the plugin files to `/wp-content/plugins/Apex27/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Settings → Apex27 to configure your API credentials
4. Run initial property sync

## Configuration

### API Settings
- **Website URL**: Your Apex27 website URL (e.g., `https://yoursite.apex27.co.uk`)
- **API Key**: Your Apex27 API key (found in your CRM under WordPress settings)

### Optional Settings
- **Expand Price Range**: Extend price filters to £50M
- **Show Overseas Dropdown**: Display overseas property options
- **Enable Google ReCAPTCHA**: Protect contact forms with reCAPTCHA v3

## Usage

### Page Builder Integration

Use any of the following meta fields in your page builder templates by wrapping them in curly braces:

```
{_apex27_display_price}
{_apex27_bedrooms}
{_apex27_description}
```

## Available Meta Fields

### 🏠 Core Property Information
| Field Name | Meta Key | Description |
|------------|----------|-------------|
| Property ID | `{_apex27_id}` | Apex27 Property ID |
| Reference | `{_apex27_reference}` | Property Reference Number |
| Full Reference | `{_apex27_full_reference}` | Full Property Reference |
| Status | `{_apex27_status}` | Property Status |
| Website Status | `{_apex27_website_status}` | Website Display Status |
| Transaction Type | `{_apex27_transaction_type}` | Transaction Type (sale, rent, etc) |

### 📍 Address Information
| Field Name | Meta Key | Description |
|------------|----------|-------------|
| Address Line 1 | `{_apex27_address1}` | Primary address line |
| Address Line 2 | `{_apex27_address2}` | Secondary address line |
| Address Line 3 | `{_apex27_address3}` | Third address line |
| Address Line 4 | `{_apex27_address4}` | Fourth address line |
| City | `{_apex27_city}` | City |
| County | `{_apex27_county}` | County |
| Postal Code | `{_apex27_postal_code}` | Postal/ZIP code |
| Country | `{_apex27_country}` | Country code |
| Display Address | `{_apex27_display_address}` | Formatted display address |
| Latitude | `{_apex27_latitude}` | GPS latitude |
| Longitude | `{_apex27_longitude}` | GPS longitude |

### 🏘️ Property Details
| Field Name | Meta Key | Description |
|------------|----------|-------------|
| Property Type | `{_apex27_property_type}` | Property type |
| Display Property Type | `{_apex27_display_property_type}` | Formatted property type |
| Property Sub Type | `{_apex27_property_sub_type}` | Property sub-category |
| Bedrooms | `{_apex27_bedrooms}` | Number of bedrooms |
| Bathrooms | `{_apex27_bathrooms}` | Number of bathrooms |
| Reception Rooms | `{_apex27_receptions}` | Number of reception rooms |
| Living Rooms | `{_apex27_living_rooms}` | Number of living rooms |
| Ensuites | `{_apex27_ensuites}` | Number of ensuite bathrooms |
| Toilets | `{_apex27_toilets}` | Number of toilets |
| Kitchens | `{_apex27_kitchens}` | Number of kitchens |
| Dining Rooms | `{_apex27_dining_rooms}` | Number of dining rooms |
| Floors | `{_apex27_floors}` | Number of floors |
| Entrance Floor | `{_apex27_entrance_floor}` | Entrance floor level |
| Floor Number | `{_apex27_floor_number}` | Floor number (for flats) |
| Levels Occupied | `{_apex27_levels_occupied}` | Number of levels occupied |
| Garages | `{_apex27_garages}` | Number of garages |
| Parking Spaces | `{_apex27_parking_spaces}` | Number of parking spaces |

### 💰 Financial Information
| Field Name | Meta Key | Description |
|------------|----------|-------------|
| Price | `{_apex27_price}` | Raw property price |
| **Display Price** | `{_apex27_display_price}` | **Formatted display price** ⭐ |
| Price Currency | `{_apex27_price_currency}` | Currency code |
| Price Prefix | `{_apex27_price_prefix}` | Price prefix (e.g., "From", "Guide") |
| Rent Frequency | `{_apex27_rent_frequency}` | Rental payment frequency |
| Council Tax Amount | `{_apex27_council_tax_amount}` | Council tax amount |
| Council Tax Band | `{_apex27_council_tax_band}` | Council tax band |
| Service Charge | `{_apex27_service_charge_amount}` | Service charge amount |
| Ground Rent | `{_apex27_ground_rent_amount}` | Ground rent amount |
| Gross Yield | `{_apex27_gross_yield}` | Gross yield percentage |
| Sale Fee | `{_apex27_sale_fee}` | Sale fee amount |
| Fee Payable by Buyer | `{_apex27_sale_fee_payable_by_buyer}` | Whether fee paid by buyer |

### 📝 Property Descriptions
| Field Name | Meta Key | Description |
|------------|----------|-------------|
| Summary | `{_apex27_summary}` | Property summary |
| **Description** | `{_apex27_description}` | **Full property description** ⭐ |
| Header | `{_apex27_header}` | Property header |
| Banner | `{_apex27_banner}` | Property banner text |
| Subtitle | `{_apex27_subtitle}` | Property subtitle |
| Area Description | `{_apex27_area_description}` | Area description |
| Print Summary | `{_apex27_print_summary}` | Print-friendly summary |
| Income Description | `{_apex27_income_description}` | Income description |

### 🏠 Property Characteristics
| Field Name | Meta Key | Description |
|------------|----------|-------------|
| Tenure | `{_apex27_tenure}` | Property tenure (freehold/leasehold) |
| Furnished | `{_apex27_furnished}` | Furnished status |
| Condition | `{_apex27_condition}` | Property condition |
| Age Category | `{_apex27_age_category}` | Age category |
| Year Built | `{_apex27_year_built}` | Year property was built |
| Internal Area | `{_apex27_internal_area}` | Internal floor area |
| Internal Area Unit | `{_apex27_internal_area_unit}` | Unit of measurement |
| External Area | `{_apex27_external_area}` | External area size |
| External Area Unit | `{_apex27_external_area_unit}` | Unit of measurement |

### 📸 Media & Images
| Field Name | Meta Key | Description |
|------------|----------|-------------|
| **Thumbnail URL** | `{_apex27_thumbnail_url}` | **Main property image URL** ⭐ |
| Images | `{_apex27_images}` | Property images (array) |
| Gallery | `{_apex27_gallery}` | Property gallery (array) |
| Floorplans | `{_apex27_floorplans}` | Floorplan images (array) |
| Brochures | `{_apex27_brochures}` | Property brochures (array) |
| Virtual Tours | `{_apex27_virtual_tours}` | Virtual tour links (array) |

### ⚡ Energy Performance Certificate (EPC)
| Field Name | Meta Key | Description |
|------------|----------|-------------|
| EPC Exempt | `{_apex27_epc_exempt}` | Whether EPC exempt |
| Current Energy Efficiency | `{_apex27_epc_ee_current}` | Current EPC energy rating |
| Potential Energy Efficiency | `{_apex27_epc_ee_potential}` | Potential EPC energy rating |
| Current Environmental Impact | `{_apex27_epc_ei_current}` | Current environmental impact |
| Potential Environmental Impact | `{_apex27_epc_ei_potential}` | Potential environmental impact |
| EPC Expiry Date | `{_apex27_dts_epc_expiry}` | EPC certificate expiry |
| EPC Reference | `{_apex27_epc_reference}` | EPC reference number |

### 🎯 Property Features (Arrays)
| Field Name | Meta Key | Description |
|------------|----------|-------------|
| Bullet Points | `{_apex27_bullets}` | Property bullet points |
| Accessibility Features | `{_apex27_accessibility_features}` | Accessibility features |
| Heating Features | `{_apex27_heating_features}` | Heating system details |
| Parking Features | `{_apex27_parking_features}` | Parking arrangements |
| Outside Space Features | `{_apex27_outside_space_features}` | Garden/outdoor features |
| Electricity Supply | `{_apex27_electricity_supply_features}` | Electricity supply info |
| Water Supply | `{_apex27_water_supply_features}` | Water supply details |
| Sewerage Supply | `{_apex27_sewerage_supply_features}` | Sewerage system info |
| Broadband Supply | `{_apex27_broadband_supply_features}` | Internet connectivity |
| Custom Features | `{_apex27_custom_features}` | Additional custom features |

### 🏢 Relationships & IDs
| Field Name | Meta Key | Description |
|------------|----------|-------------|
| Branch ID | `{_apex27_branch_id}` | Associated branch ID |
| User ID | `{_apex27_user_id}` | Assigned agent ID |
| Transaction Type Route | `{_apex27_transaction_type_route}` | Transaction routing info |
| Main Search Region ID | `{_apex27_main_search_region_id}` | Search region ID |

### 📅 Important Dates
| Field Name | Meta Key | Description |
|------------|----------|-------------|
| Date Available From | `{_apex27_date_available_from}` | When property becomes available |
| Time Created | `{_apex27_time_created}` | When listing was created |
| Time Updated | `{_apex27_time_updated}` | Last update timestamp |
| Time Marketed | `{_apex27_time_marketed}` | When marketing started |
| Date Created | `{_apex27_dts_created}` | Creation date |
| Date Updated | `{_apex27_dts_updated}` | Last update date |

### ⭐ Special Features
| Field Name | Meta Key | Description |
|------------|----------|-------------|
| Is Featured | `{_apex27_is_featured}` | Whether property is featured |
| Sale Progression | `{_apex27_sale_progression}` | Sale progression status |
| Image Overlay Text | `{_apex27_image_overlay_text}` | Text overlay for images |
| Total Income Text | `{_apex27_total_income_text}` | Total income information |

### 📊 Additional Data Objects
| Field Name | Meta Key | Description |
|------------|----------|-------------|
| Branch Info | `{_apex27_branch}` | Branch details (object) |
| Geolocation | `{_apex27_geolocation}` | Geographic data (object) |
| Point of View | `{_apex27_pov}` | POV data (object) |
| Energy Efficiency | `{_apex27_energy_efficiency}` | Energy data (object) |
| Environmental Impact | `{_apex27_environmental_impact}` | Environmental data (object) |

### 📝 Custom Descriptions
| Field Name | Meta Key | Description |
|------------|----------|-------------|
| Custom Description 1 | `{_apex27_custom_description1}` | Custom description field 1 |
| Custom Description 2 | `{_apex27_custom_description2}` | Custom description field 2 |
| Custom Description 3 | `{_apex27_custom_description3}` | Custom description field 3 |
| Custom Description 4 | `{_apex27_custom_description4}` | Custom description field 4 |
| Custom Description 5 | `{_apex27_custom_description5}` | Custom description field 5 |
| Custom Description 6 | `{_apex27_custom_description6}` | Custom description field 6 |

## Page Builder Examples

### Bricks Builder
```html
<!-- Display property price -->
<h2>{_apex27_display_price}</h2>

<!-- Property details -->
<p>{_apex27_bedrooms} bed, {_apex27_bathrooms} bath property in {_apex27_city}</p>

<!-- Property description -->
<div>{_apex27_description}</div>

<!-- Property image -->
<img src="{_apex27_thumbnail_url}" alt="{_apex27_display_address}">
```

### Elementor
Use the same meta keys in Elementor's Dynamic Tags or Text Editor widgets.

### Other Page Builders
The `{_apex27_field_name}` syntax works in any page builder that processes WordPress content.

## Custom Post Types

### Properties (`apex27_property`)
- **URL Slug**: `/properties/`
- **Admin Menu**: Properties
- **Supports**: Title, Editor, Thumbnail, Excerpt, Custom Fields
- **REST API**: Enabled (`/wp-json/wp/v2/properties`)

### Agents (`apex27_agent`)
- **URL Slug**: `/agents/`
- **Admin Menu**: Agents  
- **Supports**: Title, Editor, Thumbnail, Excerpt, Custom Fields
- **REST API**: Enabled (`/wp-json/wp/v2/agents`)

## Taxonomies

### Property Type (`property_type`)
Categories like House, Flat, Bungalow, etc.

### Property Location (`property_location`)
Geographic locations and areas

### Property Status (`property_status`)
Available, Under Offer, Sold, etc.

### Property Transaction (`property_transaction`)
- **For Sale** - Sale properties
- **To Let** - Rental properties  
- **Land For Sale** - Land sales
- **Commercial Sale** - Commercial sales
- **Commercial Rent** - Commercial rentals

## API Sync

### Manual Sync
- Go to Settings → Apex27
- Click "Sync Properties Now" or "Sync Agents Now"
- View progress and results in real-time

### Automatic Sync
- Runs hourly via WordPress cron
- Syncs both properties and agents
- Can be disabled in settings

### Sync Status
Check sync status in WordPress admin:
- **Properties**: Shows count of imported properties
- **Agents**: Shows count of imported agents
- **Last Sync**: Timestamp of last successful sync

## Data Mapping

All API fields from Apex27 are automatically captured and stored as WordPress meta fields with the `_apex27_` prefix. This ensures:

- **Complete Data Preservation** - No API data is lost
- **Page Builder Compatibility** - All fields accessible in any builder
- **Future-Proof** - New API fields automatically captured
- **Easy Access** - Consistent naming convention

## URLs & Templates

### Property Search
- **URL**: `/property-search/`
- **Template Override**: Create `apex27/property-search.php` in your theme
- **Features**: Filters, sorting, pagination, map integration

### Property Details  
- **URL Pattern**: `/property-details/{transaction-type}/{address-slug}/{property-id}/`
- **Example**: `/property-details/sales/123-main-street/12345/`
- **Template Override**: Create `apex27/property-details.php` in your theme
- **Features**: Full property details, image gallery, contact form

## Troubleshooting

### Fields Not Displaying
1. Check if property sync has been run
2. Verify API credentials in Settings → Apex27
3. Check the "Raw API Data" meta box in property edit screen
4. Ensure correct syntax: `{_apex27_field_name}` with curly braces

### Sync Issues
1. Verify API credentials
2. Check error logs for API connection issues
3. Try manual sync first
4. Contact Apex27 support if API issues persist

### Performance
- Plugin caches API responses
- Uses efficient WordPress hooks
- Minimal frontend impact
- Background sync doesn't affect site speed

## Developer Information

### Hooks & Filters
```php
// Modify search results before display
add_filter('apex27_search_results', 'my_custom_search_filter');

// Modify property details before display  
add_filter('apex27_property_details', 'my_custom_property_filter');

// Add custom meta field processing
add_filter('the_content', 'my_custom_meta_replacement');
```

### Direct Meta Access
```php
// Get property meta data
$price = get_post_meta($post_id, '_apex27_display_price', true);
$bedrooms = get_post_meta($post_id, '_apex27_bedrooms', true);
$images = get_post_meta($post_id, '_apex27_images', true);
```

### REST API Access
```javascript
// Get properties via REST API
fetch('/wp-json/wp/v2/properties')
  .then(response => response.json())
  .then(properties => {
    properties.forEach(property => {
      console.log(property.display_price);
      console.log(property.bedrooms);
    });
  });
```

## Support

- **Plugin Issues**: Check WordPress error logs
- **API Issues**: Contact Apex27 support
- **Field Mapping**: Use admin interface to view all available fields
- **Documentation**: This README contains all available parameters

## Version Information

- **WordPress**: Requires 5.0+
- **PHP**: Requires 7.4+
- **Apex27 API**: Compatible with current API version
- **Page Builders**: Works with Bricks, Elementor, Beaver Builder, and others

---

**⭐ Most Commonly Used Fields:**
- `{_apex27_display_price}` - Formatted price
- `{_apex27_bedrooms}` - Number of bedrooms  
- `{_apex27_bathrooms}` - Number of bathrooms
- `{_apex27_description}` - Property description
- `{_apex27_thumbnail_url}` - Main image URL
- `{_apex27_display_address}` - Property address