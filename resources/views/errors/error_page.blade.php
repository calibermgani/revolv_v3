@extends('layouts.app3')
@section('subheader')

<div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <div class="d-flex align-items-center flex-wrap mr-2">



            <div class="subheader-separator subheader-separator-ver mt-2 mb-2 mr-4 bg-gray-200">

            </div>



        </div>

    </div>
</div>


<div class="error error-5 d-flex flex-row-fluid bgi-size-cover bgi-position-center"  style="margin:80px 150px 0px 150px;border-radius: 11px;
border: 2px solid #ebe5f8;">
    <!--begin::Content-->
    <div class="container d-flex flex-row-fluid flex-column justify-content-md-center p-12">
        <h1 class="error-title font-weight-boldest mt-10 mt-md-0 mb-6" style="color:#ee2a7b">{{ $error }}</h1>
        {{-- <p class="font-size-h4 text-muted" style="color:#ee2a7b">{{ $error }}</p> --}}
    </div>
    <!--end::Content-->
</div>

@endsection
@section('content')

@endsection


