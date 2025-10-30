<?php
namespace App\Controllers;

use Slim\Views\Twig;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DashboardController
{
    private Twig $twig;
    private Medoo $db;

    public function __construct(Twig $twig, Medoo $db)
    {
        $this->twig = $twig;
        $this->db = $db;
    }

    public function index(Request $request, Response $response)
    {
        $data = [
            'seriesCount' => $this->db->count('series'),
            'chapterCount' => $this->db->count('chapters'),
            'genreCount' => $this->db->count('genres'),
            'authorCount' => $this->db->count('authors'),
        ];

        return $this->twig->render($response, 'dashboard/index.twig', $data);
    }
}
