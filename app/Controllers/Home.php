<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $data = [
            'title' => 'Home - City General Hospital',
            'page_title' => 'Welcome to City General Hospital'
        ];

        return view('home', $data);
    }

    public function about(): string
    {
        $data = [
            'title' => 'About - City General Hospital',
            'page_title' => 'About City General Hospital'
        ];

        return view('about', $data);
    }

    public function contact(): string
    {
        $data = [
            'title' => 'Contact - City General Hospital',
            'page_title' => 'Contact City General Hospital'
        ];

        return view('contact', $data);
    }
}
