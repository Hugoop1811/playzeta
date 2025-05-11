<?php

namespace App\Http\Controllers;

class SpeedClickController extends Controller
{
    public function index()
    {
        return view('games.speedclick');
    }

    public function challenge()
{
    return view('games.challenge');
}

}
