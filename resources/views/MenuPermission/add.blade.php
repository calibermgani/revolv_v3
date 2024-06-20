@extends('layouts.app3')
@section('content')
    {!! Form::open(['id' => 'menu_permission']) !!}
    @csrf
    @include('MenuPermission.form')
    {!! Form::close() !!}
@endsection

@push('view.scripts')
    <script>
        $(document).on("click", ".menu_permission_submit", function(e) {
            var user_id = $('#user_id').val();
            var mList = "";
            if (user_id == "") {
                Swal.fire("Permission Failed", "User Name is required", "error");
            } else {
                $('input[type=checkbox]').each(function() {
                    var sThisVal = (this.checked);
                    if (this.checked) {
                        mList += (mList == "" ? this.value : "," + this.value);
                    }

                });
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: baseUrl + "permission/grand_permission",
                    type: 'POST',
                    data: {
                        mList: mList,
                        user_id: user_id

                    },
                    success: function(data) {
                        if (data == 1) {
                            Swal.fire("Menu !", "Permission Update Successfully", "success").then(
                                () => {
                                    location.reload();
                                });

                        } else if (data == 0) {
                            Swal.fire("Menu !", "Permission not updated", "error").then(
                                () => {});
                        }
                    }
                });
            }
        });
        $(document).on("change", "#user_id", function(e) {
            $('form#menu_permission').submit();
        });
    </script>
@endpush
