@extends('layouts.app')

@section('title', 'Điểm tích lũy')

@section('header')
    <h1 class="h2 mb-1">Điểm tích lũy</h1>
@endsection

@section('content')
    <div class="alert alert-success">Bạn có <strong>{{ auth()->user()->points ?? 0 }}</strong> điểm</div>
    <a href="{{ route('points.history') }}" class="btn btn-outline-primary">Xem lịch sử điểm</a>
@endsection
