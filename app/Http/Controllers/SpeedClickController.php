<?php

namespace App\Http\Controllers;

class SpeedClickController extends Controller
{
    public function index()
    {
        return view('games.speedclick.speedclick');
    }

    public function challenge()
{
    return view('games.speedclick.challenge');
}

}
