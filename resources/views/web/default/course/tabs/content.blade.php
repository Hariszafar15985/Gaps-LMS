{{-- Progress --}}
@if ($courseContentRequestedOnly and ($hasBought or $course->isWebinar()))
    @php
        $percent = $course->getProgress($user->id);
        $expectedProgress = $course->getExpectedProgress($user->id);
        $expectedProgressPercent = 0;
        if ($expectedProgress > $percent) {
            $expectedProgressPercent = $expectedProgress - $percent;
        }
    @endphp

    <div class="mt-20 d-flex align-items-center">
        <h4> {{ trans('public.progress') }} </h4>
        <div class="progress course-progress flex-grow-1 shadow-xs rounded-sm">
            <span class="progress-bar rounded-sm {{ $percent >= $expectedProgress ? 'bg-primary' : 'bg-warning' }}"
                style="width: {{ $percent }}%"><span class="popOver" data-toggle="tooltip" data-placement="top"
                    title="85%"> </span></span>
            @if (isset($expectedProgressPercent) && $expectedProgressPercent > 0)
                <span class="progress-bar rounded-right-sm bg-danger" style="width: {{ $expectedProgressPercent }}%"><span
                        class="popOver" data-toggle="tooltip" data-placement="top" title="85%"> </span></span>
            @endif
        </div>

        <div class="ml-15 font-14 font-weight-500">
            <span class="font-14 font-weight-500">
                @if ($course->isWebinar())
                    @if ($course->isProgressing() and $hasBought)
                        {{ trans('public.course_learning_passed', ['percent' => $percent]) }}
                    @else
                        {{ $course->sales_count }}/{{ $course->capacity }} {{ trans('quiz.students') }}
                    @endif
                @else
                    {{ trans('public.course_learning_passed', ['percent' => $percent]) }}
                @endif
            </span>
            <br />
            <span class="font-14 font-weight-500">
                @if (!$course->isWebinar())
                    @if ($hasBought)
                        {{ trans('public.expected_progress', ['percent' => $expectedProgress]) }}
                    @endif
                @endif
            </span>
        </div>
    </div>
@endif
@if(!empty($course->chapters) and count($course->chapters))
    <section class="">
        @include('web.default.course.tabs.contents.chapter')
    </section>
@endif
{{-- Sessions --}}

{{-- @if (!empty($course->sessions) and count($course->sessions))
    <section class="mt-20">
        <h2 class="section-title">{{ trans('public.sessions') }}</h2>

        @if (!empty($sessionsWithoutChapter) and count($sessionsWithoutChapter))
            @include('web.default.course.tabs.contents.sessions', ['sessions' => $sessionsWithoutChapter])
        @endif

        @if (!empty($sessionChapters) and count($sessionChapters))
            @foreach ($sessionChapters as $sessionChapter)
                <div class="d-flex justify-content-between align-items-center mt-20">
                    <h3 class="section-title after-line">{{ $sessionChapter->title }}</h3>
                    <span class="ml-5 font-14 text-gray">{{ count($sessionChapter->sessions) }}
                        {{ trans('public.parts') }} -
                        {{ convertMinutesToHourAndMinute($sessionChapter->getDuration()) }}
                        {{ trans('public.hr') }}</span>
                </div>

                @include('web.default.course.tabs.contents.sessions', [
                    'sessions' => $sessionChapter->sessions,
                ])

                @if (!empty($sessionChapter->quizzes) and count($sessionChapter->quizzes))
                    @foreach ($sessionChapter->quizzes as $quiz)
                        @include('web.default.course.tabs.contents.quiz', [
                            'quiz' => $quiz,
                            'isChapterQuiz' => true,
                        ])
                    @endforeach
                @endif
            @endforeach
        @endif
    </section>
@endif --}}

{{-- Files --}}
{{-- @if (!empty($course->files) and count($course->files))
    <section class="mt-40">
        <h2 class="section-title">{{ trans('public.files') }}</h2>

        @if (!empty($filesWithoutChapter) and count($filesWithoutChapter))
            @include('web.default.course.tabs.contents.files', ['files' => $filesWithoutChapter])
        @endif

        @if (!empty($fileChapters) and count($fileChapters))
            @foreach ($fileChapters as $fileChapter)
                <div class="d-flex justify-content-between align-items-center mt-20">
                    <h3 class="section-title after-line">{{ $fileChapter->title }}</h3>
                    <span class="ml-5 font-14 text-gray">{{ count($fileChapter->files) }}
                        {{ trans('public.parts') }}</span>
                </div>

                @include('web.default.course.tabs.contents.files', ['files' => $fileChapter->files])

            @endforeach
        @endif
    </section>

    @include('web.default.course.tabs.play_modal.play_modal')

@endif --}}

