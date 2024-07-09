@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/persian-datepicker/persian-datepicker.min.css"/>
    <link rel="stylesheet" href="/assets/default/css/css-stars.css">
    <style>
        .container.profile-container {
            min-width:80%;
        }
    </style>
@endpush


@section('content')

    <section class="site-top-banner position-relative">
        <img src="{{ $user->getCover() }}" class="img-cover" alt=""/>
    </section>


    <section class="container profile-container">
        <div class="rounded-lg shadow-sm px-25 py-20 px-lg-50 py-lg-35 position-relative user-profile-info bg-white">
            <div class="profile-info-box d-flex align-items-start justify-content-between">
                <div class="user-details d-flex align-items-center">
                    <div class="user-profile-avatar">
                        <img src="{{ $user->getAvatar() }}" class="img-cover" alt="{{ $user["full_name"] }}"/>

                        @if($user->offline)
                            <span class="user-circle-badge unavailable d-flex align-items-center justify-content-center">
                                <i data-feather="slash" width="20" height="20" class="text-white"></i>
                            </span>
                        @elseif($user->verified)
                            <span class="user-circle-badge has-verified d-flex align-items-center justify-content-center">
                                <i data-feather="check" width="20" height="20" class="text-white"></i>
                            </span>
                        @endif
                    </div>
                    <div class="ml-20 ml-lg-40">
                        <h1 class="font-24 font-weight-bold text-dark-blue">{{ $user["full_name"] }}</h1>
                        <span class="text-gray">{{ $user["headline"] }}</span>

                        <div class="stars-card d-flex align-items-center mt-5">
                            @include('web.default.includes.webinar.rate',['rate' => $userRates])
                        </div>

                        <div class="w-100 mt-10 d-flex align-items-center justify-content-center justify-content-lg-start">
                            <div class="d-flex flex-column followers-status">
                                <span class="font-20 font-weight-bold text-dark-blue">{{ ($user["role_name"] === \App\Models\Role::$user) ? trans('panel.status') : $userFollowers->count() }}</span>
                                <span class="font-14 text-gray">{{ ($user["role_name"] === \App\Models\Role::$user) ? ucfirst($user->status) : trans('panel.followers') }}</span>
                            </div>

                            <div class="d-flex flex-column ml-25 pl-5 following-status">
                                <span class="font-20 font-weight-bold text-dark-blue">{{ ($user["role_name"] === \App\Models\Role::$user) ? trans('panel.last_logged_on') : $userFollowers->count() }}</span>
                                <span class="font-14 text-gray">{{ ($user["role_name"] === \App\Models\Role::$user) ? "Test" : trans('panel.following') }}</span>
                            </div>
                        </div>

                        <div class="user-reward-badges d-flex align-items-center mt-15">
                            @if(!empty($userBadges))
                                @foreach($userBadges as $userBadge)
                                    <div class="mr-15" data-toggle="tooltip" data-placement="bottom" data-html="true" title="{!! (!empty($userBadge->badge_id) ? nl2br($userBadge->badge->description) : nl2br($userBadge->description)) !!}">
                                        <img src="{{ !empty($userBadge->badge_id) ? $userBadge->badge->image : $userBadge->image }}" width="32" height="32" alt="{{ !empty($userBadge->badge_id) ? $userBadge->badge->title : $userBadge->title }}">
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <div class="user-actions d-flex flex-column">
                    @if ($user["role_name"] === \App\Models\Role::$teacher)
                        <button type="button" id="followToggle" data-user-id="{{ $user['id'] }}" class="btn btn-{{ (!empty($authUserIsFollower) and $authUserIsFollower) ? 'danger' : 'primary' }} btn-sm">
                            @if(!empty($authUserIsFollower) and $authUserIsFollower)
                                {{ trans('panel.unfollow') }}
                            @else
                                {{ trans('panel.follow') }}
                            @endif
                        </button>

                        @if($user->public_message)
                            <button type="button" class="js-send-message btn btn-border-white rounded btn-sm mt-15">{{ trans('site.send_message') }}</button>
                        @endif
                    @endif
                    @if ($user->role_name === \App\Models\Role::$user)
                        @if ($user->getPurchaseAmounts() > 0) 
                            <a href="" onclick="return false;" class="cursor-pointer pe-none btn btn-border-white rounded btn-sm mt-15"><i data-feather="dollar-sign" width="30" height="30" class="mr-10"></i>{{ trans('panel.paid_student') }}</a> 
                        @else
                            <a href="" onclick="return false;" class="cursor-pointer pe-none btn btn-border-white rounded btn-sm mt-15"><i data-feather="wind" width="30" height="30" class="mr-10"></i>{{ trans('panel.free_student') }}</a>
                        @endif
                    @endif
                </div>
            </div>

            <div class="mt-40 border-top"></div>

            @if (!$user->isTeacher())
                <div class="row mt-30 w-100 d-flex align-items-center justify-content-around">
                    <div class="col-6 col-md-3 user-profile-state d-flex flex-column align-items-center">
                        <div class="state-icon orange p-15 rounded-lg">
                            <img src="/assets/default/img/profile/students.svg" alt="">
                        </div>
                        <span class="font-20 text-dark-blue font-weight-bold mt-5">{{ $user->students_count }}</span>
                        <span class="font-14 text-gray">{{ ($user->role_name === \App\Models\Role::$user) ? trans('panel.course_progress') : trans('quiz.students') }}</span>
                    </div>

                    <div class="col-6 col-md-3 user-profile-state d-flex flex-column align-items-center">
                        <div class="state-icon blue p-15 rounded-lg">
                            <img src="/assets/default/img/profile/webinars.svg" alt="">
                        </div>
                        <span class="font-20 text-dark-blue font-weight-bold mt-5">{{ count($webinars) }}</span>
                        <span class="font-14 text-gray">{{ ($user->role_name === \App\Models\Role::$user) ? trans('panel.expected_progress') : trans('webinars.classes') }}</span>
                    </div>

                    <div class="col-6 col-md-3 mt-20 mt-md-0 user-profile-state d-flex flex-column align-items-center">
                        <div class="state-icon green p-15 rounded-lg">
                            <img src="/assets/default/img/profile/reviews.svg" alt="">
                        </div>
                        <span class="font-20 text-dark-blue font-weight-bold mt-5">{{ ($user->role_name === \App\Models\Role::$user) ? $pendingQuizzesCount : $user->reviewsCount() }}</span>
                        <span class="font-14 text-gray">{{ ($user->role_name === \App\Models\Role::$user) ? trans('quiz.assessments_pending') : trans('product.reviews') }}</span>
                    </div>


                    <div class="col-6 col-md-3 mt-20 mt-md-0 user-profile-state d-flex flex-column align-items-center">
                        <div class="state-icon royalblue p-15 rounded-lg">
                            <img src="/assets/default/img/profile/appointments.svg" alt="">
                        </div>
                        <span class="font-20 text-dark-blue font-weight-bold mt-5">{{ ($user->role_name === \App\Models\Role::$user) ? count($appointments) : $appointments }}</span>
                        <span class="font-14 text-gray">{{ ($user->role_name === \App\Models\Role::$user) ? trans('panel.meetings_conducted') : trans('site.appointments') }}</span>
                    </div>

                </div>
            @endif
        </div>
    </section>

    <div class="container profile-container mt-30">
        <section class="rounded-lg border px-10 pb-35 pt-5 position-relative">
            <ul class="nav nav-tabs d-flex align-items-center px-20 px-lg-50 pb-15" id="tabs-tab" role="tablist">
                @if (!$user->isTeacher())
                    <li class="nav-item mr-20 mr-lg-40 mt-30">
                        <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (empty(request()->get('tab')) or request()->get('tab') == 'about') ? 'active' : ''  }}" id="about-tab" data-toggle="tab" href="#about" role="tab" aria-controls="about" aria-selected="true">Student Overview</a>
                    </li>
                    <li class="nav-item mr-20 mr-lg-40 mt-30">
                        <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'webinars') ? 'active' : ''  }}" id="webinars-tab" data-toggle="tab" href="#webinars" role="tab" aria-controls="webinars" aria-selected="false">Enrolment</a>
                    </li>
                    <li class="nav-item mr-20 mr-lg-40 mt-30">
                        <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'badges') ? 'active' : ''  }}" id="badges-tab" data-toggle="tab" href="#badges" role="tab" aria-controls="badges" aria-selected="false">Identification</a>
                    </li>
                @endif

                <li class="nav-item mr-20 mr-lg-40 mt-30">
                    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'appointments' || $user->isTeacher()) ? 'active' : ''  }}" id="appointments-tab" data-toggle="tab" href="#appointments" role="tab" aria-controls="appointments" aria-selected="false">{{ $user->isTeacher() ? trans('public.meetings') : trans('public.assessments') }}</a>
                </li>

                @if (!$user->isTeacher())
                    <li class="nav-item mr-20 mr-lg-40 mt-30">
                        <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'attendance') ? 'active' : ''  }}" id="attendance-tab" data-toggle="tab" href="#attendance" role="tab" aria-controls="attendance" aria-selected="false">Attendance</a>
                    </li>
                    <li class="nav-item mr-20 mr-lg-40 mt-30">
                        <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'documents') ? 'active' : ''  }}" id="documents-tab" data-toggle="tab" href="#documents" role="tab" aria-controls="documents" aria-selected="false">Documents</a>
                    </li>
                    <li class="nav-item mr-20 mr-lg-40 mt-30">
                        <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'notes') ? 'active' : ''  }}" id="notes-tab" data-toggle="tab" href="#notes" role="tab" aria-controls="notes" aria-selected="false">Notes</a>
                    </li>
                @endif
            </ul>

            <div class="tab-content" id="nav-tabContent">
                @if (!$user->isTeacher())
                    <div class="tab-pane fade px-20 px-lg-50 {{ (empty(request()->get('tab')) or request()->get('tab') == 'about') ? 'show active' : ''  }}" id="about" role="tabpanel" aria-labelledby="about-tab">
                        @include(getTemplate().'.user.profile_tabs.about')
                    </div>
                    <div class="tab-pane fade" id="webinars" role="tabpanel" aria-labelledby="webinars-tab">
                        @include(getTemplate().'.user.profile_tabs.webinars')
                    </div>
                    <div class="tab-pane fade" id="badges" role="tabpanel" aria-labelledby="badges-tab">
                        @include(getTemplate().'.user.profile_tabs.badges')
                    </div>
                @endif

                <div class="tab-pane fade px-20 px-lg-50 {{ (request()->get('tab') == 'appointments' || $user->isTeacher()) ? 'show active' : ''  }}" id="appointments" role="tabpanel" aria-labelledby="appointments-tab">
                    @if (!$user->isUser())
                        @include(getTemplate().'.user.profile_tabs.appointments')
                    @else
                        @if (isset($quizResultsListing) && strlen(trim($quizResultsListing)) > 0)
                            {!! $quizResultsListing !!}
                        @endif
                    @endif
                </div>
            </div>
        </section>
    </div>

    <div class="d-none" id="sendMessageModal">
        <h3 class="section-title after-line font-20 text-dark-blue mb-25">{{ trans('site.send_message') }}</h3>

        <form action="/users/{{ $user->id }}/send-message" method="post">
            {{ csrf_field() }}

            <div class="form-group">
                <label class="input-label">{{ trans('public.title') }}</label>
                <input type="text" name="title" class="form-control"/>
                <div class="invalid-feedback"></div>
            </div>

            <div class="form-group">
                <label class="input-label">{{ trans('public.email') }}</label>
                <input type="text" name="email" class="form-control"/>
                <div class="invalid-feedback"></div>
            </div>

            <div class="form-group">
                <label class="input-label">{{ trans('public.description') }}</label>
                <textarea name="description" class="form-control" rows="6"></textarea>
                <div class="invalid-feedback"></div>
            </div>

            <div class="form-group">
                <label class="input-label font-weight-500">{{ trans('site.captcha') }}</label>
                <div class="row align-items-center">
                    <div class="col">
                        <input type="text" name="captcha" class="form-control">

                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col d-flex align-items-center">
                        <img id="captchaImageComment" class="captcha-image" src="">

                        <button type="button" class="js-refresh-captcha btn-transparent ml-15">
                            <i data-feather="refresh-ccw" width="24" height="24" class=""></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-30 d-flex align-items-center justify-content-end">
                <button type="button" class="js-send-message-submit btn btn-primary">{{ trans('site.send_message') }}</button>
                <button type="button" class="btn btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
            </div>
        </form>
    </div>

@endsection

@push('scripts_bottom')
    <script>
        var unFollowLang = '{{ trans('panel.unfollow') }}';
        var followLang = '{{ trans('panel.follow') }}';
        var reservedLang = '{{ trans('meeting.reserved') }}';
        var availableDays = {{ json_encode($times) }};
        var messageSuccessSentLang = '{{ trans('site.message_success_sent') }}';
    </script>

    <script src="/assets/default/vendors/persian-datepicker/persian-date.js"></script>
    <script src="/assets/default/vendors/persian-datepicker/persian-datepicker.js"></script>

    <script src="/assets/default/js/parts/profile.min.js"></script>
@endpush
