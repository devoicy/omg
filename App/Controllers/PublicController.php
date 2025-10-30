<?php

namespace App\Controllers;

use App\Models\Series;
use App\Models\Chapters;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class PublicController
{
    private Twig $view;
    private Series $seriesModel;
    private Chapters $ChapterModel;

    public function __construct(Twig $view, Series $seriesModel, Chapters $ChapterModel)
    {
        $this->view = $view;
        $this->seriesModel = $seriesModel;
        $this->ChapterModel = $ChapterModel;
    }

    /**
     * Tampilkan halaman semua series
     */
public function index(Request $request, Response $response, array $args): Response
{
    $series = $this->seriesModel->getAll();

    // Decode last_chapter JSON agar Twig bisa pakai langsung
    foreach ($series as &$s) {
        $s['last_chapter'] = $s['last_chapter'] ? json_decode($s['last_chapter'], true) : null;
    }

    return $this->view->render($response, 'public/index.twig', [
        'series' => $series
    ]);
}


    /**
     * Tampilkan detail series berdasarkan slug
     */

    public function detail(Request $request, Response $response, array $args): Response
    {
        $slug = $args['slug'] ?? '';
        $series = $this->seriesModel->getBySlug($slug);
        var_dump($series);
        $newerSeries = $this->seriesModel->getSeriesByIdLogic(
            $series['series_id'],
            $series['created_at'],
            5
        );
        if (!$series) {
            $response->getBody()->write("Series tidak ditemukan");
            return $response->withStatus(404);
        }

        // Decode last_chapter JSON
        $series['last_chapter'] = $series['last_chapter'] ? json_decode($series['last_chapter'], true) : null;

        // Ambil query param 'order' (asc/desc)
        $queryParams = $request->getQueryParams();
        $order = $queryParams['order'] ?? 'asc';

        // Ambil daftar chapter
        $chapters = $this->seriesModel->getChapters($series['series_id'], $order);

        return $this->view->render($response, 'public/detail.twig', [
            'series' => $series,
            'chapters' => $chapters,
            'order' => $order,
            'newerSeries' => $newerSeries
        ]);
    }


public function chapter(Request $request, Response $response, array $args): Response
{
    $slug = $args['slug'];
    $chapter_number = $args['id'];

    // Ambil data series
    $series = $this->seriesModel->getBySlug($slug);

    if (!$series) {
        $response->getBody()->write("Series tidak ditemukan");
        return $response->withStatus(404);
    }


    // Ambil data chapter
    $chapter = $this->ChapterModel->getById($series['series_id'], $chapter_number);

    if (!$chapter || $chapter['series_id'] != $series['series_id']) {
        $response->getBody()->write("Chapter tidak ditemukan atau tidak sesuai dengan series");
        return $response->withStatus(404);
    }

    // Ambil chapter sebelumnya dan berikutnya
    $prevChapter = $this->ChapterModel->getAdjacentChapter($series['series_id'], $chapter['chapter_number'], 'prev');
    $nextChapter = $this->ChapterModel->getAdjacentChapter($series['series_id'], $chapter['chapter_number'], 'next');

    return $this->view->render($response, 'public/chapter.twig', [
        'series' => $series,
        'chapter' => $chapter,
        'prevChapter' => $prevChapter,
        'nextChapter' => $nextChapter
    ]);
}


}
