<?php

declare(strict_types=1);
$_SERVER["PHP_AUTH_USER"] = 'smidv@nem.local';
require __DIR__ . '/../vendor/autoload.php';

App\Bootstrap::boot()
	->createContainer()
	->getByType(Nette\Application\Application::class)
	->run();
