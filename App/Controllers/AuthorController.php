<?php
namespace App\Controllers;

use Slim\Views\Twig;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthorController
{
    private Twig $twig;
    private Medoo $db;

    public function __construct(Twig $twig, Medoo $db)
    {
        $this->twig = $twig;
        $this->db = $db;
    }

    public function add(Request $request, Response $response)
    {
        return $this->twig->render($response, 'author/add.twig');
    }

    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
        $this->db->insert('authors', [
            'name' => $data['name'],
            'slug' => $slug,
            'bio' => $data['bio']
        ]);

        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }
}
