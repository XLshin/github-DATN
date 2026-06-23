<?php

namespace App\Http\Controllers;

class PointController extends Controller
{
    public function index()
    {
        return view('client.points.index');
    }

    public function history()
    {
        $histories = auth()->user()->pointHistories()->latest()->get();

        return view('client.points.history', compact('histories'));
    }
}
