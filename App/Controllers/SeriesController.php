<?php
namespace App\Controllers;

use Slim\Views\Twig;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SeriesController
{
    private Twig $twig;
    private Medoo $db;

    public function __construct(Twig $twig, Medoo $db)
    {
        $this->twig = $twig;
        $this->db = $db;
    }

    public function create(Request $request, Response $response)
    {
        $genres = $this->db->select('genres', ['id', 'name']);
        $authors = $this->db->select('authors', ['id', 'name']);
        return $this->twig->render($response, 'series/create.twig', compact('genres', 'authors'));
    }

    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));

        $this->db->insert('series', [
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'],
            'status' => $data['status'],
            'cover_image' => $data['cover_image'] ?? null,
            'created_by' => $_SESSION['user_id'] ?? 1
        ]);

        $seriesId = $this->db->id();

        foreach ($data['authors'] ?? [] as $aid) {
            $this->db->insert('series_authors', [
                'series_id' => $seriesId,
                'author_id' => $aid
            ]);
        }

        foreach ($data['genres'] ?? [] as $gid) {
            $this->db->insert('series_genres', [
                'series_id' => $seriesId,
                'genre_id' => $gid
            ]);
        }

        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }
}
