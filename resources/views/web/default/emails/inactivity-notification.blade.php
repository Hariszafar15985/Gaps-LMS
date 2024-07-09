
@extends("web.default.layouts.std-inactive-email")
@section('body')
    <!-- content -->
    <span class="h1">{{ $notification['title'] }}</span>
    {!! $notification['message'] !!}
    <p>{{ trans('notification.email_ignore_msg') }}</p>

@endsection
