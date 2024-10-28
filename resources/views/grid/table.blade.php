<!-- mannull columns heading -->
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        .table-header {
            background-color: #D9B2D9;
            text-align: center;
            font-weight: bold;
        }
        .table-subheader {
            background-color: #FFD700;
            text-align: center;
            font-weight: bold;
        }
        .table-subheader-blue {
            background-color: #ADD8E6;
            text-align: center;
            font-weight: bold;
        }
        .table-subheader-orange {
            background-color: #FFA07A;
            text-align: center;
            font-weight: bold;
        }
        .table-cell {
            text-align: center;
            vertical-align: middle;
        }
        .table-cell-green {
            background-color: #00FF00;
        }
        .table-cell-red {
            background-color: #FF0000;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th colspan="3" class="table-subheader">All Broker - Above 5 Lakhs for CV and Above 3 Lac for PVT & TW</th>

                    <th colspan="2" class="table-subheader">Upto 2.5T GCV including GCV 3W</th>
                    <th colspan="2" class="table-subheader">Agricultural Tractor & Harvester (excluding Trailer)</th>
                    <th colspan="2" class="table-subheader">PCV 3W (Carrying capacity 3+1) Petrol</th>
                    <th colspan="2" class="table-subheader">PCV 3W (Carrying capacity 3+1) Diesel</th>
                    <th colspan="2" class="table-subheader">PCV 3W (Carrying capacity 3+1) Other fuel</th>
                    <th colspan="2" class="table-subheader-blue">12T to 20T (Other makes)</th>
                    <th colspan="2" class="table-subheader-blue">12T to 20T (TATA & Ashok leyland)</th>
                    <th colspan="2" class="table-subheader">20T to 40T (Other makes)</th>
                    <th colspan="2" class="table-subheader">20T to 40T (TATA & Ashok leyland)</th>
                    <th colspan="2" class="table-subheader">>40T (Other makes)</th>
                    <th colspan="2" class="table-subheader">>40T (TATA & Ashok leyland)</th>
                    <th colspan="2" class="table-subheader">School Bus</th>
                    <th colspan="2" class="table-subheader">PCV Taxi (Carrying capacity 6+1)</th>
                    <th colspan="1" class="table-subheader">Pvt Car - Above 3 lakhs (Pvt car & 2W has common slabs)</th>
                    <th colspan="4" class="table-subheader">Pvt Car - STP</th>
                    <th colspan="4" class="table-subheader">2 Wheeler (On net for 1 year premium only (1+1))</th>
                </tr>
                <tr>
                <th  class="table-subheader">Region</th>
                    <th class="table-subheader">State Name</th>
                    <th  class="table-subheader">Circle</th>
                    <th class="table-subheader">Comp (Net)
                    </th>
                    <th class="table-subheader">SATP (Net)</th>
                    <th class="table-subheader">New
                    </th>
                    <th class="table-subheader">Non New
                    </th>
                    <th class="table-subheader">Comp (Net)</th>
                    <th class="table-subheader">SATP (Net)</th>
                    <th class="table-subheader">Comp (Net)</th>
                    <th class="table-subheader">SATP (Net)</th>
                    <th class="table-subheader">Comp (Net)</th>
                    <th class="table-subheader">SATP (Net)</th>
                    <th class="table-subheader">Comp (Net)</th>
                    <th class="table-subheader">SATP (Net)</th>
                    <th class="table-subheader">Comp (Net)</th>
                    <th class="table-subheader">SATP (Net)</th>
                    <th class="table-subheader">Comp (Net)</th>
                    <th class="table-subheader">SATP (Net)</th>
                    <th class="table-subheader">Comp (Net)</th>
                    <th class="table-subheader">SATP (Net)</th>
                    <th class="table-subheader">Comp (Net)</th>
                    <th class="table-subheader">SATP (Net)</th>
                    <th class="table-subheader">Comp (Net)</th>
                    <th class="table-subheader">SATP (Net)</th>
                    <th class="table-subheader">Comp (Net)</th>
                    <th class="table-subheader">SATP (Net)</th>
                    <th class="table-subheader">Comp (Net)</th>
                    <th class="table-subheader">SATP (Net)</th>
                    <th class="table-subheader">Comp (Net)</th>
                    <th class="table-subheader">SATP (Net)</th>
                   
                    <th rowspan="" class="table-subheader">"Pvt Car (PO On OD)
Comp & SAOD."
</th>
                    <th class="table-subheader">"Pvt Car - STP -
Petrol & Bifuel <
1500"
</th>
                    <th class="table-subheader">"Pvt Car - STP -
Diesel < 1500"
</th>
                    <th class="table-subheader">Pvt Car STP above 1500 (Diesel)
                    </th>
                    <th class="table-subheader">Pvt Car STP above 1500 (Petrol & Bifuel)
                    </th>
                    <th class="table-subheader">Scooter upto 150 cc (Comp & SAOD)
                    </th>
                    <th class="table-subheader">Bike upto 75 cc
                    </th>
                    <th class="table-subheader">"B.75-150
Bike"
</th>
                    <th class="table-subheader">C. Above 150cc
                    </th>
                  
                </tr>
            </thead>
            <tbody>
                @foreach ($sheetData as $index => $row)
                    @if ($index >= 2)
                        <tr>
                            @foreach ($row as $cell)



                                <td class="table-cell">{{ $cell ?? "" }}</td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
