<?php

/**
 * A cache manager for Google API PHP Client. This creates cache entries
 * in the database using a simple data model.
 */
class GoogleAPICacheHandler extends Google_Cache_Abstract
{
	public function __construct(Google_Client $client) {
	}

	/**
	 * Retrieves the data for the given key, or false if they
	 * key is unknown or expired
	 *
	 * @param String $key The key who's data to retrieve
	 * @param boolean|int $expiration Expiration time in seconds
	 *
	 */
	public function get($key, $expiration = false) {
		$entry = $this->getEntry($key);
		if (!$entry) {
			return null;
		}
		return $entry->Value;
	}

	/**
	 * Store the key => $value set. The $value is serialized
	 * by this function so can be of any type.
	 *
	 * @param string $key Key of the data
	 * @param string $value data
	 */

	public function set($key, $value) {
		// if it's an existing key, we want to overwrite it.
		$existing = $this->getEntry($key);

		if (!$existing) {
			// key doesn't exist, so create it.
			$existing = GoogleAPICacheEntry::create();
			$existing->Key = $key;
		}

		$existing->Value = $value;
		$existing->write();
	}

	/**
	 * Removes the key/data pair for the given $key
	 *
	 * @param String $key
	 */
	public function delete($key) {
		$existing = $this->getEntry($key);
		if ($existing) {
			$existing->delete();
		}
	}

	/**
	 * Return the GoogleAPICacheEntry for the specified Key. get() is part of the cache
	 * API, and returns the value stored in the entry.
	 */
	protected function getEntry($key) {
		return GoogleAPICacheEntry::get()->filter('Key', $key)->first();
	}
}
