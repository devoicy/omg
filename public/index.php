<?php

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

// ======================================================
// 🔧 Setup Container
// ======================================================
$container = new Container();

// Database
$container->set(\Medoo\Medoo::class, function() {
    return require __DIR__ . '/../config/database.php';
});

// Twig view (fix untuk LoaderInterface)
$container->set(Twig::class, function() {
    return Twig::create(__DIR__ . '/../views', ['cache' => false]);
});

// Optional alias "view"


// ======================================================
// 🚀 Setup Slim
// ======================================================
AppFactory::setContainer($container);
$app = AppFactory::create();

// Middleware
$app->add(TwigMiddleware::createFromContainer($app, Twig::class));
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// Routes
(require __DIR__ . '/../App/Routes.php')($app);

// Run
$app->run();
