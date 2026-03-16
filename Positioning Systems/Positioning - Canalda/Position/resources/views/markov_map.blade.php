@extends('layouts.app')

@section('content')
    <h1>TD n°4: Dynamic Markov Learning</h1>
    <p>Click the cells below to simulate movement and update the <b>Transition Matrix</b> in real-time.</p>

    <div
        style="display: grid; grid-template-columns: repeat(3, 100px); gap: 10px; margin-bottom: 30px; justify-content: center;">
        @for ($i = 0; $i < 9; $i++)
            <a href="?move_to={{ $i }}"
                style="width:100px; height:100px; border:2px solid #333; display:flex; align-items:center; justify-content:center; text-decoration:none; color:black; background: {{ session('last_cell_id') == $i ? '#d63031; color:white;' : '#fff' }}">
                Cell {{ $i }}
            </a>
        @endfor
    </div>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>From \ To</th>
                @for ($i = 0; $i < 9; $i++)
                    <th>C{{ $i }}</th>
                @endfor
                <th>Total (nb)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($matrix as $rowIdx => $row)
                <tr>
                    <td><b>C{{ $rowIdx }}</b></td>
                    @foreach (array_slice($row, 0, 9) as $cell)
                        <td style="background: rgba(9, 132, 227, {{ $cell->stat }})">
                            {{ number_format($cell->stat * 100, 0) }}%
                        </td>
                    @endforeach
                    <td>{{ $row[9]->nb }}</td>
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
