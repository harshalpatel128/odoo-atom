<?php

namespace App\Controllers;

use App\Core\Controller;

final class PageController extends Controller
{
    public function forbidden(): void
    {
        http_response_code(403);
        $this->view('errors/403', ['title' => 'Access Denied', 'panel' => 'user', 'csrf' => $this->csrf()]);
    }

    public function notFound(): void
    {
        http_response_code(404);
        $this->view('errors/404', ['title' => 'Page Not Found', 'panel' => 'user', 'csrf' => $this->csrf()]);
    }
}
