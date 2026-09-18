[![Latest Stable Version](https://poser.pugx.org/neos/googleanalytics/v/stable)](https://packagist.org/packages/neos/googleanalytics)
[![Total Downloads](https://poser.pugx.org/neos/googleanalytics/downloads)](https://packagist.org/packages/neos/googleanalytics)
[![License](https://poser.pugx.org/neos/googleanalytics/license)](LICENSE)

# Neos.GoogleAnalytics

This package allows adding Google Analytics and the Google Tag Manager to a Neos CMS website.

## Installation

1. Run the following command f.e. in your site package:
   ```bash
   composer require --no-update neos/googleanalytics
   ```

2. Update your dependencies by running the following command in your project root folder:
   ```bash
   composer update
   ```

## Configuration

### Configure tracking

To actually track visits with Google Analytics, some JavaScript with the tracking ID has to be
included in the markup. You can do this manually in your template, but the easier way is
to set up tracking for each site in the Analytics integration.

```yaml
Neos:
  GoogleAnalytics:
    sites:
      ## All site specific settings are indexed by site node name
      neosSiteName:
        analytics:
          id: 'G-XXXXXXXXXX'
```

Instead of using the Google Analytics tracking code, you can integrate the Google Tag Manager the same way:

```yaml
Neos:
  GoogleAnalytics:
    sites:
      neosSiteName:
        tagManager:
          id: 'GTM-XXXXX'
```

> **Note:** If you configure both, a container and an Analytics ID, only the Tag Manager is included.

It is also possible to define default values for all sites. These will be merged with any site specific settings.

```yaml
Neos:
  GoogleAnalytics:
    default:
      analytics:
        id: 'G-XXXXXXXXXX'
```

### Disable tracking

You can disable tracking for a site by either setting the `id` to `false` (this is the default value), or leaving it blank.

```yaml
Neos:
  GoogleAnalytics:
    sites:
      neosSiteName:
        tagManager:
          id: false
```

### Additional parameters

If you are using the Google Analytics tracking code, you can also add additional parameters e.g. `anonymize_ip` set to `true`.
These parameters are added automatically as JSON to the gtag. The default setting is no parameters.

```yaml
Neos:
  GoogleAnalytics:
    sites:
      neosSiteName:
        analytics:
          id: 'G-XXXXXXXXXX'
          parameters:
            anonymize_ip: true
```

### Rendering the tracking code

By default the tracking code is added to the `Neos.Neos:Page` prototype automatically. If you want to place it
yourself, disable the automatic inclusion and use the Fusion prototypes directly:

```yaml
Neos:
  GoogleAnalytics:
    sites:
      neosSiteName:
        addToPagePrototype: false
```

The following Fusion prototypes are available:

* `Neos.GoogleAnalytics:TrackingCode.GA` renders the gtag.js snippet for the configured Analytics ID.
* `Neos.GoogleAnalytics:TrackingCode.GTM.Script` renders the Google Tag Manager script snippet.
* `Neos.GoogleAnalytics:TrackingCode.GTM.NoScript` renders the Google Tag Manager noscript fallback.
* `Neos.GoogleAnalytics:TrackingCode.GTM.DataLayer` pushes the given `data` onto `window.dataLayer`.

The tracking code is never rendered while working in a non-live workspace, e.g. in the Neos backend.

## Upgrade instructions

### 3.x -> 4.0.0

The statistics display inside the Neos backend (inspector "Stats" tab and the "Analytics" administration module)
has been removed, because the underlying Google Analytics Reporting API (Universal Analytics) is no longer available.
The package now only provides the tracking code integration.

If you referenced any of these in your own configuration, remove those references.

### 2.x -> 3.0.0

Configuration for the tracking code has been changed:

```yaml
Neos:
  GoogleAnalytics:
    sites:
      neosSiteName:
        analytics:
          id: 'UA-XXXXX-YY'
```

#### `enableTracking` setting

Tracking code is now only included if you provide either a container or an Analytics ID.
The `enableTracking` setting has therefore been removed.

## License

See [LICENSE](./LICENSE.txt)
