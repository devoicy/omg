<?php

use Slim\App;
use App\Controllers\PublicController;

return function (App $app) {
    // Homepage
    $app->get('/', [PublicController::class, 'index'])->setName('home');

    // Detail series
    $app->get('/title/{slug}', [PublicController::class, 'detail'])->setName('series.detail');

    // Halaman chapter
    $app->get('/title/{slug}/chapter/{id}', [PublicController::class, 'chapter'])->setName('chapter.detail');
};
