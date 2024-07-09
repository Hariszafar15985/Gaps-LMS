@if ($dropDownListing === false)

    <div class="rounded-lg shadow-sm mt-35 p-20 course-teacher-card d-flex align-items-center flex-column">
        <div class="teacher-avatar mt-5">
            <img src="{{ $course->teacher->getAvatar() }}" class="img-cover" alt="{{ $course->teacher->full_name }}">
        </div>
        <h3 class="mt-10 font-20 font-weight-bold text-secondary">{{ $course->teacher->full_name }}</h3>
        <span class="mt-5 font-weight-500 text-gray">{{ trans('product.product_designer') }}</span>

        @include('web.default.includes.webinar.rate', ['rate' => $course->teacher->rates()])

        <div class="user-reward-badges d-flex align-items-center mt-30">
            @foreach ($course->teacher->getBadges() as $userBadge)
                <div class="mr-15" data-toggle="tooltip" data-placement="botom" data-html="true"
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


    {{-- @if (Request::segment(3) == 'lessons') --}}
    {{-- The following section for attachments is not working --}}
    {{-- @if (!empty($textLesson->attachments) and count($textLesson->attachments))
        <div class="shadow-sm rounded-lg bg-white px-15 px-md-25 py-20 mt-30">
            <h3 class="category-filter-title font-16 font-weight-bold text-dark-blue">
                {{ trans('public.attachments') }}</h3>

            <ul class="p-0 m-0 pt-10">
                @foreach ($textLesson->attachments as $attachment)
                    <li
                        class="mt-10 p-10 rounded bg-info-lighter font-14 font-weight-500 text-dark-blue d-flex align-items-center justify-content-between text-ellipsis">
                        <span class="">{{ $attachment->file->title }}</span>

                        <a href="{{ $course->getUrl() }}/file/{{ $attachment->file->id }}/download">
                            <i data-feather="download-cloud" width="20" class="text-secondary"></i>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif --}}

    <div class="shadow-sm rounded-lg bg-white px-15 px-md-25 py-20 mt-30">
        <h3 class="category-filter-title font-16 font-weight-bold text-dark-blue">
            {{-- {{ trans('public.course_sessions') }} --}} Class {{ trans('public.attachments') }}
        </h3>
        @php
            $courseUrl = $course->getUrl();
        @endphp
        <div class="p-0 m-0 pt-10">
            @foreach ($course->chapters as $chapterIndex => $chapter)
                @if ($chapter->status == 'active')
                    <br>
                    <span class="pt-2 mb-30">{{ ++$chapterIndex . '-' . $chapter->title }}</span>
                    <br>
                    @php
                        $counterIndex = 1;
                    @endphp
                    @foreach ($chapter->chapterItems as $item)
                        @if ($chapter->id == $item->chapter_id)
                            @if ($item->type == 'text_lesson' && $item->textLesson->status == 'active')
                                @php
                                    $itemUrl = !empty($courseUrl) ? "{$courseUrl}/lessons/{$item->textLesson->id}/read" : '';
                                @endphp
                                <a href="{{ $courseUrl }}/lessons/{{ $item->textLesson->id }}/read"
                                    class="d-block mt-10 px-5 py-15 rounded font-14 font-weight-500 text-ellipsis @if (Request::segment(4) == $item->textLesson->id) bg-primary text-white @else bg-info-lighter text-dark-blue @endif">
                                    {{ $chapterIndex . '.' . $counterIndex++ . '- ' . $item->textLesson->title }}
                                </a>

                                @if (!empty($item->textLesson->quizzes) && count($item->textLesson->quizzes))
                                    <div class=" px-5 px-md-10 py-5 mt-0">
                                        <ul class="p-0 m-0 pt-10">
                                            @foreach ($item->textLesson->quizzes as $quiz)
                                                @if ($quiz->status == 'active' && $quiz->chapter_id == $item->chapter_id)
                                                    <li
                                                        class="mt-5 p-10 rounded bg-info-lighter font-14 font-weight-500 text-dark-blue d-flex align-items-center justify-content-between text-ellipsis">
                                                        <x-panel.quiz-link :quiz="$quiz" />
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                {{-- Text Lesson File Attachment --}}
                                @if (!empty($item->textLesson->attachments) and count($item->textLesson->attachments))
                                    <div class="shadow-sm rounded-lg bg-white px-15 px-md-25 py-20 mt-30">
                                        <h3 class="category-filter-title font-16 font-weight-bold text-dark-blue">
                                            {{ trans('public.attachments') }}</h3>

                                        <ul class="p-0 m-0 pt-10">
                                            @foreach ($item->textLesson->attachments as $attachment)
                                                <li
                                                    class="mt-10 p-10 rounded bg-info-lighter font-14 font-weight-500 text-dark-blue d-flex align-items-center justify-content-between text-ellipsis">
                                                    <span class="">{{ $attachment->file->title }}</span>

                                                    <a
                                                        href="{{ $course->getUrl() }}/file/{{ $attachment->file->id }}/download">
                                                        <i data-feather="download-cloud" width="20"
                                                            class="text-secondary"></i>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @elseif($item->type == 'file' && $item->file->status == 'active')
                                @php
                                    if ($item->file->file_type == 'archive') {
                                        $itemUrl = !empty($courseUrl) ? "{$courseUrl}/file/{$item->file->id}/showHtml" : '';
                                    } else {
                                        $itemUrl = !empty($courseUrl) ? "{$courseUrl}/file/{$item->file->id}/play" : '';
                                    }
                                @endphp
                                <a href="{{ $itemUrl }}"
                                    class="d-block mt-10 px-10 py-15 rounded font-14 font-weight-500 text-ellipsis @if (Request::segment(4) == $item->file->id) bg-primary text-white @else bg-info-lighter text-dark-blue @endif">
                                    {{ $chapterIndex . '.' . $counterIndex++ . '- ' . $item->file->title }}
                                </a>
                            @endif
                        @endif
                        {{-- @foreach ($lesson->quizzes as $quiz)
                            <x-panel.quiz-link :quiz="$quiz" />
                        @endforeach --}}
                    @endforeach
                    <br>

                    @php
                        $chapterQuizzes = array_filter($chapter->quizzes->toArray(), function ($quiz) {
                            return $quiz['status'] === 'active' && $quiz['text_lesson_id'] === null;
                        });
                    @endphp

                    @if (!blank($chapterQuizzes) && count($chapterQuizzes) > 0)
                        <div class=" py-20 mt-30">
                            <h3 class="category-filter-title font-16 font-weight-bold text-dark-blue">
                                {{-- {{ trans('public.attachments') }} --}} Chapter Misc
                            </h3>
                            {{-- For chapter quizzes which don't belong to any text lesson --}}
                            @foreach ($chapter->quizzes as $quiz)
                                @if ($quiz->text_lesson_id == null && ($quiz->status = 'active'))
                                    <ul class="p-0 m-0 pt-10">

                                        <li
                                            class="mt-10 p-10 rounded bg-info-lighter font-14 font-weight-500 text-dark-blue d-flex align-items-center justify-content-between text-ellipsis">
                                            {{-- <span class="">{{ $quiz->title }}</span> --}}

                                            <x-panel.quiz-link :quiz="$quiz" />
                                        </li>

                                    </ul>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    <br>
                    <hr>
                @endif
            @endforeach

            {{-- orphan content of the course --}}
            @php
                $courseQuizzess = array_filter($course->quizzes->toArray(), function ($quiz) {
                    return $quiz['status'] === 'active' && $quiz['chapter_id'] === null;
                });
            @endphp
            @if (!blank($courseQuizzess))
                <div class=" py-20 mt-30">
                    <h3 class="category-filter-title font-16 font-weight-bold text-dark-blue">
                        {{-- {{ trans('public.attachments') }} --}} Course Misc
                    </h3>
                    @foreach ($course->quizzes as $quiz)
                        @if (empty($quiz->text_lesson_id) && empty($quiz->chapter_id))
                            <ul class="p-0 m-0 pt-10">

                                <li
                                    class="mt-10 p-10 rounded bg-info-lighter font-14 font-weight-500 text-dark-blue d-flex align-items-center justify-content-between text-ellipsis">
                                    {{-- <span class="">{{ $quiz->title }}</span> --}}

                                    <x-panel.quiz-link :quiz="$quiz" />
                                </li>

                            </ul>
                        @endif
                    @endforeach
                </div>
            @endif

        </div>
    </div>


    {{-- @if (!empty($course->chapters))
            <div class="shadow-sm rounded-lg bg-white px-15 px-md-25 py-20 mt-30">
                <h3 class="category-filter-title font-16 font-weight-bold text-dark-blue">
                    {{ trans('public.course_sessions') }}</h3>

                <div class="p-0 m-0 pt-10">
                    @foreach ($course->chapters as $textLessonChapter)
                        <br>
                        <span class="">{{ $textLessonChapter->title }}</span>
                        <br>
                        @foreach ($textLessonChapter->textLessons as $lesson)
                            @php
                                $lessonUrl = !empty($courseUrl) ? "{$courseUrl}/lessons/{$lesson->id}/read" : '';
                            @endphp
                            <a href="{{ $courseUrl }}/lessons/{{ $lesson->id }}/read"
                                class="d-block mt-10 px-10 py-15 rounded font-14 font-weight-500 text-ellipsis @if ($currentUrl == $lessonUrl) bg-primary text-white @else bg-info-lighter text-dark-blue @endif">
                                {{ $loop->iteration . '- ' . $lesson->title }}
                            </a>
                            @foreach ($lesson->quizzes as $quiz)
                                <x-panel.quiz-link :quiz="$quiz" />
                            @endforeach
                        @endforeach

                        @foreach ($textLessonChapter->quizzes as $quiz)
                            @if (empty($quiz->text_lesson_id))
                                <x-panel.quiz-link :quiz="$quiz" />
                            @endif
                        @endforeach
                    @endforeach
                </div>
            </div>
        @endif --}}


    {{-- @endif --}}

    {{-- @if (!empty($course->files) and count($course->files) and Request::segment(3) == 'file')
        <div class="shadow-sm rounded-lg bg-white px-15 px-md-25 py-20 mt-30">
            <h3 class="category-filter-title font-16 font-weight-bold text-dark-blue">
                {{ trans('public.attachments') }}</h3>
            @foreach ($course->chapters as $chapter)
                <ul class="p-0 m-0 pt-10">
                    <br>
                    <span class="">{{ $chapter->title }}</span>

                    @if (!empty($chapter->files) && count($chapter->files))
                        @foreach ($chapter->files as $file)
                            @if ($file->interactive_type == 'custom' || $file->interactive_type == 'adobe_captivate' || $file->interactive_type == 'ispring')
                                <li
                                    class="mt-10 p-10 rounded font-14 font-weight-500 align-items-center @if (Request::segment(4) == $file->id) bg-primary text-white @else bg-info-lighter text-dark-blue @endif">
                                    <div class="row">
                                        <div class="col-2">
                                            <i data-feather="{{ $file->getIconByType() }}" class="text-secondary"></i>
                                        </div>
                                        <div class="col-10">
                                            <a href="{{ $course->getUrl() }}/file/{{ $file->id }}/showHtml">
                                                {{ $file->title }}
                                            </a>
                                        </div>
                                    </div>


                                </li>
                            @endif
                        @endforeach
                    @endif
                </ul>
            @endforeach
        </div>
    @endif --}}
@else
    <x-course.quiz.lessons-drop-down :textLessonChapters=$textLessonChapters :quizLessonId=$quizLessonId
        :courseUrl=$courseUrl />

@endif
