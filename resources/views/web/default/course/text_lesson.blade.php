@extends(getTemplate() . '.layouts.app')
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css"
    integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">
@push('styles_top')
    <style>
        /* Removing the previously applied Text lesson content styling - Begin */
        /* section.container div.post-show h1,
        section.container div.post-show h1 * {
            font-family: Helvetica !important;
            font-size: 18px !important;
        }

        section.container div.post-show h2,
        section.container div.post-show h2 * {
            font-family: Helvetica !important;
            font-size: 17px !important;
        }

        section.container div.post-show h3,
        section.container div.post-show h3 * {
            font-family: Helvetica !important;
            font-size: 16px !important;
        }

        section.container div.post-show h4,
        section.container div.post-show h4 * {
            font-family: Helvetica !important;
            font-size: 15px !important;
        }

        section.container div.post-show h5,
        section.container div.post-show h5 *,
        section.container div.post-show h6,
        section.container div.post-show h6 *,
        section.container div.post-show p,
        section.container div.post-show p * {
            font-family: Helvetica !important;
            font-size: 14px !important;
        } */
        /* Removing the previously applied Text lesson content styling - End */

        body #textLessonContent:fullscreen {
            overflow: scroll !important;
            background-color: white;
            padding: 40px 20px;
        }

        body #textLessonContent:-ms-fullscreen {
            overflow: scroll !important;
        }

        body #textLessonContent:-webkit-full-screen {
            overflow: scroll !important;
        }

        body #textLessonContent:-moz-full-screen {
            overflow: scroll !important;
        }

        #helpButton {
            background: #fff;
            font-size: 21px !important;
            font-weight: 500;
            line-height: 38px;
            box-shadow: 0 0 25px rgb(23 23 23 / 25%);
            padding: 0;
            position: fixed;
            right: 45px;
            height: 75px;
            width: 75px;
            border-radius: 100%;
            text-decoration: none;
            top: 82%;
            z-index: 1029;
            letter-spacing: initial;
            display: none;
        }

        #timesButton {
            background: rgb(161, 16, 16);
            color: #fff;
            font-size: 21px !important;
            font-weight: 500;
            line-height: 38px;
            box-shadow: 0 0 25px rgb(23 23 23 / 25%);
            padding: 0;
            position: fixed;
            right: 45px;
            height: 75px;
            width: 75px;
            border-radius: 100%;
            text-decoration: none;
            top: 90%;
            z-index: 1029;
            letter-spacing: initial;
            display: none;
        }

        #toggleScreenBtn {
            background: rgb(67 212 119);
            color: #fff;
            font-size: 21px !important;
            font-weight: 500;
            line-height: 38px;
            box-shadow: 0 0 25px rgb(23 23 23 / 25%);
            padding: 0;
            position: fixed;
            right: 45px;
            height: 75px;
            width: 75px;
            border-radius: 100%;
            text-decoration: none;
            top: 85%;
            z-index: 1029;
            letter-spacing: initial;
        }

        #teacherSection {
            display: none;
        }

        #audioPlayButton{
            background: rgb(67 212 119);
            color: #fff;
            font-size: 21px !important;
            font-weight: 500;
            line-height: 38px;
            box-shadow: 0 0 25px rgb(23 23 23 / 25%);
            padding: 0;
            position: fixed;
            right: 45px;
            height: 75px;
            width: 75px;
            border-radius: 100%;
            text-decoration: none;
            top: 64%;
            z-index: 1029;
            letter-spacing: initial;
        }

        .audioAttached{
            font-size: 21px !important;
            font-weight: 500;
            line-height: 38px;
            padding: 0;
            position: fixed;
            right: 45px;
            text-decoration: none;
            top: 55%;
            z-index: 1029;
            letter-spacing: initial;
            /* display: none */
        }

        .closeAudioBtn{
            margin-bottom: 35px;
        }
    </style>
