{{-- <style>
    body #conrseContentSection:fullscreen {
        overflow: scroll !important;
        background-color: white;
    }

    body #conrseContentSection:-ms-fullscreen {
        overflow: scroll !important;
    }

    body #conrseContentSection:-webkit-full-screen {
        overflow: scroll !important;
    }

    body #conrseContentSection:-moz-full-screen {
        overflow: scroll !important;
    }
</style> --}}
<style>
    .disabled {
    pointer-events: none; /* Disable mouse events */
    opacity: 0.5; /* Adjust opacity to visually indicate it's disabled */
    /* You can add additional styling as needed */
    cursor: not-allowed; /* Display a "not-allowed" cursor */
}
</style>
<div class="mt-15">
    <div class="row">
        <div class="col-7 col-md-5 font-12 text-gray"><span class="pl-10">{{ trans('public.title') }}</span></div>
        <div class="col-2 font-12 text-gray text-center">{{ trans('public.study_time') }}</div>
        <div class="col-2 font-12 text-gray text-center d-none d-md-block">{{ trans('public.attachments') }}</div>
        <div class="col-3"></div>
    </div>
    <div class="row">
        <div class="col-12" id="conrseContentSection">
            <div class="accordion-content-wrapper mt-15" id="textLessonsAccordion" role="tablist"
                aria-multiselectable="true">
                {{-- @foreach ($textLessons as $textLesson) --}}

                    <div class="accordion-row rounded-sm shadow-lg border mt-20 p-15">
                        <div class="row align-items-center" role="tab" id="textLessons_{{ $textLesson->id }}">
                            <div class="col-7 col-md-5 d-flex align-items-center"
                                href="#collapseTextLessons{{ $textLesson->id }}"
                                aria-controls="collapseTextLessons{{ $textLesson->id }}"
                                data-parent="#textLessonsAccordion" role="button" data-toggle="collapse"
                                aria-expanded="true">

                                @if ($textLesson->accessibility == 'paid')
                                    @if (!empty($user) and $hasBought)
                                    <a href="{{ $course->getUrl() }}/lessons/{{ $textLesson->id }}/read" target="_blank"
                                        class="mr-15 {{ !userHasDripFeedAccess($textLesson->id, auth()->user()->id) ? 'disabled' : '' }}"
                                        data-toggle="tooltip" data-placement="top" title="{{ trans('public.read') }}">
                                         <i data-feather="file-text" width="20" height="20" class="text-gray"></i>
                                     </a>

                                    @else
                                        <button class="mr-15 btn-transparent">
                                            <i data-feather="lock" width="20" height="20" class="text-gray"></i>
                                        </button>
                                    @endif
                                @else
                                    <a href="{{ $course->getUrl() }}/lessons/{{ $textLesson->id }}/read" target="_blank"
                                        class="mr-15 disabled"
                                        data-toggle="tooltip" data-placement="top" title="{{ trans('public.read') }}">
                                        <i data-feather="file-text" width="20" height="20" class="text-gray"></i>
                                    </a>

                                @endif

                                <span class="font-weight-bold text-secondary font-14 file-title">{{ $textLesson->title }}</span>
                                <!---- Toogel button status -->
                                @if (
                                    !(auth()->user()->isUser()) //non-student role is accessing the profile
                                    && !empty($requestedUser) && $requestedUser->isUser() //profile user to check is a student
                                    && showManually($textLesson->id, $requestedUser->id) //check if manual access is granted to student
                                )
                                    <span class="mx-1 text-danger f-12">{{ trans('public.manual_access_granted') }}</span>
                                @endif
                            </div>

                            <div class="col-2 text-gray text-center font-14">{{ $textLesson->study_time }}
                                {{ trans('public.min') }}</div>

                            <div class="col-2 text-gray text-center font-14 d-none d-md-block">
                                {{ $textLesson->attachments_count }}</div>

                            <div class="col-3 d-flex justify-content-end">
                                @if ($textLesson->accessibility == 'paid')
                                    @if (
                                        !empty($user) //$user variable has been received from controller
                                        && (
                                            !$user->isUser() //either this is not a student accessing the course content
                                            || ($user->isUser() && $hasBought) //or this is a student, but we should check payment
                                        )
                                    )
                                        {{-- <a href="{{ $course->getUrl() }}/lessons/{{ $textLesson->id }}/read" target="_blank" class="course-content-btns btn btn-sm
                                        {{(isset($textLesson->learningStatus->text_lesson_status) && $textLesson->learningStatus->text_lesson_status === 1) ? "btn-primary" : "btn-gray"}}
                                        flex-grow-1">
                                        {{ (isset($textLesson->learningStatus->text_lesson_status) && $textLesson->learningStatus->text_lesson_status === 1) ? trans('public.completed') : trans('public.in_progress') }}
                                        </a> --}}

                                        <x-course-preview.text-lesson-buttons :course=$course :textLesson=$textLesson :requestedUser=$requestedUser > </x-course-preview.text-lesson-buttons>
                                    @else
                                        <button type="button"
                                            class="course-content-btns btn btn-sm btn-gray flex-grow-1 disabled {{ empty($user) ? 'not-login-toast' : (!$hasBought ? 'not-access-toast' : '') }}">
                                            {{ isset($textLesson->learningStatus) ? trans('public.completed') : trans('public.read') }}
                                        </button>
                                    @endif
                                @else
                                    {{-- <a href="{{ $course->getUrl() }}/lessons/{{ $textLesson->id }}/read" target="_blank" class="course-content-btns btn btn-sm
                                    {{((isset($textLesson->learningStatus->text_lesson_status) && $textLesson->learningStatus->text_lesson_status === 1) || !isset($textLesson->learningStatus->text_lesson_status)) ? "btn-primary" : "btn-gray"}}
                                    flex-grow-1">
                                    {{ (isset($textLesson->learningStatus->text_lesson_status) && $textLesson->learningStatus->text_lesson_status === 1) ? trans('public.completed') :
                                                    ((isset($textLesson->learningStatus->text_lesson_status) && $textLesson->learningStatus->text_lesson_status === 0) ? trans('public.in_progress') : trans('public.read')) }}
                                    </a> --}}

                                    <x-course-preview.text-lesson-buttons :course=$course :textLesson=$textLesson :requestedUser=$requestedUser > </x-course-preview.text-lesson-buttons>
                                @endif
                            </div>
                        </div>
                        {{-- DripFeed course will show on date --- section starts --}}
                        @if (!empty($textLesson->drip_feed) && !userHasDripFeedAccess($textLesson->id, auth()->user()->id))
                            <div class="text-center font-weight-bold text-secondary font-12 file-title">
                                {{ trans('public.lesson_show_on', [
                                        'date' => courseWillShowOn(
                                            getCoursePurchaseDate($textLesson->webinar_id, auth()->user()->id),
                                            $textLesson->show_after_days,
                                        ),
                                    ])
                                }}
                            </div>
                        @endif
                        {{-- DripFeed course will show on date --- section end --}}
                        <div class="row">
                            <div class="col-12">
                                @if (!empty($textLesson->quizzes) and count($textLesson->quizzes) > 0)

                                    @foreach ($textLesson->quizzes as $key => $quiz)
                                        @if ($quiz->chapter_id === $textLesson->chapter->id)
                                            @include('web.default.course.tabs.contents.quiz', [
                                                'quiz' => $quiz,
                                                'isChapterQuiz' => true,
                                                'requestedUser' => $requestedUser
                                            ])
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div id="collapseTextLessons{{ $textLesson->id }}"
                            aria-labelledby="textLessons_{{ $textLesson->id }}" class="collapse d-none" role="tabpanel">
                            <div class="panel-collapse">
                                <div class="text-gray">
                                    {!! nl2br(clean($textLesson->summary)) !!}
                                </div>

                                @if (!empty($user) and $hasBought)
                                    <div class="d-flex align-items-center mt-20">
                                        <label class="mb-0 mr-10 cursor-pointer font-weight-500"
                                            for="textLessonReadToggle{{ $textLesson->id }}">{{ trans('public.i_passed_this_lesson') }}</label>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" id="textLessonReadToggle{{ $textLesson->id }}"
                                                data-lesson-id="{{ $textLesson->id }}" value="{{ $course->id }}"
                                                class="js-text-lesson-learning-toggle custom-control-input"
                                                @if (!empty($textLesson->learningStatus)) checked @endif>
                                            <label class="custom-control-label"
                                                for="textLessonReadToggle{{ $textLesson->id }}"></label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                {{-- @endforeach --}}
            </div>
        </div>



    </div>
</div>
