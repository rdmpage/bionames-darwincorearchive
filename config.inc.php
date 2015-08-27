<?php

// $Id: //

/**
 * @file config.php
 *
 * Global configuration variables (may be added to by other modules).
 *
 */

global $config;

// Date timezone
date_default_timezone_set('UTC');


// Proxy settings for connecting to the web--------------------------------------------------------- 
// Set these if you access the web through a proxy server. 
$config['proxy_name'] 	= '';
$config['proxy_port'] 	= '';

//$config['proxy_name'] 	= 'wwwcache.gla.ac.uk';
//$config['proxy_port'] 	= '8080';

// CouchDB------------------------------------------------------------------------------------------

// local CouchDB
$config['couchdb_options'] = array(
		'database' => 'bionames_local',
		'host' => 'localhost',
		'port' => 5984
		);



// Cloudant
$config['couchdb_options'] = array(
		'database' => 'bionames',
		'host' => 'rdmpage:peacrab@rdmpage.cloudant.com',
		'port' => 5984
		);
		
// BioNames
$config['couchdb_options'] = array(
		'database' => 'bionames',
		'host' => 'rdmpage:peacrab@direct.bionames.org',
		'port' => 5984
		);
		
// PhyLoTa
$config['phylota_couchdb_options'] = array(
		'database' => 'phylota_184',
		'host' => 'rdmpage:peacrab@direct.bionames.org',
		'port' => 5984
		);


// HTTP proxy
if ($config['proxy_name'] != '')
{
	if ($config['couchdb_options']['host'] != 'localhost')
	{
		$config['couchdb_options']['proxy'] = $config['proxy_name'] . ':' . $config['proxy_port'];
	}
}



	
?>