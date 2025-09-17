@extends('layouts.admin')
@section('css')
<style>
      .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 25px;
      }

      .switch input {
            opacity: 0;
            width: 0;
            height: 0;
      }

      .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: red;
            transition: .4s;
            border-radius: 25px;
      }

      .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
      }

      input:checked+.slider {
            background-color: #007bff;
            /* Primary color */
      }

      input:checked+.slider:before {
            transform: translateX(24px);
      }

      .toast-success {
            background-color: green !important;
      }

      .toast-error {
            background-color: red !important;
      }
</style>
@endsection
@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">

      <!-- Page Heading -->
      <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Invoices</h1>
            <a href="{{url('invoice/create')}}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                        class="fas fa-plus fa-sm text-white-50"></i> Add New</a>
      </div>
      @if(session('success'))
      <script>
            toastr.success("{{ session('success') }}");
      </script>
      @endif
      <!-- Content Row -->
      <div class="card">
            <div class="card-header">

            </div>
            <div class="card-body">
                  <div class="row">
                        <div class="col-md-12">
                              <div class="table-responsive">
                                    <table id="invoiceTable" class="table table-striped" style="width: 100%;">
                                          <caption>List of Invoices</caption>
                                          <thead>
                                                <tr>
                                                      <th scope="col">#</th>
                                                      <th scope="col">Invoice #</th>
                                                      <th scope="col">Invoice</th>
                                                      <th scope="col">User</th>
                                                      <th scope="col">Action</th>
                                                </tr>
                                          </thead>
                                          <tbody>
                                                @foreach($invoices as $index=>$item)
                                                <tr>
                                                      <th scope="row">{{ $index+1 }}</th>
                                                      <th scope="row">{{ $item->invoice_no ?? '' }}</th>
                                                      <td>
                                                            @php
                                                            $extension = strtolower(pathinfo($item->invoice, PATHINFO_EXTENSION));
                                                            @endphp

                                                            @if($extension === 'pdf')
                                                            <img src="{{ asset('pdf.png') }}"
                                                                        alt="PDF"
                                                                        style="width:50px; height:50px;">
                                                            @else
                                                            <img src="{{ asset('storage/' . $item->invoice) }}"
                                                                  style="width:50px; height:50px;"
                                                                  alt="invoice image">
                                                            @endif
                                                      </td>
                                                      <td>{{ $item->user->name ?? '' }}</td>
                                                      <td>
                                                            <!-- Download -->
                                                            <a href="{{ asset('storage/' . $item->invoice) }}"
                                                                  class="btn btn-primary mr-2"
                                                                  download>
                                                                  <i class="fa fa-download"></i> Download
                                                            </a>

                                                            <a class="btn btn-danger mr-2" id="delete" href="javascript:void(0)"
                                                                  data-toggle="tooltip" data-id="{{ $item->id }}"
                                                                  data-original-title="delete">
                                                                  <i title="Delete" class="fa fa-trash"></i> Delete
                                                            </a>

                                                            <a href="{{ url('invoice/view/' . $item->id) }}"
                                                                  class="btn btn-info mr-2">
                                                                  <i class="fa fa-file"></i> View Invoice
                                                            </a>
                                                      </td>
                                                </tr>
                                                @endforeach
                                          </tbody>
                                    </table>
                              </div>
                        </div>
                  </div>
            </div>
      </div>

</div>
<!-- /.container-fluid -->

@endsection
@section('js')
<script>
      $(document).ready(function() {
            $('#invoiceTable').DataTable(); // Initialize DataTable without AJAX
      });
      $("body").on("click", "#delete", function() {
            var invoice_id = $(this).data("id");
            Swal.fire({
                  title: "Are you sure?",
                  text: "You won't be able to revert this!",
                  icon: "warning",
                  showCancelButton: true,
                  confirmButtonColor: "#3085d6",
                  cancelButtonColor: "#d33",
                  confirmButtonText: "Yes, delete it!",
            }).then((result) => {
                  if (result.isConfirmed) {
                        $.ajax({
                                    type: "get",
                                    url: "{{ url('invoice/destroy') }}/" + invoice_id,
                              })
                              .done(function(data) {
                                    if (data.success) {
                                          toastr.success(data.message);
                                          setTimeout(function() {
                                                location.reload();
                                          }, 1000);
                                    } else {
                                          toastr.error(data.message);
                                    }
                              })
                              .catch(function(err) {
                                    toastr.error(err.message);
                              });
                  }
            });
      });
</script>
@endsection