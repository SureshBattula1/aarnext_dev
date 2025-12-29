@extends('layouts.app')
<style>
  .dropdown-menu.show {
    top: 43px !important;  
}
</style>
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
                    <h4>Edit & Update Company Details
                        
                    </h4>
                </div>
                <div class="card-body">
             
                    <form action="{{ url('update_company_details/'.$details->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                       
                        <div class="form-group">
                          <label for="customer_email">Customer Email:</label>
                          <input type="email" class="form-control" id="customer_email" name="customer_email" value="{{$details->customer_email}}">
                        </div>
                        <div class="form-group">
                          <label for="customer_mobile">Customer Mobile No:</label>
                          <input type="text" class="form-control" id="customer_mobile" name="customer_mobile" value="{{$details->customer_mobile}}">
                        </div>
                        <div class="form-group">
                          <label for="customer_designation">Customer Designation:</label>
                          <input type="text" class="form-control" id="customer_designation" name="customer_designation" value="{{$details->customer_designation}}">
                        </div>
                        <div class="form-group">
                          <label for="division">Main Division:</label>
                            <select name="division" id="division" class="form-control">
                               <option value="" disabled selected>Select Division</option>
                              
                               <option value="SAP" <?php if($details->division =='SAP'){
                                  echo 'selected';
                               }else{  } ?> >SAP</option>
                               <option value="ByDesign" <?php if($details->division =='ByDesign'){
                                  echo 'selected';
                               }else{  } ?>>ByDesign</option>
                               <option value="IE" <?php if($details->division =='IE'){
                                  echo 'selected';
                               }else{  } ?>>IE</option>
                               <!-- <option value="Web">Web</option> -->
                            </select>
                            <span class="input-group-addon">-</span>

                            <select name="sub_division" id="choices" class="form-control">
                              <option value="" disabled selected>Please select Sub Division</option>
                            </select>
                        </div>                       

                        <div class="form-group">
                          <label for="pan_number">Pan Number</label>
                          <input type="text" class="form-control" id="pan_number" name="pan_number" value="{{$details->pan_number}}">
                        </div>
                        <div class="form-group">
                          <label for="gst_number">Gst Number</label>
                          <input type="text" class="form-control" id="gst_number" name="gst_number" value="{{$details->gst_number}}">
                        </div>
                         <div class="form-group">
                          <label for="employees">Assign Employees</label>
                          <select name="employees" id="employees" multiple class="form-control selectpicker">
                           <!-- <option value="{{$details->employees}}">{{$details->employees}}</option> -->
                            @foreach($users as $user)

                            <option value="{{$user->id}}" <?php echo (isset($details->employees) && in_array($user->id, explode(',', $details->employees)) ) ? "selected" : "" ?>>{{$user->name}}</option>

                              <!--   <option value="{{$user->id}}">{{$user->name}}</option> -->
                            @endForeach
                           </select>
                        </div>
                        <input type="hidden" name="hidden_id" id="hidden_id" />
                        <div style="clear:both"></div>
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

<script type="text/javascript">
  // Map your choices to your option value
var lookup = {
   'SAP': ['SAP Technical', 'SAP Functional'],
   'ByDesign': ['ByDesign Technical', 'ByDesign Functional'],
   'IE': ['AIML','Web'],
   // 'Web': ['Web','Ui'],
};

// When an option is changed, search the above for matching choices
$('#division').on('change', function() {
   // Set selected option as variable
   var selectValue = $(this).val();

   // Empty the target field
   $('#choices').empty();
   
   // For each chocie in the selected option
   for (i = 0; i < lookup[selectValue].length; i++) {
      // Output choice in the target field
      $('#choices').append("<option value='" + lookup[selectValue][i] + "'>" + lookup[selectValue][i] + "</option>");
   }
});

$('#employees').change(function(){
    $('#hidden_id').val($('#employees').val());
    var query = $('#hidden_id').val();
  });
</script>
@endsection