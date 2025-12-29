@extends('layouts.app')

@section('content')
<main class="app-content">
<div class="container">
    <div class="row">
        <div class="col-md-12">
           @if (session('status'))
                <h6 class="alert alert-success">{{ session('status') }}</h6>
            @endif
            <div class="card">
                <div class="card-header">
                    <h4>Edit & Update Sub Contact Details
                        
                    </h4>
                </div>
                <div class="card-body">
             
                    <form action="{{ url('update_sub_contacts/'.$details->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                       
                        <div class="form-group list">
                          <label for="name">Primary Contact:</label>
                           <input type="text" class="form-control" id="name" name="name" value="{{$details->contact_name}}"><br>
                          <input type="email" class="form-control" id="email" name="email" value="{{$details->contact_email}}"><br>
                          <input type="text" class="form-control" id="mobile" name="mobile" value="{{$details->contact_mobile}}"><br>
                          <input type="text" class="form-control" id="designation" name="designation" value="{{$details->contact_designation}}">
                        </div>
                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
</main>

@endsection