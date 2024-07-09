@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ isset($mainHeading) ? $mainHeading : trans('admin/main.break_requests') }} </h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a>{{ trans('admin/main.break_requests') }}</a></div>
                <div class="breadcrumb-item"><a href="#">{{ trans('admin/main.list') }}</a></div>
            </div>
        </div>
    </section>
    @if(session()->has('success'))
        <div class="container-fluid">
            <div class="row">
                <div class="col col-12 alert alert-success">
                    {{ session()->get('success') }}
                </div>
            </div>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="container-fluid">
            <div class="row">
                <div class="col col-12 alert alert-warning">
                    {{ session()->get('error') }}
                </div>
            </div>
        </div>
    @endif


    <div class="card">
        {{-- <div class="card-header">
            @can('admin_users_export_excel')
                <a href="/admin/students/excel?{{ http_build_query(request()->all()) }}" class="btn btn-primary">{{ trans('admin/main.export_xls') }}</a>
            @endcan
            <div class="h-10"></div>
        </div> --}}

        <div class="card-body">
            <div class="table-responsive text-center">
                <table class="table table-striped font-14">
                    <tr>
                        <th>ID</th>
                        <th>{{ trans('admin/main.name') }}</th>
                        <th>{{ trans('admin/main.organization') }}</th>
                        <th>{{ trans('admin/main.organization_site') }}</th>
                        <th>{{ trans('admin/main.break_from') }}</th>
                        <th>{{ trans('admin/main.break_to') }}</th>
                        <th>{{ trans('admin/main.requested_by') }}</th>
                        <th>{{ trans('admin/main.request_date') }}</th>
                        <th>{{ trans('admin/main.status') }}</th>
                        <th class="text-right" width="120">{{ trans('admin/main.actions') }}</th>
                    </tr>

                    @foreach($userBreaks as $break)
                        @php
                            $user = $break->user;
                        @endphp

                        <tr>
                            <td>{{ $user->id }}</td>
                            <td class="text-left">
                                <div class="d-flex align-items-center">
                                    <figure class="avatar mr-2">
                                        <img src="{{ $user->getAvatar() }}" alt="{{ $user->full_name }}">
                                    </figure>
                                    <div class="media-body ml-1">
                                        <div class="mt-0 mb-1 font-weight-bold">{{ $user->full_name }}</div>

                                        @if($user->mobile)
                                            <div class="text-primary text-small font-600-bold">{{ $user->mobile }}</div>
                                        @endif

                                        @if($user->email)
                                            <div class="text-primary text-small font-600-bold">{{ $user->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="media-body">
                                    <div class="text-primary mt-0 mb-1 font-weight-bold">{{ $user->organization->full_name }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="media-body">
                                    @if (isset($user->organizationSites) && count($user->organizationSites) > 0)
                                        @foreach ($user->organizationSites as $organizationSite)
                                            <div class="text-primary mt-0 mb-1 font-weight-bold">{{ $organizationSite->name }}</div>
                                        @endforeach
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="media-body">
                                    {{\Carbon\Carbon::createFromFormat('Y-m-d', $break->from)}}
                                </div>
                            </td>
                            <td>
                                <div class="media-body">
                                    {{\Carbon\Carbon::createFromFormat('Y-m-d', $break->to)}}
                                </div>
                            </td>
                            <td>
                                <div class="media-body">
                                    {{$break->requestedBy->full_name}}
                                </div>
                            </td>
                            <td>
                                <div class="media-body">
                                    {{ $break->created_at }}
                                </div>
                            </td>
                            <td>
                                <div class="media-body
                                @if ($break->status === \App\Models\UserBreak::$status['pending'])
                                text-warning
                                @elseif  ($break->status === \App\Models\UserBreak::$status['approved'])
                                text-primary
                                @elseif  ($break->status === \App\Models\UserBreak::$status['rejected'])
                                text-danger
                                @endif
                                ">
                                    {{ ucfirst($break->status) }}
                                </div>
                            </td>

                            <td class="text-right mb-2" width="120">
                                @if ($break->status === \App\Models\UserBreak::$status['pending'])
                                <a href="{{route('admin.breakRequest.approve', ['id' => $break->id])}}" class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top" title="Approve">
                                    <i class="fas fa-check"></i>
                                </a>
                                <a href="{{route('admin.breakRequest.reject', ['id' => $break->id])}}" class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top" title="Reject">
                                    <i class="fa fa-ban"></i>
                                </a>
                                @endif
                                @include('admin.includes.delete_button',['url' => route('admin.breakRequest.delete', ['id' => $break->id]) , 'btnClass' => ''])
                            </td>

                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        <div class="card-footer text-center">
            {{ $userBreaks->links() }}
        </div>
    </div>


@endsection
