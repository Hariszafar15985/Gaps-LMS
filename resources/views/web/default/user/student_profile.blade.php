@extends(getTemplate().'.layouts.app')

@push('styles_top')
    {{-- <link rel="stylesheet" href="/assets/vendors/fontawesome/css/all.min.css"> --}}
    <link rel="stylesheet" href="/assets/default/vendors/persian-datepicker/persian-datepicker.min.css"/>
    <link rel="stylesheet" href="/assets/vendors/fontawesome/css/all.min.css"/>
    <link rel="stylesheet" href="/assets/default/css/css-stars.css">
    <style>
        .container.profile-container {
            min-width:{{($authUser->isAdmin()) ? '90%' : '82%'}};
        }
        .text-blue {
            color: #6777ef !important;
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
                                <span class="font-14 text-gray">{{ ($user["role_name"] === \App\Models\Role::$user) ? (isset($lastLogin) ? date('d-M-Y |  h:i a T', strtotime($lastLogin->created_at))." from ".long2ip($lastLogin->ip) : "-" ) : trans('panel.following') }}</span>
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
                        {{-- @if ($user->getPurchaseAmounts() > 0)
                            <a href="" onclick="return false;" class="cursor-pointer pe-none btn btn-border-white rounded btn-sm mt-15"><i data-feather="dollar-sign" width="30" height="30" class="mr-10"></i>{{ trans('panel.paid_student') }}</a>
                        @else
                            <a href="" onclick="return false;" class="cursor-pointer pe-none btn btn-border-white rounded btn-sm mt-15"><i data-feather="wind" width="30" height="30" class="mr-10"></i>{{ trans('panel.free_student') }}</a>
                        @endif --}}
                        @if ($user->getPurchaseAmounts() > 0)
                            @if (empty($pendingPayments))
                                <a href="" onclick="return false;"
                                    class="cursor-pointer pe-none bg-success btn btn-border-white rounded btn-sm mt-15">{{ trans('panel.paid') }}
                                    {{-- <i data-feather="dollar-sign" width="30" height="30" class="mr-10"></i> --}}
                                </a>
                            @else
                                <div class="dropleft">
                                    <a href="" onclick="return false;" id="pendingCoursePaymentsBtn" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false"
                                        class="cursor-pointer pe-none bg-warning text-white btn btn-border-white rounded btn-sm mt-15 dropdown-toggle"
                                    >
                                        <i data-feather="dollar-sign" width="30" height="30" class="mr-10"></i>{{ trans('panel.awaiting_payment') }}
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="pendingCoursePaymentsBtn">
                                        @php
                                            $iteration = 1;
                                            $pendingPaymentsCount = count($pendingPayments);
                                        @endphp
                                        @foreach ( $pendingPayments as $pendingPayment)
                                            <a class="dropdown-item" href="">
                                                <strong>{{ trans('panel.course') }}:</strong> {{ $pendingPayment['webinar'] }}<br />
                                                <strong>{{ trans('panel.amount') }}:</strong> ({{ $pendingPayment['amount'] }})<br />
                                                <strong>{{ trans('panel.date_purchased') }}:</strong> {{ $pendingPayment['date_purchased'] }}<br />
                                                <strong>{{ trans('panel.payment_status') }}:</strong> {{ ucwords($pendingPayment['payment_status']) }}
                                            </a>
                                            @if ($pendingPaymentsCount > 1 && $iteration < $pendingPaymentsCount)
                                                <hr />
                                            @endif
                                        @endforeach
                                      </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <a href="" onclick="return false;" class="cursor-pointer pe-none btn btn-border-white rounded btn-sm mt-15"><i data-feather="wind" width="30" height="30" class="mr-10"></i>{{ trans('panel.free_student') }}</a>
                        @endif
                    @endif
                </div>
            </div>

            <div class="mt-40 border-top"></div>

            <div class="row mt-30 w-100 d-flex align-items-center justify-content-around">
                <div class="col-6 col-md-3 user-profile-state d-flex flex-column align-items-center">
                    <div class="state-icon orange p-15 rounded-lg">
                        <img src="/assets/default/img/profile/students.svg" alt="">
                    </div>
                    <span class="font-20 text-dark-blue font-weight-bold mt-5">{{ $user->students_count }}</span>
                    <span class="font-14 text-gray">{{ ($user->role_name === \App\Models\Role::$user) ? trans('panel.course_progress') : $userFollowers->count() }}</span>
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
        </div>
    </section>

    <div class="container profile-container mt-30">
        <section class="rounded-lg border px-10 pb-35 pt-5 position-relative">
            <ul class="nav nav-tabs d-flex align-items-center px-20 px-lg-50 pb-15" id="tabs-tab" role="tablist">
                <li class="nav-item mr-20 mr-lg-40 mt-30">
                    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (empty(request()->get('tab')) or request()->get('tab') == 'about') ? 'active' : ''  }}" id="about-tab" data-toggle="tab" href="#about" role="tab" aria-controls="about" aria-selected="true">{{ trans("public.student_overview") }}</a>
                </li>
                <li class="nav-item mr-20 mr-lg-40 mt-30">
                    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'courseinfo') ? 'active' : ''  }}" id="courseinfo-tab" data-toggle="tab" href="#courseinfo" role="tab" aria-controls="courseinfo" aria-selected="false">{{ trans("public.course_information") }}</a>
                </li>
                <li class="nav-item mr-20 mr-lg-40 mt-30">
                    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'enrollment') ? 'active' : ''  }}" id="enrollment-tab" data-toggle="tab" href="#enrollment" role="tab" aria-controls="enrollment" aria-selected="false">{{ trans("public.enrollment") }}</a>
                </li>
                <li class="nav-item mr-20 mr-lg-40 mt-30">
                    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'badges') ? 'active' : ''  }}" id="badges-tab" data-toggle="tab" href="#badges" role="tab" aria-controls="badges" aria-selected="false">{{ trans("public.identification") }}</a>
                </li>

                {{-- <li class="nav-item mr-20 mr-lg-40 mt-30">
                    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'appointments') ? 'active' : ''  }}" id="appointments-tab" data-toggle="tab" href="#appointments" role="tab" aria-controls="appointments" aria-selected="false">{{ trans("public.assessments") }}</a>
                </li> --}}
                <li class="nav-item mr-20 mr-lg-40 mt-30">
                    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'assessments') ? 'active' : ''  }}" id="assessments-tab" data-toggle="tab" href="#assessments" role="tab" aria-controls="assessments" aria-selected="false">{{ trans("public.assessments") }}</a>
                </li>
                <li class="nav-item mr-20 mr-lg-40 mt-30">
                    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'training') ? 'active' : ''  }}" id="training-tab" data-toggle="tab" href="#training" role="tab" aria-controls="training" aria-selected="false">{{ trans("public.trainings") }}</a>
                </li>

                <li class="nav-item mr-20 mr-lg-40 mt-30">
                    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'attendance') ? 'active' : ''  }}" id="attendance-tab" data-toggle="tab" href="#attendance" role="tab" aria-controls="attendance" aria-selected="false">{{ trans("public.attendance") }}</a>
                </li>
                <li class="nav-item mr-20 mr-lg-40 mt-30">
                    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'documents') ? 'active' : ''  }}" id="documents-tab" data-toggle="tab" href="#documents" role="tab" aria-controls="documents" aria-selected="false">{{ trans("public.documents") }}</a>
                </li>
                <li class="nav-item mr-20 mr-lg-40 mt-30">
                    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'notes') ? 'active' : ''  }}" id="notes-tab" data-toggle="tab" href="#notes" role="tab" aria-controls="notes" aria-selected="false">{{ trans("public.notes") }}</a>
                </li>
                <li class="nav-item mr-20 mr-lg-40 mt-30">
                    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'auditTrail') ? 'active' : ''  }}" id="auditTrail-tab" data-toggle="tab" href="#auditTrail" role="tab" aria-controls="auditTrail" aria-selected="false">{{ trans("public.audit_trail") }}</a>
                </li>
                @if ($authUser->isAdmin())
                    <li class="nav-item mr-20 mr-lg-40 mt-30">
                        <a class="position-relative text-blue font-weight-500 font-16 {{ (request()->get('tab') == 'userBreaks') ? 'active' : ''  }}" id="userBreaks-tab" data-toggle="tab" href="#userBreaks" role="tab" aria-controls="userBreaks" aria-selected="false">{{ trans("public.user_breaks") }}</a>
                    </li>
                    <li class="nav-item mr-20 mr-lg-40 mt-30">
                        <a class="position-relative text-blue font-weight-500 font-16 {{ (request()->get('tab') == 'placementNote') ? 'active' : ''  }}" id="placementNote-tab" data-toggle="tab" href="#placementNote" role="tab" aria-controls="placementNote" aria-selected="false">{{ trans("public.placement_notes") }}</a>
                    </li>
                @endif
            </ul>

            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade px-20 px-lg-50 {{ (empty(request()->get('tab')) or request()->get('tab') == 'about') ? 'show active' : ''  }}" id="about" role="tabpanel" aria-labelledby="about-tab">
                    @include(getTemplate().'.user.profile_tabs.about')
                </div>
                <div class="tab-pane fade" id="courseinfo" role="tabpanel" aria-labelledby="courseinfo-tab">
                    @include(getTemplate().'.user.profile_tabs.webinars')
                </div>
                <div class="tab-pane fade" id="enrollment" role="tabpanel" aria-labelledby="enrollment-tab">
                    @include(getTemplate().'.user.profile_tabs.enrollment')
                </div>
                <div class="tab-pane fade" id="badges" role="tabpanel" aria-labelledby="badges-tab">
                    @include(getTemplate().'.user.profile_tabs.badges')
                </div>

                <div class="tab-pane fade px-20 px-lg-50 {{ (request()->get('tab') == 'assessments') ? 'show active' : ''  }}" id="assessments" role="tabpanel" aria-labelledby="assessments-tab">
                    @if ($user->role_name !== \App\Models\Role::$user)
                        @include(getTemplate().'.user.profile_tabs.assessments')
                    @else
                        @if (isset($quizResultsListing) && strlen(trim($quizResultsListing)) > 0)
                            {!! $quizResultsListing !!}
                        @endif
                    @endif
                </div>
                <div class="tab-pane fade px-20 px-lg-50 {{ (request()->get('tab') == 'training') ? 'show active' : ''  }}" id="training" role="tabpanel" aria-labelledby="training-tab">
                    @include(getTemplate().'.user.profile_tabs.trainings',[$user])
                </div>
                <div class="tab-pane fade px-20 px-lg-50 {{ (request()->get('tab') == 'attendance') ? 'show active' : ''  }}" id="attendance" role="tabpanel" aria-labelledby="attendance-tab">
                    @include(getTemplate().'.user.profile_tabs.attendance')
                </div>
                <div class="tab-pane fade px-20 px-lg-50 {{ (request()->get('tab') == 'documents') ? 'show active' : ''  }}" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                    @include(getTemplate().'.user.profile_tabs.documents')
                </div>
                <div class="tab-pane fade px-20 px-lg-50 {{ (request()->get('tab') == 'notes') ? 'show active' : ''  }}" id="notes" role="tabpanel" aria-labelledby="notes-tab">
                    @include(getTemplate().'.user.profile_tabs.notes')
                </div>
                <div class="tab-pane fade px-20 px-lg-50 {{ (request()->get('tab') == 'auditTrail') ? 'show active' : ''  }}" id="auditTrail" role="tabpanel" aria-labelledby="auditTrail-tab">
                    @include(getTemplate().'.user.profile_tabs.audit')
                </div>
                @if ($authUser->isAdmin())
                    <div class="tab-pane fade px-20 px-lg-50 {{ (request()->get('tab') == 'userBreaks') ? 'show active' : ''  }}" id="userBreaks" role="tabpanel" aria-labelledby="userBreaks-tab">
                        @include(getTemplate().'.user.profile_tabs.user_break')
                    </div>
                    <div class="tab-pane fade px-20 px-lg-50 {{ (request()->get('tab') == 'placementNote') ? 'show active' : ''  }}" id="placementNote" role="tabpanel" aria-labelledby="placementNote-tab">
                        @include(getTemplate().'.user.profile_tabs.placement_notes')
                    </div>
                @endif

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

    @include('admin.users.modals.upload_documents')

