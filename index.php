<?php
// ElasticSearch service configuration:
const esHost = 'https://localhost:9200';
const esPassword = 'Q+_6SrutkGQicUQuCMhY';

// Document URL for the parser:
const uri = '//foxentry.com/cs/cenik-api';

require_once 'src/test.php';

$test = new Test(esHost, esPassword);

$test->fetch(uri)
    ->parse()
    ->index();

echo <<<EOT
    Test passed, the document fetched, parsed, indexed, no errors noted.<br/>
    Test proběhl, dokument byl načten, rozebrán a indexován, nebyly zaznamenány žádné chyby.<br/>
EOT;
