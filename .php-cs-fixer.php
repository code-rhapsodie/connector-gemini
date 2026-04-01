<?php

declare(strict_types=1);

$factory = new Ibexa\CodeStyle\PhpCsFixer\InternalConfigFactory();

// You can omit the call below if you want Ibexa ruleset with no custom rules
$factory->withRules([
    // Your rules go here
]);
$config = $factory->buildConfig();
$config->setFinder(
    PhpCsFixer\Finder::create()
        ->in(__DIR__ . '/src')
        ->in(__DIR__ . '/tests')
        ->files()->name('*.php')
);

return $config;