@endsection

@push('scripts_bottom')
    <script>
        var unFollowLang = '{{ trans('panel.unfollow') }}';
        var followLang = '{{ trans('panel.follow') }}';
        var reservedLang = '{{ trans('meeting.reserved') }}';
        var availableDays = {{ json_encode($times) }};
        var messageSuccessSentLang = '{{ trans('site.message_success_sent') }}';

        $(document).on('click', '#btn-upload-document', function(e){
            e.preventDefault();

            $('#uploadDocumentModal').modal('show');

            /*Swal.fire({
                html: $('#uploadDocumentModal').html(),
                showCancelButton: false,
                showConfirmButton: false,
                customClass: {
                    content: 'p-0 text-left',
                },
                width: '48rem',
                onOpen: () => {

                    var $modal = $('#addTestLessonModal');
                    var summernoteTarget = $modal.find('.js-content-summernote');
                    if (summernoteTarget.length) {
                        summernoteTarget.summernote({
                            tabsize: 2,
                            height: 400,
                            callbacks: {
                                onChange: function (contents, $editable) {
                                    $modal.find('.js-hidden-content-summernote').val(contents);
                                }
                            }
                        });
                    }
                }
            });*/
        });

    </script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function uploadDocument() {
            var url = "{{ url('/users/upload-user-document')}}";
            var uid = $('#upload-document-form input[name="user_id"]').val();
            var formData = new FormData();
            formData.append("document", $("#ud-document")[0].files[0]);
            formData.append("title", $('#ud-title').val());
            formData.append("type", $('#ud-type').val());
            formData.append("description", $('#ud-description').val());
            formData.append("user_id", uid);

            $.ajax({
                url: url,
                method: "POST",
                data: formData,
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function () {
                    //$('#uploaded_image').html("<label class='text-success'>Image Uploading...</label>");
                },
                success: function (data) {
                    if (data == 1) {
                        $('#uploadDocumentModal').modal('hide');
                        Swal.fire(
                            'Good job!',
                            'Document uploaded successfully!',
                            'success'
                        );
                        var url = "{{ url('/users') }}";
                        window.location = url + '/' + uid+'/profile?tab=documents';
                    } else {
                        //console.log(data)
                    }
                    return false;
                }
            });
        }

        function deleteUserDocument(id) {
            var url = "{{ url('/users/delete-user-document')}}";
            $.ajax({
                type: "delete",
                data: { 'did': id },
                url: url,
                dataType: "json",
                success: function(data) {
                    if (data.res == 1) {
                        Swal.fire(
                            'Good job!',
                            'Document deleted successfully!',
                            'success'
                        );
                        $('#user-documents-div #row-'+id).remove();
                    } else {

                    }
                    return false;
                },
                error: function() {
                }
            });
        }

        $(".stdVisibility").on("click", function() {

            var dataId = $(this).attr("data-id");
            var element = $(this);

            visibleUserDocument(dataId, element)
        })

        /**
         * visibleUserDocument fuction is created to disable/enable the student document visibility
         */
        function visibleUserDocument(id, element) {


            var url = "{{ route('student.document.visiblity') }}";
            $.ajax({
                type: "post",
                data: { 'did': id },
                url: url,
                dataType: "json",
                success: function(data) {
                    if (data == 1) {

                        if(element.attr("data-visibility") == 0) {
                            element.html("")
                            element.append("<i class='fa fa-eye'>")
                            element.attr("title", "Click to hide")
                        }else{
                            element.html("")
                            element.append("<i class='fa fa-eye-slash'>")
                            element.attr("title", "Click to show")
                        }

                        $.toast({
                            heading: "Success",
                            text: "Done successfully!",
                            bgColor: '#43d477',
                            textColor: 'white',
                            hideAfter: 10000,
                            position: 'bottom-right',
                            icon: 'success'
                        });
                    } else {

                    }
                    return false;
                },
                error: function() {
                }
            });
        }

    </script>

    <script src="/assets/default/vendors/persian-datepicker/persian-date.js"></script>
    <script src="/assets/default/vendors/persian-datepicker/persian-datepicker.js"></script>

    <script src="/assets/default/js/parts/profile.min.js"></script>
@endpush
