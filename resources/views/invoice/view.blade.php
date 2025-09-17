@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-transparent d-flex justify-content-between">
            <h3 class="mb-0 text-gray-800">Invoice</h3>
            <button class="btn btn-primary" onclick="copyInvoice()">Copy</button>
        </div>
        <div class="card-body" id="invoiceArea">
            <h2 class="text-center">{{ $structured['invoice_heading'] ?? 'Invoice' }}</h2>
            <h4>Website Invoice No: <strong>{{ $structured['unique_invoice_no'] }}</strong></h4>
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>From:</h5>
                    <p>{{ $structured['company_from'] ?? '' }}</p>
                </div>
                <div class="col-md-6 text-right">
                    <h5>To:</h5>
                    <p>{{ $structured['customer_to'] ?? '' }}</p>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <strong>Invoice No:</strong> {{ $structured['invoice_no'] ?? '' }}<br>
                    <strong>Date:</strong> {{ $structured['date'] ?? '' }}
                </div>
            </div>

            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Disc %</th>
                        <th>Disc Amt</th>
                        <th>VAT/Tax</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($structured['items'] ?? [] as $item)
                    <tr>
                        <td>{{ $item['name'] ?? '' }}</td>
                        <td>{{ $item['qty'] ?? '' }}</td>
                        <td>{{ $item['price'] ?? '' }}</td>
                        <td>{{ $item['disc_percent'] ?? '' }}</td>
                        <td>{{ $item['disc_amount'] ?? '' }}</td>
                        <td>{{ $item['vat_tax'] ?? '' }}</td>
                        <td>{{ $item['total'] ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="row justify-content-end">
                <div class="col-md-4">
                    <table class="table table-sm">
                        <tr>
                            <th>Subtotal:</th>
                            <td>{{ $structured['totals']['subtotal'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Discount:</th>
                            <td>{{ $structured['totals']['discount'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Total Ex VAT/Tax:</th>
                            <td>{{ $structured['totals']['total_ex_vat_tax'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Total VAT/Tax:</th>
                            <td>{{ $structured['totals']['total_vat_tax'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Net Total:</th>
                            <td>{{ $structured['totals']['net_total'] ?? '' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
function copyInvoice() {
    let text = document.getElementById("invoiceArea").innerText;
    navigator.clipboard.writeText(text).then(function() {
        alert("Invoice copied in formatted text!");
    }, function(err) {
        alert("Failed to copy: " + err);
    });
}
</script>
@endsection
