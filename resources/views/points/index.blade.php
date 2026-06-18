@extends('layouts.app')

@section('content')

<div class="container">

    <h2>Điểm tích lũy của tôi</h2>

    <div class="alert alert-success">
        Bạn hiện có:
        <strong>{{ auth()->user()->points }}</strong>
        điểm
    </div>

</div>

@endsection
