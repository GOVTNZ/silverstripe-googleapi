<?php

namespace GovtNZ\SilverStripe\GoogleApi;

/**
 * Simple class to provide convenient access to the Google API PHP client, as
 * well as configuration to be supplied to GoogleAPI.
 */
class GoogleAPI {
	// The following properties are used by the configuration system. They should not be used directly
	// within this class; see get_all_config instead.

	/**
	 * @config
	 */
	private static $external_proxy = '';

	/**
	 * @config
	 */
	private static $client_id;

	/**
	 * @config
	 */
	private static $application_name;

	/**
	 * @config
	 */
	private static $private_key_file;

	/**
	 * @config
	 */
	private static $service_account;

	/**
	 * @config
	 */
	private static $scopes;

	/**
	 * @config
	 */
	private static $profile_id;

	/**
	 * @config
	 */
	private static $config_in_cms = true;

	/**
	 * Returns an instance of Google_Client. Automatically handles set up of the
	 * client, including:
	 * - authentication using configuration in SiteConfig
	 * - caching support via an implementation of the caching interface defined
	 *   by the Google API.
	 */
	public static function get_client() {
		$client = new Google_Client();

		// If we are going through a proxy, set that up
		$proxy = static::get_config('external_proxy');
		if ($proxy) {
			$io = $client->getIo();

			$parts = static::decode_address($proxy, 'http', '80');

			$io->setOptions(array(
				CURLOPT_PROXY => $parts['Address'],
				CURLOPT_PROXYPORT => $parts['Port']
			));
		}

		$client->setClientId(static::get_config('client_id'));
		$client->setApplicationName(static::get_config('application_name'));

		// absolute if starts with '/' otherwise relative to site root
		$keyPathName = static::get_config('private_key_file');
		if (substr($keyPathName, 0, 1) !== '/') {
			$keyPathName = Director::baseFolder() . "/" . $keyPathName;
		}

		$client->setAssertionCredentials(new Google_Auth_AssertionCredentials(
				static::get_config('service_account'),
				explode(',', static::get_config('scopes')),
				file_get_contents($keyPathName)
			)
		);

		$client->setAccessType('offline_access');

		// Set up custom database cache handler.
        $client->setCache(new GoogleAPICacheHandler($client));

		return $client;
	}

	/**
	 * Parse an address and return array of Protocol (scheme), Address, Port substituting defaults where not supplied.
	 *
	 * @param string $address full address
	 * @param string $defaultProtocol default to http if not in $address
	 * @param string $defaultPort default to 80 if not in $address
	 * @return array of Protocol, Address, Port
	 */
	protected static function decode_address($address, $defaultProtocol = 'http', $defaultPort = '80') {
		$parts = parse_url($address);
		return array(
			'Protocol' => empty($parts['scheme']) ? $defaultProtocol : $parts['scheme'],
			'Address' => $parts['host'],
			'Port' => empty($parts['port']) ? $defaultPort : $parts['port']
		);
	}

	/**
	 * Helper function to get a configuration property. If the property is not defined in the configuration,
	 * null is returned. Configuration is obtained from get_all_config.
	 */
	public static function get_config($prop) {
		$conf = static::get_all_config();
		if (!isset($conf[$prop])) {
			return null;
		}
		return $conf[$prop];
	}

	private static $_config_cache;

	/**
	 * Return a map of configuration properties. This is obtained by merging properties from the config
	 * system with properties from SiteConfig. Where the same property is defined and populated in both
	 * places, SiteConfig takes precedence.
	 */
	public static function get_all_config() {
		if (!self::$_config_cache) {
			$conf = array();

			$editable = Config::inst()->get('GoogleAPI', 'config_in_cms');

			$siteConfig = SiteConfig::current_site_config();
			// $googleAPIConfig = Config::inst()->get('GoogleAPI');
			// $googleAPIConfig = self::config();

			// Default the result properties from the config system.
			foreach (array(
				'external_proxy',
				'client_id',
				'application_name',
				'private_key_file',
				'service_account',
				'profile_id',
				'scopes') as $prop) {
				$conf[$prop] = Config::inst()->get('GoogleAPI', $prop);
			}

			// For any site config properties with values, these should override the
			// config system, but only if editing config in the CMS is enabled.
			if ($editable) {
				foreach (array(
					'GoogleAPIExternalProxy' => 'external_proxy',
					'GoogleAPIClientID' => 'client_id',
					'GoogleAPIApplicationName' => 'application_name',
					'GoogleAPIServiceAccount' => 'service_account',
					'GoogleAPIPrivateKeyFile' => 'private_key_file',
					'GoogleAPIScopes' => 'scopes',
					'GoogleAPIProfileID' => 'profile_id'
					) as $src => $dest) {
					if (!empty($siteConfig->$src)) {
						$conf[$dest] = $siteConfig->$src;
					}
				}
			}

			self::$_config_cache = $conf;
		}

		return self::$_config_cache;
	}
}
