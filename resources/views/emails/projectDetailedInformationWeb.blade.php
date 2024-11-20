@extends('layouts.app3')
@php
use Carbon\Carbon;
@endphp
@section('content')
<div class="table-responsive pb-2">

  
    <table class="table" border="1" style="border-collapse: collapse">
        <thead>
            <tr>
                <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">User Name</th>
                @foreach ($headers as $header)
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>

            @if (isset($BodyDetails) && count($BodyDetails) > 0)
                @foreach ($BodyDetails as $data)
              
                    <tr>
                        <td style="text-align: center; padding: 5px;">                         
                                {{ $data['user'] }}                          
                        </td>
                        @foreach ($data['hourlyCount'] as $count)
                        <td style="text-align: center;padding: 5px;">{{ $count }}</td>
                    @endforeach
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" style="text-align: center; padding: 5px;">--No Records--</td>
                </tr>
            @endif
        </tbody>
    </table>
    <br>
    @include('emails.emailFooter')
</div>
@endsection
<style>
       * {
        font-family: Verdana, Arial, sans-serif;
        color: black;
    }

    table {
        font-size: small;
    }

    thead,
    th {
        background-color: #0e969c2b;
    }

    th,
    td {
        text-align: center;
        padding-right: 30px;
    }
</style>