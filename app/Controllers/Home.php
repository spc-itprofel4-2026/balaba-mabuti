<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (session()->get('officerId')) {
            return redirect()->to('/dashboard');
        }

        return redirect()->to('/login');
    }
}
