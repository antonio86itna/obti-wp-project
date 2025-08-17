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

## Installation

1. Copy the `obti` theme along with the `obti-booking` and `obti-elementor-widgets` plugins into your site's `wp-content` directory, keeping the folder structure above.
2. From `wp-content/themes/obti` install dependencies and build the CSS assets:

   ```
   cd wp-content/themes/obti
   npm install
   npm run build:css
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

A prebuilt Home page layout using custom widgets is stored in [`wp-content/elementor/home.json`](wp-content/elementor/home.json). Re-import this template if you pull updates to ensure the latest widgets are applied.

### Import instructions
1. Activate the **OBTI Elementor Widgets** plugin so the custom widgets are available.
2. In the WordPress admin go to **Elementor → Templates → Import Templates**.
3. Choose the `wp-content/elementor/home.json` file from this repository and click **Import**.
4. Create a new page (or edit an existing one) and click **Edit with Elementor**.
5. Insert the imported **Home** template. The layout sets the container width to **1280px**.
6. Optionally set this page as the site's front page under **Settings → Reading**.

The template includes the following widgets in order: `obti-hero`, `obti-highlights`, `obti-schedule-map`, `obti-faq`, and `obti-booking`.

## Stripe Test

Enter your Stripe publishable and secret keys in **OBTI Booking → Settings** within the WordPress admin. When testing, enable test mode and use Stripe's test card numbers such as `4242 4242 4242 4242`.

## User Roles & Dashboard

The booking plugin registers an `obti_customer` role for customers created through the front-end form. After importing the [`wp-content/elementor/home.json`](wp-content/elementor/home.json) template, create a dashboard page using Elementor and assign the imported layout. Logged-in `obti_customer` users can visit this page to view their bookings.

## Testing Workflow

1. Simulate a booking on the front end and confirm that the selected date and time show available seats.
2. Complete checkout using a Stripe test card and verify that the payment succeeds.
3. After payment, log in as the created `obti_customer` and ensure the dashboard page is accessible.

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
