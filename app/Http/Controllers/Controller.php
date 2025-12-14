<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected $token;

    function __construct()
    {
        $this->token = session('api_token');
    }

    function isAuthenticated()
    {
        return session()->has('api_token');
    }
}
