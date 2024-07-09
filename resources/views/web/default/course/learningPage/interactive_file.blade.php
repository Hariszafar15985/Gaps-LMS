@extends(getTemplate() . '.layouts.app')
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css"
    integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">
@push('styles_top')
    {{-- <link href="/assets_1/default/css/font.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets_1/default/css/app.css"> --}}
    <style>
        body #filesContent:fullscreen {
            overflow: scroll !important;
            background-color: white;
            padding: 40px 20px;
        }

        body #filesContent:-ms-fullscreen {
            overflow: scroll !important;
        }

        body #filesContent:-webkit-full-screen {
            overflow: scroll !important;
        }

        body #filesContent:-moz-full-screen {
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
            top: 60%;
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

        #audioPlayButton {
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

        .audioAttached {
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

        .closeAudioBtn {
            margin-bottom: 35px;
        }

        .footer {
            margin-top: 250px;
        }
    </style>
@endpush
@section('content')
    <section class="cart-banner position-relative text-center">
        <div class="container h-50">
            <div class="row h-100 align-items-center justify-content-center text-center">
                <div class="col-12 col-md-9 col-lg-7">

                    <h1 class="font-30 text-white font-weight-bold">{{ $pageTitle }}</h1>

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
            <div id="fileContentOnFullScreenMode" class="col-12 col-lg-8 my-2 mt-30">
                {{-- @if (session('validityErrorMessage')) --}}
                {{-- <x-quiz-attempt-error error="{{ session('validityErrorMessage') }}" /> --}}
                {{-- @else --}}

                <div id="filesContent" class="play-iframe-page">
                    @if (!empty($iframe))
                        <div class="interactive-file-iframe"  width="100%" height="50%">

                            {!! $iframe !!}
                        </div>
                    @elseif (isset($path) && !empty($path))
                        <iframe src="{{ $path }}" frameborder="0" allowfullscreen class="interactive-file-iframe"
                            width="100%" height="100%"></iframe>
                    @elseif (isset($videoPath) && !empty($videoPath))
                        <video src="{{ asset($videoPath) }}" controls width="100%" allowfullscreen
                            class="interactive-file-iframe">
                            Your browser does not support the video tag.
                        </video>
                    @elseif (isset($filePath) && !empty($filePath))
                        <iframe src="https://docs.google.com/gview?url={{ urlencode($filePath) }}&embedded=true"
                            frameborder="0" allowfullscreen class="interactive-file-iframe" width="100%"
                            height="500"></iframe>
                    @endif

                    {{-- Teacher Section --}}
                    <div id="teacherSection" class="d-none">
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
                    <x-course-notes-component :file=$file />
                    <button id="helpButton" data-toggle="tooltip" data-placement="top" title="Help">
                        <i class="fa fa-question" aria-hidden="true"></i>
                    </button>

                </div>
                <div class="row mt-3">
                    <div class="col-12 text-right">
                        <button id="toggleScreenBtnFirst" class="btn btn-danger" data-toggle="tooltip"
                            title="Fullscreen">Full Screen</button>
                    </div>
                </div>

                {{-- @if ($previousFile || $nextFile)
                    <div class="row mt-3 mb-3">
                        <div class="col-md-7">

                        </div>

                        <div class="col-md-5">
                            <div class="row">
                                <div class="col-md-6">
                                    @if ($previousFile)
                                        <a href="{{ $course->getUrl() }}/file/{{ $previousFile->id }}/showHtml"
                                            class="btn btn-lg btn-primary">
                                            Previous
                                        </a>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    @if ($nextFile)
                                        <a href="{{ $course->getUrl() }}/file/{{ $nextFile->id }}/showHtml"
                                            class="btn btn-lg btn-primary">Next</a>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                @endif --}}
                <x-course.lesson-navigation-buttons :webinarId="$course->id" :previous=$previous :next=$next />
                <button id="toggleScreenBtn" data-toggle="tooltip" data-placement="top" title="Fullscreen"> <i
                        class="fas fa-2x fa-expand-arrows-alt"></i> </button>
                <button id="timesButton" data-toggle="tooltip" data-placement="top" title="Close Fullscreen">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>

            </div>
            {{-- sideBar section --}}
            <div id="sideBar" class="col-4 col-lg-4">

                <x-course.sidebar :webinarId="$course->id" />

            </div>
        </div>
    </section>
@endsection
@push('scripts_bottom')
    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });

        var elem = document.getElementById("filesContent");
        var toggleScreenBtn = document.getElementById("toggleScreenBtn");
        var toggleScreenBtnFirst = document.getElementById("toggleScreenBtnFirst");
        var fileContentOnFullScreenMode = document.getElementById("fileContentOnFullScreenMode");
        var teacherSection = document.getElementById("teacherSection");
        var iframe = document.querySelector(".interactive-file-iframe");

        // Variable to track full-screen state
        var isFullScreen = false;
        var isTeacherSectionVisible = false;

        function openFullscreen() {
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
                showHelpButton();
            } else if (elem.webkitRequestFullscreen) {
                /* Safari */
                elem.webkitRequestFullscreen();
                showHelpButton();
            } else if (elem.msRequestFullscreen) {
                /* IE11 */
                elem.msRequestFullscreen();
                showHelpButton();
            }
        }

        // Function to exit fullscreen
        function exitFullscreen() {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                /* Safari */
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                /* IE11 */
                document.msExitFullscreen();
            }
            // Remove the fullscreen class
            fileContentOnFullScreenMode.classList.remove("col-lg-8");
            teacherSection.style.width = "0%";
            iframe.style.width = "100%";
            isTeacherSectionVisible = false;
        }

        // Function to show the "Help" button
        function showHelpButton() {
            $("#helpButton").show();
        }

        // Function to hide the "Help" button
        function hideHelpButton() {
            // Check if not in full-screen mode before hiding
            if (!isFullScreen) {
                $("#helpButton").hide();
            }
        }

        // Function to toggle the layout when the help button is clicked
        function toggleLayout() {
            if (isFullScreen) {
                isTeacherSectionVisible = !isTeacherSectionVisible;
                if (isTeacherSectionVisible) {
                    teacherSection.classList.remove('d-none');
                    teacherSection.style.width = "25%";
                    iframe.style.width = "75%";
                    elem.classList.add('d-flex');
                } else {
                    teacherSection.classList.add('d-none');
                    teacherSection.style.width = "0%";
                    iframe.style.width = "100%";
                    elem.classList.remove('d-flex');
                }
            }
        }

        // Add a click event listener to the toggleScreenBtn
        toggleScreenBtn.addEventListener("click", function() {
            // Check if the document is currently in fullscreen
            if (
                document.fullscreenElement ||
                document.webkitFullscreenElement ||
                document.msFullscreenElement
            ) {
                // If in fullscreen, exit fullscreen mode
                isFullScreen = false;
                exitFullscreen();
            } else {
                // If not in fullscreen, enter fullscreen mode
                isFullScreen = true;
                openFullscreen();
            }
        });

        // Add a click event listener to the toggleScreenBtnFirst
        toggleScreenBtnFirst.addEventListener("click", function() {
            // Check if the document is currently in fullscreen
            if (
                document.fullscreenElement ||
                document.webkitFullscreenElement ||
                document.msFullscreenElement
            ) {
                // If in fullscreen, exit fullscreen mode
                isFullScreen = false;
                exitFullscreen();
            } else {
                // If not in fullscreen, enter fullscreen mode
                isFullScreen = true;
                openFullscreen();
            }
        });

        // Listen for fullscreen change events
        document.addEventListener("fullscreenchange", function() {
            isFullScreen = !!document.fullscreenElement;
            // Hide the "Help" button when exiting full-screen
            if (!isFullScreen) {
                hideHelpButton();
                teacherSection.classList.add('d-none');
                teacherSection.style.width = "0%";
                iframe.style.width = "100%";
                elem.classList.remove('d-flex');
                isTeacherSectionVisible = false;
            }
        });

        // Add a click event listener to the helpButton
        $("#helpButton").on('click', function() {
            toggleLayout();
        });

        $(document).on("keyup", function(e) {
            if (e.keyCode === 27) {
                // Handle Escape key press
                // Check if in full-screen mode, and hide the button and teacher section if not
                if (!isFullScreen) {
                    hideHelpButton();
                    hideTeacherSection();
                }
            }
        });
    </script>
@endpush
{{-- <html>
<head>
    <title>{{ $pageTitle ?? '' }}{{ !empty($generalSettings['site_name']) ? (' | '.$generalSettings['site_name']) : '' }}</title>

    <!-- General CSS File -->
    <link href="/assets_1/default/css/font.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets_1/default/css/app.css">
</head>
<body class="play-iframe-page">
@if (!empty($iframe))
    {!! $iframe !!}
@else
    <iframe src="{{ $path }}" frameborder="0" allowfullscreen class="interactive-file-iframe"></iframe>
@endif
</body>
</html> --}}
