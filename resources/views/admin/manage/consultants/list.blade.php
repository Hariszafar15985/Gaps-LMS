@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <style>
        .mt-5 {
            margin-top: 0px !important;
        }
        .table td {
            padding: 15px 10px !important;
        }
    </style>
@endpush

@section('content')

    <section class="section">
        <div class="section-header">
            <h1>{{ trans('public.consultants') }}</h1>
        </div>
    </section>

    <section>
        {{-- <h2 class="section-title">{{ trans('public.sub_managers') }}</h2> --}}

        {{-- <div class="activities-container mt-25 p-20 p-lg-35">
            <div class="row">
                <div class="col-4 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/48.svg" width="64" height="64" alt="">
                        <strong class="font-30 text-dark-blue font-weight-bold mt-5">{{ $users->count() }}</strong>
                        <span class="font-16 text-gray font-weight-500">{{ trans('quiz.students') }}</span>
                    </div>
                </div>

                <div class="col-4 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/49.svg" width="64" height="64" alt="">
                        <strong class="font-30 text-dark-blue font-weight-bold mt-5">{{ $activeCount }}</strong>
                        <span class="font-16 text-gray font-weight-500">{{ trans('public.active') }}</span>
                    </div>
                </div>

                <div class="col-4 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/60.svg" width="64" height="64" alt="">
                        <strong class="font-30 text-dark-blue font-weight-bold mt-5">{{ $inActiveCount }}</strong>
                        <span class="font-16 text-gray font-weight-500">{{ trans('public.inactive') }}</span>
                    </div>
                </div>

            </div>
        </div> --}}
    </section>

    <section class="mt-35">
        <h2 class="section-title">{{ trans('panel.filter_consultants') }}</h2>
        @include('admin.manage.filters')
    </section>
    <section class="mt-35">
        <h2 class="section-title">{{ trans('panel.consultants_list') }}</h2>

        @if(!empty($users) and !$users->isEmpty())
            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive">
                            <table class="table custom-table text-center ">
                                <thead>
                                <tr>
                                    <th class="text-left text-gray">{{ trans('auth.name') }}</th>
                                    <th class="text-left text-gray">{{ trans('auth.email') }}</th>
                                    <th class="text-center text-gray">{{ trans('admin/main.organization') }}</th>
                                    <th class="text-center text-gray">{{ trans('public.organization_sites') }}</th>
                                    <th class="text-center text-gray">{{ trans('public.phone') }}</th>
                                    <th class="text-center text-gray">{{ trans('public.date') }}</th>
                                    <th width="120">{{ trans('admin/main.actions') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($users as $user)

                                    <tr>
                                        <td class="text-left">
                                            <div class="user-inline-avatar d-flex align-items-center">
                                                <div class="avatar">
                                                    <img src="{{ $user->getAvatar() }}" class="img-cover" alt="">
                                                </div>
                                                <div class=" ml-5">
                                                    <span class="d-block text-dark-blue font-weight-500">{{ $user->full_name }}</span>
                                                    <span class="mt-5 d-block font-12 text-{{ ($user->status == 'active') ? 'gray' : 'danger' }}">{{ ($user->status == 'active') ? trans('public.active') : trans('public.inactive') }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-left">
                                            <div class="">
                                                <span class="d-block text-dark-blue font-weight-500">{{ $user->email }}</span>
                                                <span class="mt-5 d-block font-12 text-gray">id : {{ $user->id }}</span>
                                            </div>
                                        </td>
                                        <td class="text-left">
                                            <div class="">
                                                @if(isset($user->organization->id))
                                                    <span class="d-block text-dark-blue font-weight-500">{{ $user->organization->full_name }}</span>
                                                    <span class="mt-5 d-block font-12 text-gray">id : {{ $user->organization->id }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            @php
                                                $userSites = $user->organizationSites;
                                                $count = 0;
                                                $sitesCount = count($userSites);
                                            @endphp
                                            @foreach ($userSites as $site)
                                                @php $count++; @endphp
                                                <span class="text-dark-blue font-weight-500">{{ $site->name }}</span>
                                                @if($count < $sitesCount)
                                                    , <br />
                                                @endif
                                            @endforeach
                                        </td>
                                        <td class="align-middle">
                                            <span class="text-dark-blue font-weight-500">{{ $user->mobile }}</span>
                                        </td>
                                        <td class="text-dark-blue font-weight-500 align-middle">{{ dateTimeFormat($user->created_at,'Y M j | H:i') }}</td>

                                        <td class="text-center mb-2" width="120">
                                            @can('admin_users_impersonate')
                                                <a href="/admin/users/{{ $user->id }}/impersonate" target="_blank" class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.login') }}">
                                                    <i class="fas fa-sign-in-alt"></i>
                                                </a>
                                            @endcan

                                            @can('admin_users_edit')
                                                <a href="/admin/users/{{ $user->id }}/edit" class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endcan

                                            @can('admin_users_delete')
                                                @include('admin.includes.delete_button',['url' => '/admin/users/'.$user->id.'/delete' , 'btnClass' => ''])
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        @else

            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'studentt.png',
                'title' => trans('panel.manager_no_result'),
                'hint' =>  nl2br(trans('panel.manager_no_result_hint')),
                'btn' => ['url' => route('panel.manage.create.user', ['user_type' => 'sub_managers']),'text' => trans('panel.add_a_sub_manager')]
            ])
        @endif

    </section>

    @if ((!isset($noPagination) || !$noPagination) && (isset($users) && count($users) > 0))
        <div class="my-30">
            {{-- $users->links('vendor.pagination.panel') --}}
            {{ $users->links() }}
        </div>
    @endif
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
@endpush
