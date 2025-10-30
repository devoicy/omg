<?php
namespace App\Controllers;

use Slim\Views\Twig;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    private Twig $twig;
    private Medoo $db;

    public function __construct(Twig $twig, Medoo $db)
    {
        $this->twig = $twig;
        $this->db = $db;
    }

    public function showLogin(Request $request, Response $response)
    {
        return $this->twig->render($response, 'auth/login.twig');
    }

    public function login(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $user = $this->db->get('users', '*', ['email' => $data['email']]);

        if ($user && password_verify($data['password'], $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        return $this->twig->render($response, 'auth/login.twig', [
            'error' => 'Email atau password salah.'
        ]);
    }

    public function logout(Request $request, Response $response)
    {
        session_destroy();
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}
