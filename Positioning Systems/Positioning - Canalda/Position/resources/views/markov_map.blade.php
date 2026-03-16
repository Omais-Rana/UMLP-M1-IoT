@extends('layouts.app')

@section('content')
    <h1>TD n°4: Dynamic Markov Model</h1>
    <p>Click the cells below to simulate movement and update the <b>Transition Matrix</b> in real-time.</p>

    <div style="display: flex; justify-content: center; gap: 40px; margin-bottom: 30px;">
        <!-- Left side: The Grid -->
        <div style="display: grid; grid-template-columns: repeat(3, 100px); gap: 10px;">
            @for ($i = 0; $i < 6; $i++)
                <a href="?move_to={{ $i }}"
                    style="width:100px; height:100px; border:2px solid #333; display:flex; align-items:center; justify-content:center; text-decoration:none; font-weight:bold; background: {{ session('last_cell_id') === $i ? '#d63031' : '#fff' }}; color: {{ session('last_cell_id') === $i ? '#fff' : '#000' }};">
                    Cell {{ $i }}
                </a>
            @endfor
        </div>

        <!-- Right side: The History -->
        <div
            style="width: 250px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 15px; max-height: 220px; overflow-y: auto;">
            <h4 style="margin-top: 0; font-size: 1.1em; border-bottom: 1px solid #ccc; padding-bottom: 5px;">Movement History
            </h4>
            <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                @if (empty($history))
                    <span style="color: #888; font-style: italic;">No moves yet.</span>
                @else
                    @foreach ($history as $index => $cellId)
                        <span
                            style="background: #0984e3; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.9em;">
                            {{ $index == 0 ? 'Start' : '->' }} C{{ $cellId }}
                        </span>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>From \ To</th>
                @for ($i = 0; $i < 6; $i++)
                    <th>C{{ $i }}</th>
                @endfor
                <th>Total (nb)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($matrix as $rowIdx => $row)
                <tr>
                    <td><b>C{{ $rowIdx }}</b></td>
                    @foreach (array_slice($row, 0, 6) as $cell)
                        <td style="background: rgba(9, 132, 227, {{ $cell->stat }})">
                            {{ number_format($cell->stat * 100, 0) }}%
                        </td>
                    @endforeach
                    <td>{{ $row[6]->nb }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; border-top: 1px solid #ccc; padding-top: 20px;">
        <form action="{{ route('markov.reset') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger"
                style="background: #d63031; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                Clear History & Reset Matrix
            </button>
        </form>
    </div>
@endsection
