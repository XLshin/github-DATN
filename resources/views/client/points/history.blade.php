@extends('layouts.app')

@section('title', 'Lịch sử điểm')

@section('header')
    <h1 class="h2 mb-1">Lịch sử điểm</h1>
@endsection

@section('content')
    @if ($histories->isEmpty())
        <div class="alert alert-info">Chưa có lịch sử điểm.</div>
    @else
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light"><tr><th>Thời gian</th><th>Mô tả</th><th class="text-end">Điểm</th></tr></thead>
                    <tbody>
                        @foreach ($histories as $history)
                            <tr>
                                <td>{{ $history->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $history->description ?? $history->type ?? '—' }}</td>
                                <td class="text-end">{{ $history->points }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