@endpush
@section('content')
    <section class="cart-banner position-relative text-center">
        <div class="container h-50">
            <div class="row h-100 align-items-center justify-content-center text-center">
                <div class="col-12 col-md-9 col-lg-7">

                    <h1 class="font-30 text-white font-weight-bold">{{ $textLesson->title }}</h1>

                    <div class="mt-20 font-16 font-weight-500 text-white">
                        <span>{{ trans('public.lesson') }} {{ $textLesson->order }}/{{ count($course->textLessons) }}
                        </span> | <span>{{ trans('public.study_time') }}: {{ $textLesson->study_time }}
                            {{ trans('public.min') }}</span>
                    </div>

                    <div class="mt-20 font-16 font-weight-500 text-white">
                        <span>{{ trans('product.course') }}: <a href="{{ $course->getUrl() }}"
                                class="font-16 font-weight-500 text-white text-decoration-underline">{{ $course->title }}</a></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container text-course-content-section mt-10 mt-md-40">
        <div class="row">
            <div class="col-12 col-lg-8 lessonContent my-2" id="lessonContent">
                @if(session('validityErrorMessage'))
                    <x-quiz-attempt-error error="{{ session('validityErrorMessage') }}"  />
                @else
                    <div class="post-show mt-30">
                        <div class="post-img pb-30">
                            <img src="{{ url($textLesson->image) }}" alt="{{ $textLesson->title }}" />
                        </div>

                        <div id="textLessonContent" class="row">

                            <div id="textContent" class="">
                                {!! nl2br($textLesson->content) !!}
                            </div>
                            {{-- Teacher section --}}
                            <div class="col-4" id="teacherSection">
                                <div
                                    class="rounded-lg shadow-sm mt-35 p-20 course-teacher-card d-flex align-items-center flex-column">
                                    <div class="teacher-avatar mt-5">
                                        <img src="{{ $course->teacher->getAvatar() }}" class="img-cover"
                                            alt="{{ $course->teacher->full_name }}">
                                    </div>
                                    <h3 class="mt-10 font-20 font-weight-bold text-secondary">{{ $course->teacher->full_name }}
                                    </h3>
                                    <span class="mt-5 font-weight-500 text-gray">{{ trans('product.product_designer') }}</span>

                                    @include('web.default.includes.webinar.rate', [
                                        'rate' => $course->teacher->rates(),
                                    ])

                                    <div class="user-reward-badges d-flex align-items-center mt-30">
                                        @foreach ($course->teacher->getBadges() as $userBadge)
                                            <div class="mr-15" data-toggle="tooltip" data-placement="bottom" data-html="true"
                                                title="{!! !empty($userBadge->badge_id) ? nl2br($userBadge->badge->description) : nl2br($userBadge->description) !!}">
                                                <img src="{{ !empty($userBadge->badge_id) ? $userBadge->badge->image : $userBadge->image }}"
                                                    width="32" height="32"
                                                    alt="{{ !empty($userBadge->badge_id) ? $userBadge->badge->title : $userBadge->title }}">
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-25 d-flex flex-row align-items-center justify-content-center w-100">
                                        <a href="{{ $course->teacher->getProfileUrl() }}" target="_blank"
                                            class="btn btn-sm btn-primary teacher-btn-action">{{ trans('public.profile') }}</a>

                                        @if (!empty($course->teacher->hasMeeting()))
                                            <a href="{{ $course->teacher->getProfileUrl() }}"
                                                class="btn btn-sm btn-primary teacher-btn-action ml-15">{{ trans('public.book_a_meeting') }}</a>
                                        @else
                                            <button type="button"
                                                class="btn btn-sm btn-primary disabled teacher-btn-action ml-15">{{ trans('public.book_a_meeting') }}</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <button id="toggleScreenBtn" data-toggle="tooltip" data-placement="top" title="Fullscreen"> <i
                                    class="fas fa-2x fa-expand-arrows-alt"></i> </button>
                            <button id="timesButton" data-toggle="tooltip" data-placement="top" title="Close Fullscreen">
                                <i class="fa fa-times" aria-hidden="true"></i>
                            </button>
                            <button id="helpButton" data-toggle="tooltip" data-placement="top" title="Help">
                                <i class="fa fa-question" aria-hidden="true"></i>
                            </button>
                            <x-course-notes-component :textLesson=$textLesson />
                                @if($audioFile)
                                <button id="audioPlayButton" data-audio="{{ asset("storage/audio_files/".$audioFile->file_name) }}" data-toggle="tooltip" data-placement="top" title="Play Lesson Audio">
                                    <i class="fa fa-volume-up" aria-hidden="true"></i>
                                 </button>
                                <div class="text-danger audioAttached">
                                    <audio class="" controls controlsList="nodownload" src="{{ asset("storage/audio_files/".$audioFile->file_name) }}"> </audio>
                                    <button class="btn btn-sm rounded closeAudioBtn btn-danger">X</button>
                                </div>
                                @endif
                        </div>
                    </div>
                    <x-course.lesson-navigation-buttons :webinarId="$course->id" :previous=$previous :next=$next/>
                @endif
            </div>

            <div class="col-12 col-lg-4">

                <x-course.sidebar :webinarId="$course->id" />

            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script>
        var learningToggleLangSuccess = '{{ trans('public.course_learning_change_status_success') }}';
        var learningToggleLangError = '{{ trans('public.course_learning_change_status_error ') }}';
    </script>

    <script src="/assets/default/js/parts/text_lesson.min.js"></script>

    <script>
        $(document).ready(function() {
            $(".audioAttached").hide()
            $("#audioPlayButton").on("click", function(){
                $(this).next().show()
            })
        })

        $(".closeAudioBtn").on("click", function(){
            $(".audioAttached").hide()
        })
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })

        var elem = document.getElementById("textLessonContent");

        function openFullscreen() {
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.webkitRequestFullscreen) {
                /* Safari */
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) {
                /* IE11 */
                elem.msRequestFullscreen();
            }
        }

        $("#helpButton").on('click', function() {
            $("#teacherSection").toggle();

            if ($("#textContent").hasClass("col-8")) {
                $("#textContent").removeClass("col-8")
            } else {
                $("#textContent").addClass("col-8")
            }
        })

        $("#timesButton").on('click', function() {
            document.exitFullscreen()
            $("#teacherSection").hide();
            $(this).hide()
            $("#helpButton").hide()
            $("#toggleScreenBtn").show()

            if ($("#textContent").hasClass("col-8")) {
                $("#textContent").removeClass("col-8")
            } else {
                // $("#textContent").addClass("col-8")
            }
        })

        $("#fullScreenBtn").on("click", function() {
            $("#helpButton").show()
            $("#timesButton").show()
            $("#toggleScreenBtn").toggle()
        })

        $("#toggleScreenBtn").on("click", function() {
            // $("#helpButton").show()
            // $("#timesButton").show()
            $("#fullScreenBtn").click()
            // $("#toggleScreenBtn").toggle()
            $(this).hide()

        })

        $(document).on("keyup", function(e) {
            if (e.keyCode === 27) {
                $("#helpButton").hide()
            }
        })
    </script>
@endpush
