<?php

namespace App\Http\Controllers;

class PointController extends Controller
{
    public function index()
    {
        return view('points.index');
    }

    public function history()
{
    $histories = auth()->user()
                       ->pointHistories()
                       ->latest()
                       ->get();

    return view(
        'points.history',
        compact('histories')
    );
}
}