{{-- @if (!empty($course->textLessons) and count($course->textLessons))
    <section class="mt-40">
        <h2 class="section-title after-line">{{ trans('webinars.text_lessons') }}</h2>

        @if (!empty($textLessonsWithoutChapter) and count($textLessonsWithoutChapter))
            @include('web.default.course.tabs.contents.text_lessons', [
                'textLessons' => $textLessonsWithoutChapter,
            ])
        @endif
        @if (!empty($textLessonChapters) && count($textLessonChapters))
            @foreach ($textLessonChapters as $textLessonChapter)
                <div class="d-flex justify-content-between align-items-center mt-20">
                    <h3 class="section-title after-line">{{ $textLessonChapter->title }}</h3>
                    <span class="ml-5 font-14 text-gray">{{ count($textLessonChapter->textLessons) }}
                        {{ trans('public.parts') }} -
                        {{ convertMinutesToHourAndMinute($textLessonChapter->getDuration()) }}
                        {{ trans('public.hr') }}</span>
                </div>

                @include('web.default.course.tabs.contents.text_lessons', [
                    'textLessons' => $textLessonChapter->textLessons,
                    'quizzes' => $textLessonChapter->quizzes,
                ])
                @if (!empty($textLessonChapter->quizzes) && count($textLessonChapter->quizzes))
                    @foreach ($textLessonChapter->quizzes as $key => $quiz)

                        @include('web.default.course.tabs.contents.quiz' , ['quiz' => $quiz, 'isChapterQuiz' => true])
                        @php $textLessonChapter->quizzes->forget($key); @endphp
                    @endforeach
                @endif
            @endforeach
        @endif
    </section>
@endif --}}

@php
    //check if any quizzes have certificates so the certificates panel can be built as well
    $atleastOneCertificate = false;
@endphp


@if (!blank($quizzes) && $quizzes->count() > 0)
    {{-- if Quizzes are not part of chapter --}}
    <section class="mt-40">
        <h2 class="section-title after-line">{{ trans('quiz.quizzes') }}</h2>

        <div class="mt-15">
            <div class="row">
                <div class="col-7 col-md-3 font-12 text-gray"><span class="pl-10">{{ trans('public.title') }}</span>
                </div>
                <div class="col-2 font-12 text-gray text-center">{{ trans('public.min') }} {{ trans('quiz.grade') }}
                </div>
                <div class="col-2 font-12 text-gray text-center d-none d-md-block">{{ trans('quiz.attempts') }}</div>
                <div class="col-2 font-12 text-gray text-center d-none d-md-block">{{ trans('public.status') }}</div>
                <div class="col-3"></div>
            </div>

            <div class="row">
                <div class="col-12">
                    @foreach ($quizzes as $quiz)
                        @include('web.default.course.tabs.contents.quiz', ['quiz' => $quiz])
                        @php
                            if ($quiz->certificate) {
                                $atleastOneCertificate = true;
                            }
                        @endphp
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
{{-- Certificates --}}
@if (!empty($course->quizzes) and $course->quizzes->count() > 0 and $atleastOneCertificate)
    <section class="mt-40">
        <h2 class="section-title after-line">{{ trans('panel.certificates') }}</h2>

        <div class="mt-15">
            <div class="row">
                <div class="col-6 font-12 text-gray"><span class="pl-10">{{ trans('public.title') }}</span></div>
                <div class="col-3 text-center font-12 text-gray">{{ trans('public.min') }} {{ trans('quiz.grade') }}
                </div>
                <div class="col-3"></div>
            </div>

            <div class="row">
                <div class="col-12">
                    @foreach ($course->quizzes as $quiz)
                        @if ($quiz->certificate)
                            <div class="rounded-sm shadow-lg border mt-20 p-15">
                                <div class="row align-items-center">
                                    <div class="col-6 d-flex flex-column">
                                        <span
                                            class="font-weight-bold font-14 text-secondary">{{ $quiz->title }}</span>
                                    </div>

                                    <div class="col-3 text-gray font-14 text-center">
                                        {{ $quiz->pass_mark }}/{{ $quiz->quizQuestions->sum('grade') }}</div>

                                    <div class="col-3 d-flex justify-content-end">
                                        @if (!empty($user) and $quiz->can_download_certificate and $hasBought)
                                            <a href="/panel/quizzes/results/{{ $quiz->result->id }}/downloadCertificate"
                                                class="course-content-btns btn btn-sm btn-primary flex-grow-1">{{ trans('home.download') }}</a>
                                        @else
                                            <button type="button"
                                                class="course-content-btns btn btn-sm btn-gray flex-grow-1 disabled {{ empty($user) ? 'not-login-toast' : (!$hasBought ? 'not-access-toast' : (!$quiz->can_download_certificate ? 'can-not-download-certificate-toast' : '')) }}">
                                                {{ trans('home.download') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
