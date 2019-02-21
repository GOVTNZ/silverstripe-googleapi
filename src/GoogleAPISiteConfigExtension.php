<?php

namespace GovtNZ\SilverStripe\GoogleApi;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;

/**
 * Extend site config with the properties that can be used while interacting
 * with Google API services. The values here are consumed by
 * `GoogleAPI::get_all_config(), which merges it with configuration in the
 * config system, so this is not the only source of configuration for Google
 * API.
 */
class GoogleAPISiteConfigExtension extends DataExtension
{
	private static $db = array(
		'GoogleAPIExternalProxy' => 'Varchar(255)',
		'GoogleAPIClientID' => 'Varchar(255)',
		'GoogleAPIApplicationName' => 'Varchar(32)',
		'GoogleAPIServiceAccount' => 'Varchar(255)',
		'GoogleAPIPrivateKeyFile' => 'Varchar(255)',
		'GoogleAPIScopes' => 'Text',
		'GoogleAPIProfileID' => 'Varchar(32)',
	);

	public function updateCMSFields(FieldList $fields)
	{
		// Get values from the config system, to act as placeholders for site config properties.
		$config = Config::inst();

		$editable = $config->get('GoogleAPI', 'config_in_cms');
		if (!$editable) {
			// No extra config fields if this is disabled.
			return $fields;
		}

		$fields->addFieldToTab('Root.GoogleAPI', $fld = new TextField(
			'GoogleAPIExternalProxy',
			'Proxy to call external apis'
		));
		$fld->setAttribute('placeholder', $config->get('GoogleAPI', 'external_proxy'));

		$fields->addFieldToTab('Root.GoogleAPI', $fld = new TextField(
			'GoogleAPIClientID',
			'Google API Client ID'
		));
		$fld->setAttribute('placeholder', $config->get('GoogleAPI', 'client_id'));

		$fields->addFieldToTab('Root.GoogleAPI', $fld = new TextField(
			'GoogleAPIProfileID',
			'Google API Profile ID (not Property ID)'
		));
		$fld->setAttribute('placeholder', $config->get('GoogleAPI', 'profile_id'));

		$fields->addFieldToTab('Root.GoogleAPI', $fld = new TextField(
			'GoogleAPIApplicationName',
			'Google API Application Name'
		));
		$fld->setAttribute('placeholder', $config->get('GoogleAPI', 'application_name'));

		$fields->addFieldToTab('Root.GoogleAPI', $fld = new TextField(
			'GoogleAPIServiceAccount',
			'Google API Service Account'
		));
		$fld->setAttribute('placeholder', $config->get('GoogleAPI', 'service_account'));

		$fields->addFieldToTab('Root.GoogleAPI', $fld = new TextField(
			'GoogleAPIPrivateKeyFile',
			'Google API Path to Private Key file (relative to site root)'
		));
		$fld->setAttribute('placeholder', $config->get('GoogleAPI', 'private_key_file'));

		$fields->addFieldToTab('Root.GoogleAPI', $fld = new TextField(
			'GoogleAPIScopes',
			'Google API Scopes'
		));
		$fld->setAttribute('placeholder', $config->get('GoogleAPI', 'scopes'));

		return $fields;
	}
}
