@extends('layouts.app')

@section('content')
    <style>
        .approval-container {
            max-width: 600px;
            margin: 96px auto;
            padding: 60px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
            font-family: Arial, sans-serif;
        }
        .approval-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .approval-header h3 {
            margin: 0;
            color: #333;
        }
        .approval-header p {
            margin: 5px 0;
            color: #666;
        }
        .order-info {
            margin-bottom: 20px;
        }
        .order-info p {
            margin: 5px 0;
            font-size: 14px;
        }
        .btn-toggle {
            margin-bottom: 20px;
        }
        .check-group {
            display: flex;
            gap: 20px;
            justify-content: center;
        }
        .check-option {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 16px;
        }
        .check-option input[type="radio"] {
            margin-right: 8px;
            transform: scale(1.2);
        }
        .remarks-box {
            margin-bottom: 20px;
        }
        .remarks-box label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .remarks-box textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .save-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .save-btn:hover {
            background-color: #0056b3;
        }
        .completed {
            text-align: center;
            font-size: 18px;
            color: #28a745;
            margin-top: 20px;
        }
    </style>

    <div class="approval-container">
        <div class="approval-header">
            <h3>Sales Order Approval</h3>
            <p>Review and accept/reject this order</p>
        </div>

        <div class="order-info">
            <p><strong>Order No:</strong> {{ $orderData->sales_invoice_no }}</p>
            <p><strong>Customer ID:</strong> {{ $orderData->customer_id }}</p>
            <p><strong>Total Amount:</strong> ₹{{ number_format($orderData->total, 2) }}</p>
        </div>
        
        @if ($orderData->status === 0)
            {{-- Pending: Show Approve/Reject UI --}}
            <div class="btn-toggle">

                <div class="check-group">
                    <label class="check-option accept" id="acceptBtn">
                        <input type="radio" name="action" value="accept" onclick="selectAction('accept')">
                        <span>✅ Approve</span>
                    </label>

                    <label class="check-option reject" id="rejectBtn">
                        <input type="radio" name="action" value="reject" onclick="selectAction('reject')">
                        <span>❌ Reject</span>
                    </label>
                </div>
            </div>

            <div class="remarks-box">
                <label for="remarks" class="form-label"><strong>Remarks (optional):</strong></label>
                <textarea id="remarks" rows="3" placeholder="Enter your remarks here..."></textarea>
            </div>

            <button class="save-btn" onclick="submitApproval({{ $orderData->id }})">💾 Save</button>
        @else
            {{-- Already Completed --}}
            <div class="completed">
                ✅ Approval Completed
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        let selectedAction = '';

        function selectAction(action) {
            selectedAction = action;

            // Toggle active state
            document.getElementById('acceptBtn').classList.remove('active');
            document.getElementById('rejectBtn').classList.remove('active');

            if (action === 'accept') {
                document.getElementById('acceptBtn').classList.add('active');
            } else {
                document.getElementById('rejectBtn').classList.add('active');
            }
        }

        function submitApproval(id) {
            if (!selectedAction) {
                alert('⚠️ Please select Approve or Reject before saving.');
                return;
            }

            let remark = document.getElementById('remarks').value;

            $.ajax({
                url: '{{ url('approve-sales-request') }}/' + id + '/' + selectedAction,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    remarks: remark
                },
                success: function(data) {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                },
                error: function() {
                    alert('Server error occurred.');
                }
            });
        }
    </script>
@endpush
