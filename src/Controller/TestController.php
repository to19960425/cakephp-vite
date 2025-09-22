<?php

declare(strict_types=1);

namespace App\Controller;

class TestController extends AppController
{
    public function index()
    {
        $this->viewBuilder()->disableAutoLayout();
    }
}
