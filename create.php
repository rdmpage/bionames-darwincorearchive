<?php

require_once (dirname(__FILE__) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/reference.php');



//----------------------------------------------------------------------------------------
// taxa
$taxa_filename = 'taxa.tsv';
$file_handle = fopen($taxa_filename, "w");
fclose($file_handle);

//--------------------------------------------------------------------------------------------------
$taxon_fields = array(
	'taxonID' 				=> 'http://rs.tdwg.org/dwc/terms/taxonID',
	'references' 			=> 'http://purl.org/dc/terms/references',
	'scientificNameID' 		=> 'http://rs.tdwg.org/dwc/terms/scientificNameID',
	'scientificName' 		=> 'http://rs.tdwg.org/dwc/terms/scientificName',
	'scientificNameAuthorship' => 'http://rs.tdwg.org/dwc/terms/scientificNameAuthorship',
	'TaxonRank' 			=> 'http://rs.tdwg.org/dwc/terms/taxonRank',

	'uninomial' 			=> 'http://rs.tdwg.org/ontology/voc/TaxonName#uninomial',
	'genusPart' 			=> 'http://rs.tdwg.org/ontology/voc/TaxonName#genusPart',
	'infragenericEpithet' 	=> 'http://rs.tdwg.org/ontology/voc/TaxonName#infragenericEpithet',
	'specificEpithet' 		=> 'http://rs.tdwg.org/ontology/voc/TaxonName#specificEpithet',
	'infraspecificEpithet' 	=> 'http://rs.tdwg.org/ontology/voc/TaxonName#infraspecificEpithet',

	'nomenclaturalCode' 	=> 'http://rs.tdwg.org/dwc/terms/nomenclaturalCode',
	'higherClassification' 	=> 'http://rs.tdwg.org/dwc/terms/higherClassification',

	'namePublishedInID' 	=> 'http://rs.tdwg.org/dwc/terms/namePublishedInID'
);

file_put_contents($taxa_filename, join("\t", array_keys($taxon_fields)) . "\n");

//----------------------------------------------------------------------------------------
// references
$reference_filename = 'references.tsv';
$file_handle = fopen($reference_filename, "w");
fclose($file_handle);

// header
$header = array_values($reference_map);
array_unshift($header, 'taxonID');

file_put_contents($reference_filename, join("\t", $header) . "\n");

//----------------------------------------------------------------------------------------
// Generate metadata

$metadata = new DomDocument('1.0', 'UTF-8');
$metadata->preserveWhiteSpace = false;
$metadata->formatOutput = true;


$archive = $metadata->appendChild($metadata->createElement('archive'));
$archive->setAttribute('xmlns', 			 'http://rs.tdwg.org/dwc/text/');
$archive->setAttribute('xmlns:xsi', 		 'http://www.w3.org/2001/XMLSchema-instance');
$archive->setAttribute('xsi:schemaLocation', 'http://rs.tdwg.org/dwc/text/  http://rs.tdwg.org/dwc/text/tdwg_dwc_text.xsd');

// taxa
$core = $archive->appendChild($metadata->createElement('core'));

$core->setAttribute('encoding', 			'UTF-8');
$core->setAttribute('fieldsTerminatedBy', 	'\t');
$core->setAttribute('linesTerminatedBy', 	'\n');
$core->setAttribute('ignoreHeaderLines',  	'1');
$core->setAttribute('rowType',  			'http://rs.tdwg.org/dwc/terms/Taxon');

$files = $core->appendChild($metadata->createElement('files'));
$location = $files->appendChild($metadata->createElement('location'));
$location->appendChild($metadata->createTextNode($taxa_filename));

$id = $core->appendChild($metadata->createElement('id'));
$id->setAttribute('index', 	'0');

$index = 0;
foreach ($taxon_fields as $key => $namespace)
{
	$field = $core->appendChild($metadata->createElement('field'));
	$field->setAttribute('index', 	$index++);
	$field->setAttribute('term', 	$namespace);
}

// references

$extension = $archive->appendChild($metadata->createElement('extension'));

$extension->setAttribute('encoding', 			'UTF-8');
$extension->setAttribute('fieldsTerminatedBy', 	'\t');
$extension->setAttribute('linesTerminatedBy', 	'\n');
$extension->setAttribute('ignoreHeaderLines',  	'1');
$extension->setAttribute('rowType',  			'http://eol.org/schema/reference/Reference');

$files = $extension->appendChild($metadata->createElement('files'));
$location = $files->appendChild($metadata->createElement('location'));
$location->appendChild($metadata->createTextNode($reference_filename));

$coreid = $extension->appendChild($metadata->createElement('coreid'));
$coreid->setAttribute('index', 	'0');

$index = 0;
$field = $extension->appendChild($metadata->createElement('field'));
$field->setAttribute('index', 	$index++);
$field->setAttribute('term', 	'http://rs.tdwg.org/dwc/terms/taxonID');

foreach ($reference_fields as $key => $namespace)
{
	$field = $extension->appendChild($metadata->createElement('field'));
	$field->setAttribute('index', 	$index++);
	$field->setAttribute('term', 	$namespace);
}


file_put_contents('meta.xml', $metadata->saveXML());


//----------------------------------------------------------------------------------------

$done = false;

$rows_per_page = 10;
$skip = 0;

while (!$done)
{
	$url = '_design/darwincorearchive/_view/taxa';
	$url .= '?limit=' . $rows_per_page . '&skip=' . $skip;
	$url .= '&stale=ok';
	
	$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);
	
	//echo $resp;
	
	$obj = json_decode($resp);
	
	//print_r($obj);
	
	//exit();
	
	foreach($obj->rows as $row)
	{
		echo ".";
		
		$core_id = $row->key;
		$core_id = str_replace('urn:lsid:organismnames.com:name:', 'http://bionames.org/names/cluster/', $core_id);
		
		// adjust array
		$values = explode("\t", $row->value);
		
		//Add LSID
		array_unshift($values, $row->key);
		
		// remove reference
		//array_pop($values);
		
		$n = count($values);
		$namePublishedInID = $values[$n-1];
		if ($namePublishedInID != '')
		{
			$namePublishedInID = 'http://bionames.org/references/' . $namePublishedInID;
			$values[$n-1] = $namePublishedInID;
		}
		
				
		
		file_put_contents($taxa_filename, $core_id . "\t" . $core_id . "\t" . join("\t", $values) . "\n", FILE_APPEND);
		//echo $row->key . "\t" . $row->value . "\n";
		
		// reference
		
		$values = explode("\t", $row->value);
		
		$reference_id = array_pop($values);
		if ($reference_id != '')
		{			
			$url = '_design/darwincorearchive/_view/taxa_references?key=' . urlencode('"' . $row->id . '"');
			$url .= '&include_docs=true';
			$url .= '&stale=ok';
			
			$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);
			
			//echo $resp;
			
			$taxa_references= json_decode($resp);
			
			foreach ($taxa_references->rows as $tr_row)
			{
				$values = export_reference($tr_row->doc);
				
				array_unshift($values, $core_id); 
				
				file_put_contents($reference_filename, join("\t", $values) . "\n", FILE_APPEND);
			}
			
			
		}
	}
	
	$page = ($obj->offset / $rows_per_page) + 1; // == 1
	$skip = $page * $rows_per_page; // == 10 for the first page, 20 for the second ...	
	
	echo '[' . $skip . '(' . count($obj->rows) . ')]';
	$done = ($skip > 1000);
}


?>