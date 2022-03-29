<?php

include 'autoloader.php';
spl_autoload_register ('myautoloader');

set_include_path(get_include_path() . ':' . $_SERVER['HOME'] . '/classes/prod');

$nconst = 'nm9272303';
$bio = new get_bio('key', $nconst);
echo 'Endpoint: ' . $bio -> getEndpoint() . "\n";

$bio -> go();
$body = $bio -> getBody();
echo 'body: ' . $body . "\n";
$result = $bio -> getResult();

print_r ($result);

?>
