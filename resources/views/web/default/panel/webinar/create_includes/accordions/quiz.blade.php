<li data-id="{{ !empty($quizInfo) ? $quizInfo->id :'' }}" class="accordion-row bg-white rounded-sm border border-gray300 mt-20 py-15 py-lg-30 px-10 px-lg-20">
{{-- <div class="accordion-row bg-white rounded-sm panel-shadow mt-20 py-15 py-lg-30 px-10 px-lg-20"> --}}
    <div class="d-flex align-items-center justify-content-between " role="tab" id="quiz_{{ !empty($quizInfo) ? $quizInfo->id :'record' }}">
        <div class="d-flex align-items-center font-weight-bold text-dark-blue" href="#collapseQuiz{{ !empty($quizInfo) ? $quizInfo->id :'record' }}" aria-controls="collapseQuiz{{ !empty($quizInfo) ? $quizInfo->id :'record' }}" data-parent="#quizzesAccordion" role="button" data-toggle="collapse" aria-expanded="true">
            <span class="chapter-icon chapter-content-icon mr-10">
                <i data-feather="award" class=""></i>
            </span>
            <div>{{ !empty($quizInfo) ? $quizInfo->title : trans('public.add_new_quizzes') }}</div>
            @if (!empty($quizInfo) && !empty($quizInfo->textLesson))
            <div class="ml-10 text-dark-blue">(Lesson:{{$quizInfo->textLesson->title}})</div>
            @endif
        </div>
        <div class="d-flex align-items-center">
            @if(!empty($quizInfo) and $quizInfo->status != \App\Models\WebinarChapter::$chapterActive)
            <span class="disabled-content-badge mr-10">{{ trans('public.disabled') }}</span>
        @endif

        <i data-feather="move" class="move-icon mr-10 cursor-pointer" height="20"></i>

        @if(!empty($quizInfo))
            <a href="/panel/quizzes/{{ $quizInfo->id }}/delete" class="delete-action btn btn-sm btn-transparent text-gray">
                <i data-feather="trash-2" class="mr-10 cursor-pointer" height="20"></i>
            </a>
            <a href="/panel/quizzes/{{ $quizInfo->id }}/duplicate" class="duplicate-action btn btn-sm btn-transparent text-gray">
                <i data-feather="copy" class="mr-10 cursor-pointer" height="20"></i>
            </a>
        @endif
            <i class="collapse-chevron-icon" data-feather="chevron-down" height="20" href="#collapseQuiz{{ !empty($quizInfo) ? $quizInfo->id :'record' }}" aria-controls="collapseQuiz{{ !empty($quizInfo) ? $quizInfo->id :'record' }}" data-parent="#chapterContentAccordion{{ !empty($chapter) ? $chapter->id :'' }}" role="button" data-toggle="collapse" aria-expanded="true"></i>
        </div>
    </div>
    <div id="collapseQuiz{{ !empty($quizInfo) ? $quizInfo->id :'record' }}" aria-labelledby="quiz_{{ !empty($quizInfo) ? $quizInfo->id :'record' }}" class=" collapse @if(empty($quizInfo)) show @endif" role="tabpanel">
        <div class="panel-collapse text-gray">
            @include('web.default.panel.quizzes.create_quiz_form',
                    [
                        'inWebinarPage' => true,
                        'selectedWebinar' => $webinar,
                        'quiz' => $quizInfo ?? null,
                        'quizQuestions' => !empty($quizInfo) ? $quizInfo->quizQuestions : [],
                        'chapters' => $webinar->chapters,
                    ]
                )
        </div>
    </div>
{{-- </div> --}}
<li>
