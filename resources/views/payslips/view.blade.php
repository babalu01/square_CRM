{{-- custom view --}}
<html>
<head>
  <style>
    /* Reset and base styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: Arial, sans-serif;
      line-height: 1.6;
      padding: 20px;
      background-color: whitesmoke;
    }

    /* Container styles */
    .container {
      border: 1px dashed #000;
      padding: 20px;
      max-width: 800px;
      margin: 20px auto;
      background-color: white;
    }

    /* Header section */
    .header {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
    }

    .text-end {
      text-align: right;
      flex: 1;
    }

    .text-start {
      text-align: left;
      flex: 2;
      padding-left: 20px;
    }

    /* Table styles */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    td, th {
      border: 1px solid #000;
      padding: 8px;
    }

    th {
      text-align: left;
      background-color: #f5f5f5;
    }

    /* Utility classes */
    .text-center {
      text-align: center;
    }

    .mt-4 {
      margin-top: 20px;
    }

    /* Print button */
    .print-btn {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 10px 20px;
      cursor: pointer;
      border-radius: 4px;
    }

    .print-btn:hover {
      background-color: #0056b3;
    }

    /* Print styles */
    @media print {
      .print-btn {
        display: none;
      }
      
      .container {
        border: none;
      }
    }

    /* Custom styles to replace Bootstrap */
    .flex {
      display: flex;
    }

    .flex-wrap {
      flex-wrap: wrap;
    }

    .flex-1 {
      flex: 1;
    }

    .flex-2 {
      flex: 2;
    }

    .min-width-200 {
      min-width: 200px;
    }

    .text-right {
      text-align: right;
    }

    .text-left {
      text-align: left;
      padding-left: 20px;
    }

    .total {
      font-weight: bold;
    }
   
  </style>
</head>
<body>
 
    <div class="container">
             <a href="#" onclick="window.print()">Print </a>
          <div class="header flex">
            <div class="flex-1 text-right">
                <img alt="Company Logo" height="200" src="{{ url('storage/logos/logo.png') }}" width="200" />
            </div>
            <div class="flex-2 text-left companytitle">
                <h1>{{($general_settings['company_title'])}}</h1>
                <h4>
                    123, Sunshine Apartments,
                    Near Bandra Station,
                    Bandra West, Mumbai - 400050,
                    Maharashtra, India.
                </h4>
                <h3>
                 Pay Slip for {{ $payslip->month->format('F, Y') }}
                </h3>
            </div>
        </div>
        <br>
   <hr>
   <div class="employee-info flex flex-wrap">
       <div class="flex-1 min-width-200">
           <strong>Employee ID:</strong> {{$payslip->user_id}}<br>
           <strong>Employee Name:</strong> {{$payslip->user_name}}<br>
           <strong>Designation:</strong>{{ $payslip->user->role->name ?? ""}}<br>
           <strong>Department:</strong> TEST<br>
       </div>
       <div class="flex-1 min-width-200">
           <strong>PF No.:</strong> 88585458<br>
           <strong>ESI No.:</strong> 450450450<br>
           <strong>Payment Status:</strong> {!!$payslip->status!!}<br>
       </div>
   </div><br>
   <hr>
   <br>
   <table class="details">
    <tr>
     <td>Gross Wages</td>
     <td><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>{{ ($payslip->net_pay) }}</td>
     <td>Total Working Days</td>
     <td>{{ $payslip->working_days }}</td>
     <td>Leaves</td>
     <td>{{ $payslip->lop_days }}</td>
    </tr>
    <tr>
     <td>LOP Days</td>
     <td>{{ $payslip->lop_days }}</td>
     <td>Paid Days</td>
     <td>{{ $payslip->paid_days }}</td>
     <td>Basic Salary</td>
     <td><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span> {{ ($payslip->basic_salary) }}</td>
    </tr>
   </table>
   <table class="earnings">
    <thead>
     <tr>
      <th>Earnings</th>
      <th></th>
      <th>Deductions</th>
      <th></th>
     </tr>
    </thead>
    <tbody>
        <tr>
            <td>Basic</td>
            <td><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>{{ ($payslip->basic_salary -$payslip->leave_deduction) }}</td>
            <td></td>
            <td></td>
        </tr>
         @foreach($payslip->allowances as $index => $allowance)
            <tr>
                <td>{{ $allowance->title }}</td>
                <td><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>{{ ($allowance->amount) }}</td>
                @if ($index < count($payslip->deductions)) <!-- Check if deduction exists -->
                    <td>{{$payslip->deductions[$index]->title}}</td>
                    <td><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>{{ ($payslip->deductions[$index]->amount) }}</td>
                @else
                    <td></td>
                    <td></td>
                @endif
            </tr>
        @endforeach

        @for ($i = count($payslip->allowances); $i < count($payslip->deductions); $i++)
            <tr>
                <td></td>
                <td></td>
                <td>{{$payslip->deductions[$i]->title}}</td>
                <td><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>{{ ($payslip->deductions[$i]->amount) }}</td>
            </tr>
        @endfor
        <tr class="total">
            <td>Total Earnings</td>
            <td><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>{{ ($payslip->allowances->sum('amount') + $payslip->basic_salary -$payslip->leave_deduction) }}</td>
            <td>Total Deductions</td>
            <td><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>{{ ($payslip->deductions->sum('amount')) }}</td>
        </tr>
    </tbody>
   </table>
   <div class="net-salary">
   <strong> Net Salary: <span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>{{ ($payslip->net_pay) }}</strong>
   </div>
  </div>
</body>
</html>
{{-- custom view --}}