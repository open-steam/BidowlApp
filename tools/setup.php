#!/usr/bin/php
<?php
$pathBase = __DIR__ . "/../";

passthru("cd $pathBase; php app/console cache:clear --env=prod; chmod -R 777 app/cache");
