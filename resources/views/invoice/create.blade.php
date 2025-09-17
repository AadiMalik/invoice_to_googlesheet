@extends('layouts.admin')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">

      <!-- Page Heading -->
      <!-- <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Create Subscription Plan</h1>
      </div> -->
      @if(session('error'))
      <div class="alert alert-danger">
            <ul>
                  <li>{{ session('error') }}</li>
            </ul>
      </div>
      @endif
      @if ($errors->any())
      <div class="alert alert-danger">
            <ul>
                  @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                  @endforeach
            </ul>
      </div>
      @endif
      <form action="{{url('invoice/store')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card">
                  <div class="card-header bg-transparent">
                        <h3 class="mb-0 text-gray-800">Upload Invoice</h3>
                  </div>
                  <div class="card-body">
                        <!-- Content Row -->
                        <div class="row">
                              <div class="col-md-12">
                                    <div class="form-group">
                                          <label class="form-label">Invoice:<span class="text-danger">*</span></label>
                                          <input type="file" id="invoice" name="invoice" class="form-control"  accept="image/*,.pdf" required />
                                          @error('invoice')
                                          <span class="text-danger">{{ $message }}</span>
                                          @enderror
                                    </div>
                              </div>
                        </div>
                  </div>
                  <div class="card-footer">
                        <div class="col-md-12">
                              <button class="btn btn-primary" id="submit" accesskey="s">Save</button>
                        </div>
                  </div>
            </div>
      </form>
</div>
<!-- /.container-fluid -->

@endsection