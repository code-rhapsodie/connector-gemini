<?php

declare(strict_types=1);

use CodeRhapsodie\Tests\Integration\ConnectorGemini\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;

require_once dirname(__DIR__) . '/vendor/autoload.php';

chdir(dirname(__DIR__));

$kernel = new Kernel('test', true);
$kernel->boot();

$application = new Application($kernel);
$application->setAutoExit(false);

$kernel->shutdown();
