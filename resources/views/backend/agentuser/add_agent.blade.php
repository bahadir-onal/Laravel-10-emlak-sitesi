@extends('admin.admin_dashboard')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <div class="page-content">
        <div class="row profile-body">
          <!-- middle wrapper start -->
            <div class="col-md-8 col-xl-12 middle-wrapper">
                <div class="row">
                    <div class="col-md-12 grid-margin">
                        <div class="card">
                            <div class="card-body">
                              <h6 class="card-title">Add Agent</h6>
                              <form id="myForm" method="post" action="{{ route('store.agent') }}" class="forms-sample">
                                @csrf

                                <div class="mb-3 form-group">
                                  <label for="exampleInputUsername1" class="form-label">Agent Name</label>
                                  <input type="text" name="name" class="form-control" >
                                </div>

                                <div class="mb-3 form-group">
                                  <label for="exampleInputUsername1" class="form-label">Agent E-Mail</label>
                                  <input type="email" name="email" class="form-control" >
                                </div>

                                <div class="mb-3 form-group">
                                  <label for="exampleInputUsername1" class="form-label">Agent Phone</label>
                                  <input type="text" name="phone" class="form-control" >
                                </div>

                                <div class="mb-3 form-group">
                                  <label for="exampleInputUsername1" class="form-label">Agent Address</label>
                                  <input type="text" name="address" class="form-control" >
                                </div>

                                <div class="mb-3 form-group">
                                  <label for="exampleInputUsername1" class="form-label">Agent Password</label>
                                  <input type="password" name="password" class="form-control" >
                                </div>
                                
                                <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                              </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	</div>

    <script type="text/javascript">
    $(document).ready(function (){
        $('#myForm').validate({
            rules: {
                name: {
                    required : true,
                }, 
                email: {
                    required : true,
                },
                phone: {
                    required : true,
                },
                address: {
                    required : true,
                },
                password: {
                    required : true,
                },
                
            },
            messages :{
                name: {
                    required : 'Please enter name',
                },
                email: {
                    required : 'Please enter e-mail',
                },
                phone: {
                    required : 'Please enter phone number',
                },
                address: {
                    required : 'Please enter address',
                },
                password: {
                    required : 'Please enter password',
                }, 
                 
            },
            errorElement : 'span', 
            errorPlacement: function (error,element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight : function(element, errorClass, validClass){
                $(element).addClass('is-invalid');
            },
            unhighlight : function(element, errorClass, validClass){
                $(element).removeClass('is-invalid');
            },
        });
    });
    
</script>

@endsection