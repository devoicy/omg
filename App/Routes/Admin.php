<?php
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return (function (App $app) {

    // Dashboard admin
    $app->get('/admin', function (Request $request, Response $response) {
        $response->getBody()->write("Dashboard Admin Webtoon");
        return $response;
    });

    // Kelola series
    $app->get('/admin/series', function (Request $request, Response $response) {
        $response->getBody()->write("Manajemen Series");
        return $response;
    });

    // Tambah series
    $app->get('/admin/series/add', function (Request $request, Response $response) {
        $response->getBody()->write("Form Tambah Series Baru");
        return $response;
    });

    // Kelola chapter
    $app->get('/admin/chapters', function (Request $request, Response $response) {
        $response->getBody()->write("Manajemen Chapter");
        return $response;
    });

});
