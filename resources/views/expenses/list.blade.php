@extends('layouts.app')

<style>
    .card-header {
        background-color: #333;
        color: #fff;
        padding: 10px;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }

    .total-amount-container {
        margin-top: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        margin-right: 80px;
    }

    #totalAmount {
        width: 60px;
        height: 30px;
        padding: 5px;
    }
</style>

@section('content')
    <div class="content-wrapper">
        <div class="card">

            <div class="card-header">
                <h3 class="d-block p-2 bg-primary text-white">EXPENSES DETAILS </h3>
            </div>
        </div>
        <div class="card">
            {{-- <div class="card-body">
                <h4 class="card-title">User Details</h4>
                <div class="row">
                    <div class="col-md-6">

                    </div>
                </div> --}}

            <h4 class="header-title mt-4" style="margin-left: 12px">Expenses List</h4>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Account Code</th>
                            <th>Amount</th>
                            <th>Inter Company Code</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalAmount = 0; @endphp
                        @forelse ($expenses as $expense)
                            <tr>
                                <td>{{ $expense->acc_name }}</td>
                                <td>{{ $expense->acc_code }}</td>
                                <td>{{ $expense->amount }}</td>
                                <td>{{ $expense->inter_cmpy_code }}</td>
                                @php $totalAmount += $expense->amount; @endphp
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">No expenses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="total-amount-container" style="margin-left: auto;">
                <label for="totalAmount"><strong>Total Amount:</strong></label>
                <input type="text" id="totalAmount" value="{{ $totalAmount }}" readonly>
            </div>
        </div>

    </div>
    </div>
@endsection
