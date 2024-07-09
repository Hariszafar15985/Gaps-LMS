@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
    <link href="/assets/default/vendors/sortable/jquery-ui.min.css"/>
    <link rel="stylesheet" href="/assets/vendors/fontawesome/css/all.min.css"/>
    <style>
        .closeAudio{
            display: none;
        }
    </style>
@endpush

@if($webinar->isWebinar())
    {{-- <section class="mt-50">
        <div class="">
            <h2 class="section-title after-line">{{ trans('public.sessions') }} ({{ trans('public.optional') }})</h2>
        </div>

        <button type="button" class="js-add-chapter btn btn-primary btn-sm mt-15" data-webinar-id="{{ $webinar->id }}" data-type="{{ \App\Models\WebinarChapter::$chapterSession }}">{{ trans('public.new_chapter') }}</button>

        @include('web.default.panel.webinar.create_includes.accordions.chapter',
                    [
                        'chapters' => $webinar->chapters ,
                        'type' => \App\Models\WebinarChapter::$chapterSession,
                        'relationMethod' => 'sessions',
                        'includeFileName' => 'session',
                        'tableName' => 'sessions',
                        'variableName' => 'session',
                        'emptyState' => [
                            'file_name' => 'meet.png',
                            'title' => trans('public.sessions_no_result'),
                            'hint' => trans('public.sessions_no_result_hint'),
                        ]
                    ]
                )
    </section> --}}

    <div id="newSessionForm" class="d-none">
        @include('web.default.panel.webinar.create_includes.accordions.session',['webinar' => $webinar])
    </div>
@endif

<section class="mt-50">
    <div class="">
        {{-- <h2 class="section-title after-line">{{ trans('public.files') }} ({{ trans('public.optional') }})</h2> --}}
        <h2 class="section-title after-line">Sections</h2>
    </div>
    <div class="mt-15">
        <p class="font-12 text-gray">- {{ trans('webinars.course_hint_1') }}</p>
        <p class="font-12 text-gray">- {{ trans('webinars.course_hint_2') }}</p>
    </div>
    <button type="button" class="js-add-chapter btn btn-primary btn-sm mt-15" data-webinar-id="{{ $webinar->id }}" data-type="{{ \App\Models\WebinarChapter::$chapterFile }}">{{ trans('public.new_chapter') }}</button>

    @include('web.default.panel.webinar.create_includes.accordions.chapter',
                [
                    'chapters' => $webinar->chapters ,
                    'type' => \App\Models\WebinarChapter::$chapterFile,
                    'relationMethod' => 'files',
                    'includeFileName' => 'file',
                    'tableName' => 'files',
                    'variableName' => 'file',
                    'emptyState' => [
                        'file_name' => 'files.png',
                        'title' => trans('public.files_no_result'),
                        'hint' => trans('public.files_no_result_hint'),
                    ]
                ]
            )
</section>



@if($webinar->isTextCourse())
    {{-- <section class="mt-50">
        <div class="">
            <h2 class="section-title after-line">{{ trans('public.test_lesson') }} ({{ trans('public.optional') }})</h2>
        </div>

        <button type="button" class="js-add-chapter btn btn-primary btn-sm mt-15" data-webinar-id="{{ $webinar->id }}" data-type="{{ \App\Models\WebinarChapter::$chapterTextLesson }}">{{ trans('public.new_chapter') }}</button>

        @include('web.default.panel.webinar.create_includes.accordions.chapter',
                    [
                        'chapters' => $webinar->chapters ,
                        'type' => \App\Models\WebinarChapter::$chapterTextLesson,
                        'relationMethod' => 'textLessons',
                        'includeFileName' => 'text-lesson',
                        'tableName' => 'text_lessons',
                        'variableName' => 'textLesson',
                        'emptyState' => [
                            'file_name' => 'files.png',
                            'title' => trans('public.text_lesson_no_result'),
                            'hint' => trans('public.text_lesson_no_result_hint'),
                        ]
                    ]
                )
    </section>

    <div id="newTextLessonForm" class="d-none">
        @include('web.default.panel.webinar.create_includes.accordions.text-lesson',['webinar' => $webinar])
    </div> --}}
    <div id="newFileForm" class="d-none">
        @include('web.default.panel.webinar.create_includes.accordions.file',['webinar' => $webinar])
    </div>
    <div id="newInteractiveFileForm" class="d-none">
        @include('web.default.panel.webinar.create_includes.accordions.new_interactive_file',['webinar' => $webinar])
    </div>
    <div id="newTextLessonForm" class="d-none">
        @include('web.default.panel.webinar.create_includes.accordions.text-lesson',['webinar' => $webinar])
    </div>
    <div id="newAssessmentForm" class="d-none">
        @include('web.default.panel.webinar.create_includes.accordions.quiz',['webinar' => $webinar])
    </div>
@endif

@include('web.default.panel.webinar.create_includes.chapter_modal')
{{-- script to handle drip feed show hide for all chapter items is placed in 'resources\views\components\drip-feed-quiz.blade.php' --}}
@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
    <script src="/assets/default/vendors/sortable/jquery-ui.min.js"></script>
    <script src="/assets/default/js/jquery_ui.js"></script>
    <script>
        $( function() {
          $( ".draggable" ).draggable({
                zIndex: 100,
                snap: ".droppable",
                snapMode: "inner",
                scope: "matching_quiz",
                revert: "invalid",
                axis: "y"
            });
            $( ".droppable" ).droppable({
                accept: ".draggable-quiz",
                tolerance: "touch",
                scope: "matching_quiz",
                drop: function( event, ui ) {
                    var quizId = $(ui.draggable.context).attr("data-quiz-id");
                    var quizCurrentChapterId = $(ui.draggable.context).attr("data-chapter-id");
                    var chapterId = $(this).attr("data-chapter-id");
                    var quizCurrentlessonId = $(ui.draggable.context).attr("data-lesson-id");
                    var lessonId = $(this).attr("data-lesson-id");

                    if (lessonId || quizCurrentlessonId) {
                        if (lessonId && quizCurrentlessonId && (quizCurrentlessonId != lessonId)) { //lesson to lesson
                            updateQuizRelation({'quiz_id': quizId, 'chapter_id': chapterId, 'lesson_id': lessonId });
                        } else if ((typeof lessonId == 'undefined')) { //lesson to chapter
                            updateQuizRelation({'quiz_id': quizId, 'chapter_id': chapterId, 'remove_lesson': true });
                        } else if ((typeof quizCurrentlessonId == 'undefined')) { //chapter to lesson
                            updateQuizRelation({'quiz_id': quizId, 'chapter_id': chapterId, 'lesson_id': lessonId});
                        }
                    } else {
                        if (chapterId != quizCurrentChapterId) { //chapter to chapter
                            updateQuizRelation({'quiz_id': quizId, 'chapter_id': chapterId});
                        }
                    }
                }
            });
        });

        function updateQuizRelation(data)
        {
            let action = "/panel/webinars/updateQuizRelation"
            $.post(action, data, function (result) {
                location.reload();
            }).fail(err => {
                console.log(err.responseJSON);
            });
        }

        $(document).ready(function(){
        $(".audioPlayBtn").off().on("click", function(){
            $(this).hide()
            $(this).next().show()
            let audio_link = $(this).attr("data-audio")
            $(this).before('<audio class="audioFile" controlsList="nodownload" controls src="'+audio_link+'"></audio>')
        })

        $(".closeAudio").on("click", function(){
            $(".audioFile").remove()
            $(this).hide()
            $(".audioPlayBtn").show()
        })
    })
    </script>
@endpush
