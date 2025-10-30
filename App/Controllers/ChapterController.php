<?php
namespace App\Controllers;

use Slim\Views\Twig;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ChapterController
{
    private Twig $twig;
    private Medoo $db;

    public function __construct(Twig $twig, Medoo $db)
    {
        $this->twig = $twig;
        $this->db = $db;
    }

    public function add(Request $request, Response $response, array $args)
    {
        $series = $this->db->get('series', ['id', 'title'], ['id' => $args['id']]);
        return $this->twig->render($response, 'chapter/add.twig', compact('series'));
    }

    public function store(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();
        $this->db->insert('chapters', [
            'series_id' => $args['id'],
            'title' => $data['title'],
            'chapter_number' => $data['chapter_number'],
            'content_path' => $data['content_path'] ?? '[]',
            'created_by' => $_SESSION['user_id'] ?? 1
        ]);

        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }
}
