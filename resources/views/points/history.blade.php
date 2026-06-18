@extends('layouts.app')

@section('content')

<div class="container">

    <h2>Lịch sử tích điểm</h2>

    <table class="table table-bordered">

        <thead>
            <tr>
                <th>Loại</th>
                <th>Điểm</th>
                <th>Mô tả</th>
                <th>Ngày</th>
            </tr>
        </thead>

        <tbody>

            @forelse($histories as $history)

            <tr>

                <td>{{ $history->type }}</td>

                <td>{{ $history->points }}</td>

                <td>{{ $history->description }}</td>

                <td>
                    {{ $history->created_at->format('d/m/Y H:i') }}
                </td>

            </tr>

            @empty

            <tr>
                <td colspan="4" class="text-center">
                    Chưa có lịch sử điểm
                </td>
            </tr>

            @endforelse

        </tbody>

    </table>

</div>

@endsection
