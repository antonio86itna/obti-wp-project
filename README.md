# OBTI WordPress Project

This repository contains the `wp-content` directory for the OpenBusTour Ischia site. It includes a custom theme, custom plugins and an Elementor page template.

## Structure

```
wp-content/
  themes/obti/
  plugins/obti-booking/
  plugins/obti-elementor-widgets/
  elementor/home.json
```

## Building the Theme
The theme relies on Tailwind CSS. To compile the stylesheet run:

```
cd wp-content/themes/obti
npm install
npm run build:css
```

## Theme Customizer Settings

The theme adds a **Theme Settings** section under *Appearance → Customize* with the following options:

- **Facebook URL** (`social_facebook`)
- **Instagram URL** (`social_instagram`)
- **Twitter URL** (`social_twitter`)
- **Mapbox Token** (`mapbox_token`) – used by the Schedule & Map widget
- **Chatbot API Key** (`chatbot_api_key`) – used by the Chatbot widget

## Elementor Home Template

A prebuilt Home page layout using custom widgets is stored in [`wp-content/elementor/home.json`](wp-content/elementor/home.json).

### Import instructions
1. Ensure the **OBTI Elementor Widgets** plugin is active so that the custom widgets are available.
2. Navigate to **Elementor → Templates → Import Templates** in the WordPress admin.
3. Upload the `wp-content/elementor/home.json` file from this repository and click **Import**.
4. Create or edit a page and insert the imported template to use the layout. The template sets the container width to **1280px**.

The template includes the following widgets in order: `obti-hero`, `obti-highlights`, `obti-schedule-map`, `obti-faq`, and `obti-booking`.

## Booking REST API

The `obti-booking` plugin exposes authenticated endpoints under `/wp-json/obti/v1`.

### Authentication

Requests must include either:

- `Authorization: Bearer <API_KEY>` header where the key is configured in the plugin settings.
- A logged-in WordPress user via OAuth or another supported auth method.

### List bookings

```
GET /wp-json/obti/v1/bookings?date=2024-01-01&status=obti-confirmed
```

Returns an array of bookings with fields `id`, `customer`, `date`, `time`, `qty`, `total`, `agency_fee` and `transfer_status`.

### Mark transfer

```
PATCH /wp-json/obti/v1/bookings/{id}/transfer
```

Marks the Totaliweb agency fee as transferred for the given booking. Optionally send `status=no` to reset.
