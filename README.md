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

## Elementor Home Template

A prebuilt Home page layout using custom widgets is stored in [`wp-content/elementor/home.json`](wp-content/elementor/home.json).

### Import instructions
1. Ensure the **OBTI Elementor Widgets** plugin is active so that the custom widgets are available.
2. Navigate to **Elementor → Templates → Import Templates** in the WordPress admin.
3. Upload the `wp-content/elementor/home.json` file from this repository and click **Import**.
4. Create or edit a page and insert the imported template to use the layout.

The template includes the following widgets in order: `obti-hero`, `obti-highlights`, `obti-schedule-map`, `obti-faq`, and `obti-booking`.
