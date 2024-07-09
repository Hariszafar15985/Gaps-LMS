{{-- <div class="row mt-10">
    <div class="col-12">
        <div class="accordion-content-wrapper mt-15" id="chapterAccordion{{ !empty($chapter) ? $chapter->id :'' }}" role="tablist" aria-multiselectable="true">
            @if(!empty($chapters) and count($chapters))
                <ul class="draggable-content-lists draggable-lists-chapter-{{ $type }}" data-drag-class="draggable-lists-chapter-{{ $type }}" data-order-table="webinar_chapters">
                    @foreach($chapters->where('type', $type) as $chapter)

                        <li data-id="{{ !empty($chapter) ? $chapter->id :'' }}" data-chapter-order="{{ $chapter->order }}" class="accordion-row bg-white rounded-sm panel-shadow mt-20 py-15 py-lg-30 px-10 px-lg-20">
                            <div class="d-flex align-items-center justify-content-between " role="tab" id="chapter_{{ !empty($chapter) ? $chapter->id :'record' }}">
                                <div class="d-flex align-items-center" href="#collapseChapter{{ !empty($chapter) ? $chapter->id :'record' }}" aria-controls="collapseChapter{{ !empty($chapter) ? $chapter->id :'record' }}" data-parent="#chapterAccordion" role="button" data-toggle="collapse" aria-expanded="true">
                                    <span class="chapter-icon mr-10">
                                        <i data-feather="grid" class=""></i>
                                    </span>
                                    <div class="">
                                        <span class="font-weight-bold text-dark-blue d-block">{{ !empty($chapter) ? $chapter->title : trans('public.add_new_chapter') }}</span>
                                        <span class="font-12 text-gray d-block">
                                            {{ !empty($chapter->$relationMethod) ? count($chapter->$relationMethod) : 0 }} {{ trans('public.topic') }}

                                            @if($chapter->type != \App\Models\WebinarChapter::$chapterFile)
                                                | {{ convertMinutesToHourAndMinute($chapter->getDuration()) }} {{ trans('public.hr') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center">

                                    @if($chapter->status != \App\Models\WebinarChapter::$chapterActive)
                                        <span class="disabled-content-badge mr-10">{{ trans('public.disabled') }}</span>
                                    @endif

                                    <button type="button" class="add-course-content-btn mr-10" data-webinar-id="{{ $webinar->id }}" data-type="{{ $type }}" data-chapter="{{ !empty($chapter) ? $chapter->id :'' }}" data-toggle="tooltip" data-placement="top" data-html="true" title="{{ trans('public.add_'.$type) }}">
                                        <i data-feather="plus" class=""></i>
                                    </button>

                                    <button type="button" class="js-add-chapter btn-transparent text-gray" data-webinar-id="{{ $webinar->id }}" data-type="{{ $type }}" data-chapter="{{ $chapter->id }}" data-locale="{{ mb_strtoupper($chapter->locale) }}">
                                        <i data-feather="edit-3" class="mr-10 cursor-pointer" height="20"></i>
                                    </button>

                                    <a href="/panel/chapters/{{ $chapter->id }}/delete" class="delete-action btn btn-sm btn-transparent text-gray">
                                        <i data-feather="trash-2" class="mr-10 cursor-pointer" height="20"></i>
                                    </a>

                                    <a href="/panel/chapters/{{ $chapter->id }}/duplicate" class="duplicate-action btn btn-sm btn-transparent text-gray"  data-toggle="tooltip" data-placement="top" title="{{ trans('public.duplicate') }}">
                                        <i data-feather="copy" class="mr-10 cursor-pointer" height="20"></i>
                                    </a>

                                    <i data-feather="move" class="move-icon mr-10 cursor-pointer text-gray" height="20"></i>

                                    <i class="collapse-chevron-icon feather-chevron-up text-gray" data-feather="chevron-down" height="20" href="#collapseChapter{{ !empty($chapter) ? $chapter->id :'record' }}" aria-controls="collapseChapter{{ !empty($chapter) ? $chapter->id :'record' }}" data-parent="#chapterAccordion" role="button" data-toggle="collapse" aria-expanded="true"></i>
                                </div>
                            </div>

                            <div id="collapseChapter{{ !empty($chapter) ? $chapter->id :'record' }}" aria-labelledby="chapter_{{ !empty($chapter) ? $chapter->id :'record' }}" class=" collapse show" role="tabpanel">
                                <div class="panel-collapse text-gray matching_quiz">
                                    <div class="accordion-content-wrapper mt-15" id="chapterContentAccordion{{ !empty($chapter) ? $chapter->id :'' }}" role="tablist" aria-multiselectable="true">
                                        @if(!empty($chapter->$relationMethod) and count($chapter->$relationMethod))
                                            <ul class="draggable-content-lists draggable-lists-{{ $type }}-chapter-{{ $chapter->id }}" data-drag-class="draggable-lists-{{ $type }}-chapter-{{ $chapter->id }}" data-order-table="{{ $tableName }}">
                                                @foreach($chapter->$relationMethod as $row)
                                                    @include('web.default.panel.webinar.create_includes.accordions.'.$includeFileName ,[$variableName => $row , 'chapter' => $chapter])
                                                @endforeach
                                            </ul>
                                        @else
                                            @include(getTemplate() . '.includes.no-result',[
                                                'file_name' => $emptyState['file_name'],
                                                'title' => $emptyState['title'],
                                                'hint' => $emptyState['hint'],
                                            ])
                                        @endif
                                    </div>
                                    <div class="row matching-quiz-row pt-35 px-35">
                                        <div class="font-weight-bold text-dark-blue d-block">{{ trans('quiz.quizzes') }}</div>
                                        <div id="chapter_{{$chapter->id}}_optionDiv" class="w-100 quiz-col droppable" data-chapter-id="{{$chapter->id}}">
                                            <div class="accordion-content-wrapper mt-15" id="quizzesAccordion" role="tablist" aria-multiselectable="true">
                                                @if(!empty($chapter->quizzes) and count($chapter->quizzes))
                                                    <ul class="col-12 h-100" style="width: 95% !important;">
                                                    @php $count = 0; @endphp
                                                    @foreach($chapter->quizzes as $quizInfo)
                                                        <li id="quiz_{{$quizInfo->id}}_{{$count}}" style="z-index:100;" class='draggable draggable-quiz quiz_{{$quizInfo->id}}_option' data-chapter-id="{{$chapter->id}}" data-quiz-id="{{$quizInfo->id}}" data-initialTarget="initial_droppable_{{$quizInfo->id}}">
                                                            @include('web.default.panel.webinar.create_includes.accordions.quiz',['webinar' => $webinar,'quizInfo' => $quizInfo])
                                                            @php $count++; @endphp
                                                        </li>
                                                    @endforeach
                                                    </ul>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                @include(getTemplate() . '.includes.no-result',[
                    'file_name' => $emptyState['file_name'],
                    'title' => $emptyState['title'],
                    'hint' => $emptyState['hint'],
                ])
            @endif
        </div>

    </div>
</div> --}}
<div class="row mt-10">
    <div class="col-12">
        <div class="accordion-content-wrapper mt-15" id="chapterAccordion{{ !empty($chapter) ? $chapter->id :'' }}" role="tablist" aria-multiselectable="true">
            @if(!empty($webinar->chapters) and count($webinar->chapters))
                <ul class="draggable-content-lists draggable-lists-chapter" data-drag-class="draggable-lists-chapter" data-order-table="webinar_chapters">
                    @foreach($webinar->chapters as $chapter)

                        <li data-id="{{ !empty($chapter) ? $chapter->id :'' }}" data-chapter-order="{{ $chapter->order }}" class="accordion-row bg-white rounded-sm panel-shadow mt-20 py-15 py-lg-30 px-10 px-lg-20">
                            <div class="d-flex align-items-center justify-content-between " role="tab" id="chapter_{{ !empty($chapter) ? $chapter->id :'record' }}">
                                <div class="d-flex align-items-center" href="#collapseChapter{{ !empty($chapter) ? $chapter->id :'record' }}" aria-controls="collapseChapter{{ !empty($chapter) ? $chapter->id :'record' }}" data-parent="#chapterAccordion" role="button" data-toggle="collapse" aria-expanded="true">
                                    <span class="chapter-icon mr-10">
                                        <i data-feather="grid" class=""></i>
                                    </span>
                                    <div class="">
                                        <span class="font-weight-bold text-dark-blue d-block">{{ !empty($chapter) ? $chapter->title : trans('public.add_new_chapter') }}</span>
                                        <span class="font-12 text-gray d-block">
                                            {{ !empty($chapter->chapterItems) ? count($chapter->chapterItems) : 0 }} {{ trans('public.topic') }}
                                            | {{ convertMinutesToHourAndMinute($chapter->getDuration()) }} {{ trans('public.hr') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center">

                                    @if($chapter->status != \App\Models\WebinarChapter::$chapterActive)
                                        <span class="disabled-content-badge mr-10">{{ trans('public.disabled') }}</span>
                                    @endif

                                    <div class="btn-group dropdown table-actions">
                                        <button type="button" class="add-course-content-btn mr-10 dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i data-feather="plus" class=""></i>
                                        </button>
                                        <div class="dropdown-menu ">
                                            @if($webinar->isWebinar())
                                                <button type="button" class="js-add-course-content-btn d-block mb-10 btn-transparent" data-webinar-id="{{ $webinar->id }}" data-type="session" data-chapter="{{ !empty($chapter) ? $chapter->id :'' }}" data-toggle="tooltip" data-placement="top" data-html="true">
                                                    {{ trans('public.add_session') }}
                                                </button>

                                            @endif
                                            {{-- Add file Button --}}
                                            <button type="button" class="js-add-course-content-btn d-block mb-10 btn-transparent" data-webinar-id="{{ $webinar->id }}" data-type="file" data-chapter="{{ !empty($chapter) ? $chapter->id :'' }}" data-toggle="tooltip" data-placement="top" data-html="true">
                                                {{ trans('public.add_file') }}
                                            </button>
                                            {{-- Add Scorm button --}}
                                            <button type="button" class="js-add-course-content-btn d-block mb-10 btn-transparent" data-webinar-id="{{ $webinar->id }}" data-type="new_interactive_file" data-chapter="{{ !empty($chapter) ? $chapter->id :'' }}" data-toggle="tooltip" data-placement="top" data-html="true">
                                                {{ trans('update.new_interactive_file') }}
                                            </button>
                                            {{-- Add Text Lesson Button --}}
                                            <button type="button" class="js-add-course-content-btn d-block mb-10 btn-transparent" data-webinar-id="{{ $webinar->id }}" data-type="text_lesson" data-chapter="{{ !empty($chapter) ? $chapter->id :'' }}" data-toggle="tooltip" data-placement="top" data-html="true">
                                                {{ trans('public.add_text_lesson') }}
                                            </button>
                                            {{-- Add Quiz Button --}}
                                            <button type="button" class="js-add-course-content-btn d-block mb-10 btn-transparent" data-webinar-id="{{ $webinar->id }}" data-type="new_assessment" data-chapter="{{ !empty($chapter) ? $chapter->id :'' }}" data-toggle="tooltip" data-placement="top" data-html="true">
                                                {{ trans('public.add_quiz') }}
                                            </button>

                                            @if(getFeaturesSettings('webinar_assignment_status'))
                                                <button type="button" class="js-add-course-content-btn d-block mb-10 btn-transparent" data-webinar-id="{{ $webinar->id }}" data-type="assignment" data-chapter="{{ !empty($chapter) ? $chapter->id :'' }}">
                                                    {{ trans('update.add_new_assignments') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <button type="button" class="js-add-chapter btn-transparent text-gray" data-webinar-id="{{ $webinar->id }}" data-chapter="{{ $chapter->id }}" data-locale="{{ mb_strtoupper($chapter->locale) }}">
                                        <i data-feather="edit-3" class="mr-10 cursor-pointer" height="20"></i>
                                    </button>

                                    <a href="/panel/chapters/{{ $chapter->id }}/delete" class="delete-action btn btn-sm btn-transparent text-gray">
                                        <i data-feather="trash-2" class="mr-10 cursor-pointer" height="20"></i>
                                    </a>
                                    <a href="/panel/chapters/{{ $chapter->id }}/duplicate" class="duplicate-action btn btn-sm btn-transparent text-gray"  data-toggle="tooltip" data-placement="top" title="{{ trans('public.duplicate') }}">
                                        <i data-feather="copy" class="mr-10 cursor-pointer" height="20"></i>
                                    </a>
                                    <i data-feather="move" class="move-icon mr-10 cursor-pointer text-gray" height="20"></i>

                                    <i class="collapse-chevron-icon feather-chevron-up text-gray" data-feather="chevron-down" height="20" href="#collapseChapter{{ !empty($chapter) ? $chapter->id :'record' }}" aria-controls="collapseChapter{{ !empty($chapter) ? $chapter->id :'record' }}" data-parent="#chapterAccordion" role="button" data-toggle="collapse" aria-expanded="true"></i>
                                </div>
                            </div>

                            <div id="collapseChapter{{ !empty($chapter) ? $chapter->id :'record' }}" aria-labelledby="chapter_{{ !empty($chapter) ? $chapter->id :'record' }}" class=" collapse show" role="tabpanel">
                                <div class="panel-collapse text-gray">

                                    <div class="accordion-content-wrapper mt-15" id="chapterContentAccordion{{ !empty($chapter) ? $chapter->id :'' }}" role="tablist" aria-multiselectable="true">
                                        @if(!empty($chapter->chapterItems) and count($chapter->chapterItems))
                                            <ul class="draggable-content-lists draggable-lists-chapter-{{ $chapter->id }}" data-drag-class="draggable-lists-chapter-{{ $chapter->id }}" data-order-table="webinar_chapter_items">
                                                @foreach($chapter->chapterItems as $chapterItem)
                                                    @if($chapterItem->type == \App\Models\WebinarChapterItem::$chapterSession and !empty($chapterItem->session))
                                                        @include('web.default.panel.webinar.create_includes.accordions.session' ,['session' => $chapterItem->session , 'chapter' => $chapter, 'chapterItem' => $chapterItem])
                                                    @elseif($chapterItem->type == \App\Models\WebinarChapterItem::$chapterFile and !empty($chapterItem->file) and $chapterItem->file->storage != 'upload_archive')
                                                        @include('web.default.panel.webinar.create_includes.accordions.file' ,['file' => $chapterItem->file , 'chapter' => $chapter, 'chapterItem' => $chapterItem])
                                                    @elseif($chapterItem->type == \App\Models\WebinarChapterItem::$chapterFile and !empty($chapterItem->file) and $chapterItem->file->storage == 'upload_archive')
                                                        @include('web.default.panel.webinar.create_includes.accordions.new_interactive_file' ,['file' => $chapterItem->file , 'chapter' => $chapter, 'chapterItem' => $chapterItem])
                                                    @elseif($chapterItem->type == \App\Models\WebinarChapterItem::$chapterTextLesson and !empty($chapterItem->textLesson))
                                                        @include('web.default.panel.webinar.create_includes.accordions.text-lesson' ,['textLesson' => $chapterItem->textLesson , 'chapter' => $chapter, 'chapterItem' => $chapterItem])
                                                    @elseif($chapterItem->type == \App\Models\WebinarChapterItem::$chapterAssignment and !empty($chapterItem->assignment))
                                                        @include('web.default.panel.webinar.create_includes.accordions.assignment' ,['assignment' => $chapterItem->assignment , 'chapter' => $chapter, 'chapterItem' => $chapterItem])
                                                    @elseif($chapterItem->type == \App\Models\WebinarChapterItem::$chapterQuiz and !empty($chapterItem->quiz))
                                                        @include('web.default.panel.webinar.create_includes.accordions.quiz' ,['quizInfo' => $chapterItem->quiz , 'chapter' => $chapter, 'chapterItem' => $chapterItem])
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @else
                                            @include(getTemplate() . '.includes.no-result',[
                                                'file_name' => 'meet.png',
                                                'title' => trans('update.chapter_content_no_result'),
                                                'hint' => trans('update.chapter_content_no_result_hint'),
                                            ])
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                @include(getTemplate() . '.includes.no-result',[
                    'file_name' => 'meet.png',
                    'title' => trans('update.chapter_no_result'),
                    'hint' => trans('update.chapter_no_result_hint'),
                ])
            @endif
        </div>

    </div>
</div>
