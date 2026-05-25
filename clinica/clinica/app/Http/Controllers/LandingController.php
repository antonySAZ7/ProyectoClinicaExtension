<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        return view('landing.inicio');
    }

    public function nosotros(): View
    {
        return view('landing.nosotros');
    }

    public function objetivos(): View
    {
        return view('landing.objetivos');
    }

    public function contacto(): View
    {
        return view('landing.contacto');
    }
}
