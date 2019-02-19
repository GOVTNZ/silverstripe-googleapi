<?php

use SilverStripe\ORM\DataObject;

/**
 * A simple data object for representing cache values for google API.
 */
class GoogleAPICacheEntry extends DataObject {
	private static $db = array(
		"Key" => "Text",
		"Value" => "Text"
	);

}
