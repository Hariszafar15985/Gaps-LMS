@extends('web.default.layouts.email')

@section('body')

<h3>Hi {{ $mailContent["organizationName"] }}</h3>
<p>This is to let you know that <b>{{ $mailContent["studentName"] . " (ID: " . $mailContent["studentId"] . ") "}}</b> has successfully completed their course <b>({{ $mailContent["courseName"] }})</b> </p>

@endsection
