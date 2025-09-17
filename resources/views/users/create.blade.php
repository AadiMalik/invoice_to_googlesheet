@extends('layouts.admin')
@section('css')
<style>
      .hidden {
            display: none;
      }
</style>
@endsection
@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">

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
      <form action="{{url('user/store')}}" id="userAdd" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card">
                  <div class="card-header bg-transparent">
                        <h3 class="mb-0 text-gray-800">{{ isset($user)?'Edit':'Create' }} User</h3>
                  </div>
                  <div class="card-body">
                        <!-- Content Row -->
                        <div class="row">
                              <input type="hidden" name="id" value="{{ isset($user)?$user->id:'' }}">
                              <div class="col-md-6">
                                    <div class="form-group">
                                          <label class="form-label">Name:<span class="text-danger">*</span></label>
                                          <input type="text" id="name" name="name" placeholder="Enter user name" value="{{ isset($user)?$user->name:old('name') }}" class="form-control" required />
                                          @error('name')
                                          <span class="text-danger">{{ $message }}</span>
                                          @enderror
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group">
                                          <label class="form-label">Email:<span class="text-danger">**</span></label>
                                          <input type="email" id="email" name="email" placeholder="Enter user email" value="{{ isset($user)?$user->email:old('email') }}" class="form-control" required />
                                          @error('email')
                                          <span class="text-danger">{{ $message }}</span>
                                          @enderror
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group">
                                          <label class="form-label">Phone No:<span class="text-danger">**</span></label>
                                          <input type="text" id="phone" name="phone" placeholder="Enter user phone no" value="{{ isset($user)?$user->phone:old('phone') }}" class="form-control" required />
                                          @error('phone')
                                          <span class="text-danger">{{ $message }}</span>
                                          @enderror
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group">
                                          <label class="form-label">Password:<span class="text-danger">*</span></label>
                                          <input type="password" id="password" name="password" placeholder="Enter password" class="form-control" required />
                                          @error('password')
                                          <span class="text-danger">{{ $message }}</span>
                                          @enderror
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group">
                                          <label class="form-label">Role:<span class="text-danger">*</span></label>
                                          <select name="role" id="role" class="form-control" required>
                                                <option value="user" {{ (isset($user) && $user->role='user')?'selected':'' }}>User</option>
                                                <option value="admin" {{ (isset($user) && $user->role='admin')?'selected':'' }}>Admin</option>
                                          </select>
                                          @error('role')
                                          <span class="text-danger">{{ $message }}</span>
                                          @enderror
                                    </div>
                              </div>
                        </div>
                  </div>
                  <div class="card-footer">
                        <div class="col-md-12">
                              <button class="btn btn-primary" id="submit" accesskey="s">{{ isset($user)?'Update':'Save' }}</button>
                              <button class="btn btn-primary" type="button" id="loading" disabled>
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    Processing...
                              </button>
                        </div>
                  </div>
            </div>
      </form>
</div>
<!-- /.container-fluid -->

@endsection
@section('js')
<script>
      $('#loading').hide();

      function isNumberKey(evt) {
            var charCode = evt.which ? evt.which : evt.keyCode;
            if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57))
                  return false;

            return true;
      }
      document.getElementById('userAdd').addEventListener('submit', function(event) {
            $('#submit').hide();
            $('#loading').show(); // Show Loading
      });

      // If validation errors exist, show Save button again after reload
      @if($errors->any())
      $('#submit').hide();
      $('#loading').hide();
      @endif
</script>
@endsection