<?php
require_once 'src/test.php';

$test = new Test();

$test->fetch('//foxentry.com/cs/cenik-api')
    ->parse()
    ->index();

echo <<<EOT
    Test passed, the document fetched, parsed, indexed, no errors noted.<br/>
    Test proběhl, dokument byl načten, rozebrán a indexován, nebyly zaznamenány žádné chyby.<br/>
EOT;
