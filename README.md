# bionames-darwincorearchive
Darwin Core Archive export of BioNames

## Build Darwin Core Archive dump for BioNames

See [Darwin Core Text Guide](http://rs.tdwg.org/dwc/terms/guides/text/index.htm) for background, and [EOL Content Partners: Contribute Using Archives](http://eol.org/info/329). Can validate using [EOL Archive and Spreadsheet Validator](http://services.eol.org/dwc_validator/) and [GBIF Darwin Core Archive Validator](http://tools.gbif.org/dwca-validator/)

As per [EOL Deliverable](https://trello.com/c/dwoZ193L) generate a Darwin Core archive file containing taxa, references, and link between taxa and reference.

Note that there are some conceptual issues in generating a Darwin Core Archive for BioNames. Darwin Core Archive assumes that we have taxa, whereas BioNames is primarily a database of taxonomic names, not taxa. So for the moment we pretend that names are the same as taxa (they really, really aren’t).

## Generate Darwin Core Archive from CouchDB

Use create.php to generate files for archive. This queries CouchDB database to retrieve names, and then extracts any associated references. It also generates the meta.xml file.

Then create archive:

    zip bionames-dwca.zip meta.xml taxa.tsv references.tsv


