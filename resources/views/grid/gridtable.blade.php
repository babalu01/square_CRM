<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excel Table</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid black;
            padding: 10px;
            text-align: center;
        }

        form {
            margin: 0;
        }

        input[type="text"] {
            width: 100%;
            border: none;
            background: transparent;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Excel Data Table</h1>

    @if(session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <!-- Form for submitting the entire table data -->
    <form action="{{ route('store.excel.data') }}" method="POST">
        @csrf

        <table>
            @foreach($sheetData as $rowIndex => $row)
                <tr>
                    @foreach($row as $colIndex => $cell)
                        @php
                            // Check if the current cell is part of a merged cell
                            $colspan = 1;
                            $rowspan = 1;

                            foreach ($mergedCells as $mergeRange) {
                                // Get the start and end of the merged range
                                list($startCell, $endCell) = explode(':', $mergeRange);
                                $start = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($startCell);
                                $end = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($endCell);

                                // Check if the current cell is in this range
                                if (\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1) == $start[0] && $rowIndex + 1 == $start[1]) {
                                    $colspan = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($end[0]) - \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($start[0]) + 1;
                                    $rowspan = $end[1] - $start[1] + 1;
                                }
                            }
                        @endphp

                        @if ($colspan > 1 || $rowspan > 1)
                            <td colspan="{{ $colspan }}" rowspan="{{ $rowspan }}">
                                <input type="text" name="table[{{ $rowIndex }}][{{ $colIndex }}]" value="{{ $cell }}">
                            </td>
                        @else
                            <td>
 
