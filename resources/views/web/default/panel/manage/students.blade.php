@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')

    <section>
        <h2 class="section-title">{{ trans('quiz.students') }}</h2>

        <div class="activities-container mt-25 p-20 p-lg-35">
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
        </div>
    </section>

    <section class="mt-35">
        <h2 class="section-title">{{ trans('panel.filter_students') }}</h2>

        @include('web.default.panel.manage.filters')
    </section>

    @if (session('success'))
        <section class="mt-20">
            <div class="row">
                <div class="col-sm-12">
                    <div class="alert  alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                    </div>
                </div>
            </div>
        </section>
    @endif
    @if(isset($NewUser))
        <section class="mt-20">
            <div class="alert alert-success alert-dismissible fade show">
                <h4 class="alert-heading mb-2">{{trans('public.student_created')}}</h4>
                <div class="row">
                    <div class="col-2 font-weight-bold" >
                        {{ trans('public.student_id') }}
                    </div>
                    <div class="col" >
                        {{ $NewUser->id }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-2 font-weight-bold" >
                        {{ trans('auth.name') }}
                    </div>
                    <div class="col" >
                        {{ $NewUser->full_name }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-2 font-weight-bold" >
                        {{ trans('public.consultant') }}
                    </div>
                    <div class="col" >
                        {{ $NewUser->manager->full_name }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-2 font-weight-bold" >
                        {{ trans('public.organization_site') }}
                    </div>
                    <div class="col" >
                        {{ $NewUser->organizationSites->first()->id }}
                    </div>
                </div>
            </div>
        </section>
    @endif
    @if (session('error'))
        <section class="mt-20">
            <div class="row">
                <div class="col-sm-12">
                    <div class="alert  alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                    </div>
                </div>
            </div>
        </section>
    @endif


    <section class="mt-35">
        <div class="row">
            <div class="col-6">
                <h2 class="section-title">{{ trans('panel.students_list') }}</h2>
            </div>
            @if(env("EXPORT_STUDENTS_FROM_PANEL", false) == true)
            <div align="right" class="col-6">
                <x-export-to-c-s-v btnText="Export Students" route="export.students" query="{!! request()->getQueryString() !!}" />
            </div>
            @endif
        </div>
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
                                    <th class="text-center text-gray">{{ trans('public.phone') }}</th>
                                    <th class="text-center text-gray">{{ trans('public.consultant') }}</th>
                                    <th class="text-center text-gray">{{ trans('public.organization_site') }}</th>
                                    <th class="text-center text-gray">{{ trans('webinars.webinars') }}</th>
                                    <th class="text-center text-gray">{{ trans('panel.progress') }}</th>
                                    <th class="text-center text-gray">{{ trans('panel.student_progress') }}</th>
                                    <th class="text-center text-gray">{{ trans('panel.expected_progress') }}</th>
                                    <th class="text-center text-gray">{{ trans('quiz.quizzes') }}</th>
                                    <th class="text-center text-gray">{{ trans('panel.certificates') }}</th>
                                    <th class="text-center text-gray">{{ trans('public.date') }}</th>
                                    <th></th>
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
                                        <td class="align-middle">
                                            <span class="text-dark-blue font-weight-500">{{ $user->mobile }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="text-dark-blue font-weight-500">{{
                                                !empty($user->manager->full_name) ? $user->manager->full_name : ""
                                                }}</span>
                                        </td>
                                        <td class="align-middle">
                                            @foreach($user->organizationSites as $userSite)
                                                <span class="text-dark-blue font-weight-500">{{ $userSite->name }}</span>
                                            @endforeach
                                        </td>
                                        <td class="align-middle">
                                            <span class="text-dark-blue font-weight-500">{{ count($user->getPurchasedCoursesIds()) }}</span>
                                        </td>
                                        @php
                                            //dd($user->getPurchasedCourses());
                                            $classesPurchased = $user->getPurchasedCourses();
                                            $studentProgress = 0;
                                            $expectedProgress = 0;
                                            if (isset($classesPurchased) && count($classesPurchased)) {
                                                $behindProgress = $user->isBehindProgress();
                                                foreach ($classesPurchased as $classPurchased) {
                                                    $studentProgress = $studentProgress + $classPurchased->webinar->getProgress($user->id);
                                                    $expectedProgress = $expectedProgress + $classPurchased->webinar->getExpectedProgress($user->id);
                                                }

                                                $studentProgress = round($studentProgress / count($classesPurchased), 2);
                                                $expectedProgress = round($expectedProgress / count($classesPurchased), 2);                                            }
                                        @endphp
                                        <td class="align-middle">
                                            @if (isset($classesPurchased) && count($classesPurchased))
                                                <span class="{{(isset($behindProgress) && $behindProgress) ? "text-danger" : "text-primary"}} mt-0 mb-1 font-weight-bold">
                                                    {{ (isset($behindProgress) && $behindProgress) ? trans("admin/main.behind_progress") : trans("admin/main.on_schedule") }}
                                                </span>
                                            @else
                                                <span class="text-primary mt-0 mb-1 font-weight-bold">
                                                    -
                                                </span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <span class="text-dark-blue">{{ ($studentProgress ?? 0) . "%" }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="text-dark-blue">{{ ($expectedProgress ?? 0) . "%" }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="text-dark-blue font-weight-500">{{ count($user->getActiveQuizzesResults()) }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="text-dark-blue font-weight-500">{{ count($user->certificates) }}</span>
                                        </td>
                                        <td class="text-dark-blue font-weight-500 align-middle">{{ dateTimeFormat($user->created_at,'Y M j | H:i') }}</td>

                                        <td class="text-right align-middle">
                                            <div class="btn-group dropdown table-actions">
                                                <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a href="/panel/manage/students/{{ $user->id }}/enrol" class="btn-transparent webinar-actions d-block mt-10">{{ trans('panel.enrol')}}</a>
                                                    <a href="{{route('panel.break.create', ['user_id' => $user->id])}}" class="btn-transparent webinar-actions d-block mt-10">{{ trans('panel.add_break') }}</a>
                                                    @if ($authUser->role_name === \App\Models\Role::$teacher)
                                                        <a href="{{ $user->getProfileUrl() }}" class="btn-transparent webinar-actions d-block mt-10">{{ trans('public.profile') }}</a>
                                                    @endif
                                                    <a href="/panel/manage/students/{{ $user->id }}/edit" class="btn-transparent webinar-actions d-block mt-10">{{ trans('public.edit') }}</a>
                                                    @if($authUser->canDeleteStudent())
                                                        <a href="/panel/manage/students/{{ $user->id }}/delete" class="webinar-actions d-block mt-10 delete-action">{{ trans('public.delete') }}</a>
                                                    @endif
                                                    <a href="/panel/manage/students/{{ $user->id }}/courses" class="btn-transparent webinar-actions d-block mt-10">Courses </a>
                                                </div>
                                            </div>
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
                'title' => trans('panel.students_no_result'),
                'hint' =>  nl2br(trans('panel.students_no_result_hint')),
                'btn' => ['url' => '/panel/manage/students/new','text' => trans('panel.add_an_student')]
            ])
        @endif

    </section>

    @if (!isset($noPagination) || !$noPagination)
        <div class="my-30">
            {{ $users->links('vendor.pagination.panel') }}
        </div>
    @endif
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
@endpush
