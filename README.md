# Introduction

This module provides simplified access to Google APIs. It uses the
Google API PHP Client from
[github](https://github.com/google/google-api-php-client), in beta at time of writing.

# Installation

## Using composer

To include the module using composer, add the following to your requirements in composer.json:

    "require": {
        "govtnz/googleapi": "dev-master",
    }

## Manually

You can clone or download the module from [googleapi on github](https://github.com/govtnz/silverstripe-googleapi).

# Configuration

The module supports two levels of configuration, via the SilverStripe configuration system, and also via a SiteConfig extension. The following properties can be defined in your site's config.yml:

    GoogleAPI:
      client_id: 'xyz.apps.googleusercontent.com'
      profile_id: 'ga:xxxxxxxx'
      application_name: 'app name'
      service_account: 'xxxxxxxxx-yyyyyy@developer.gserviceaccount.com'
      private_key_file: 'mysite/private/x.p12'
      scopes: 'https://www.googleapis.com/auth/analytics.readonly'

Site config has corresponding properties under the Google API tab that if specified override these properties. The site config properties are prefixed GoogleAPI so as not to conflict with other site config properties that may be defined by extensions on other modules.

# Usage

Access to Google services is via the Google_Client class that is defined by the Google API PHP Client library. It handles authentication and configuration. You can create a Google_Client instance yourself, but an easier way is as follows:

    $client = GoogleAPI::get_client()

This will return a new Google_Client instance, but will also apply site configuration to it, and authenticate for you.

From this point, you can use the Google API classes directly to access the services you need.

Additionally, you can get access to the GoogleAPI configuration, using

    $value = GoogleAPI::get_config('application_name');

# Examples

## Fetch top 10 popular pages in the last 30 days

The following example gets popular pages. It uses GoogleAPI::get_config to get the Google Analytics profile ID, which can either be specified in the config system or in site config.

    $client = GoogleAPI::get_client();
    try {
        // create a service instance
        $service = new Google_Service_Analytics($client);
        // Make the request
        $result = $service->data_ga->get(
            GoogleAPI::get_config('profile_id'),
            date("Y-m-d", strtotime("-1 month")),
            date('Y-m-d', time()),
            "ga:uniquePageViews",
            array(
                'dimensions' => 'ga:pagePath',
                'sort' => '-ga:uniquePageViews',
                'max-results' => 10
            )
        );
    }
    catch (Exception $e) {
        ...
    }

# Design notes

## Intended scope of module

The module is designed mostly to integrate the Google API PHP client with SilverStripe, and provide the behaviour that is common across services. There are many auxiliary functions that are service-specific that could be added, but these are probably better done as separate modules that have a dependency on this module.

## Caching 

Google's API client has built-in support for caching via an interface. It provides file system and memcached implementors, which you can use. By default,
however, this module is configured to use it's own ORM-based cache system which implements the caching interface.

This module's intent with regards to caching is to utilise Google's caching system if that provides sufficient performance.

## To do

The initial use case was to replace old client API code for Google Analytics. As such, a GA-specific configuration property was added for profile for convenience. However, there are many other services that may require configuration properties, and there is no nice way to add those into config at present. So that needs to be figured out.
