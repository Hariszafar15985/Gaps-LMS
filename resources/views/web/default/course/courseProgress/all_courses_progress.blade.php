@extends(getTemplate().'.layouts.app')

@section('content')
<section class="course-cover-container {{ empty($activeSpecialOffer) ? 'not-active-special-offer' : '' }}">
    <div class="cover-content pt-40">
        <div class="container position-relative" style="top: 30%;">
            <h3 class="text-white">
                Students Courses Progress
            </h3>
            <br>
            <br>

            <h6 class="text-white">
                <span> Student Name: &nbsp;</span> <span>{{$student->full_name}}</span>
            </h6>
            <br>
            <br>
            <h6 class="text-white">
                Enrolled Classes : {{count($coursePurchased )}}
            </h6>
        </div>
    </div>
</section>
{{-- course stats section starts from here --}}
<section>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="accordion-content-wrapper" id="chaptersAccordion" role="tablist" aria-multiselectable="true">
                    @foreach($coursePurchased as $purchased)
                        {{-- progress calculation of each course --}}
                        @php
                            if ($purchased) {
                                $behindProgress = $student->isBehindProgress();
                                $studentProgress = 0;
                                $expectedProgress = 0;
                                $studentProgress =  $purchased->webinar->getProgress($student->id);
                                $expectedProgress = $purchased->webinar->getExpectedProgress($student->id);
                                $courseStats = \App\Helpers\WebinarHelper::courseStats($purchased, $student);
                            }
                        @endphp
                        <div class="accordion-row rounded-sm border mt-20 p-15">
                            <div class="row" role="tab" id="webinar_{{ $purchased->webinar->id }}" data-toggle="tooltip" data-placement="top" title="{{ (isset($behindProgress) && $behindProgress) ? trans("admin/main.behind_progress") : trans("admin/main.on_schedule") }}">
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-4">
                                            <div class="js-webinar-collapse-toggle d-flex align-items-center" href="#collapseWebinar{{ $purchased->webinar->id }}" aria-controls="collapseWebinar{{ $purchased->webinar->id }}" role="button" data-toggle="collapse" aria-expanded="true">
                                                <span class="chapter-icon mr-15">
                                                    <i data-feather="grid"></i>
                                                </span>
                                                <span class="font-weight-bold text-secondary font-14">{{ $purchased->webinar->title }}</span>
                                            </div>
                                        </div>
                                        <div class="col-4 font-weight-bold text-gray d-flex align-items-center" style="font-size: 12px;">
                                            <div class="row">
                                                <div class="col-3 text-left">
                                                    Chpaters ({{$courseStats['chaptersInProgress']}}/{{$courseStats['totalChapters']}})
                                                </div>
                                                <div class="col-3 text-left">
                                                    Lessons ({{$courseStats['lessonsLearnt']}}/{{$courseStats['totalLesons']}})
                                                </div>
                                                <div class="col-3 text-left">
                                                    Quizzes ({{$courseStats['passedQuizzes']}}/{{$courseStats['totalQuizzes']}})
                                                </div>
                                                <div class="col-3 text-left">
                                                    Files ({{$courseStats['filesVisited']}}/{{$courseStats['totalFiles']}})
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="d-flex align-items-center justify-content-between">

                                                        {{-- Progress --}}
                                                        <div class="progress course-progress flex-grow-1 shadow-xs rounded-sm">
                                                            <span class="progress-bar bg-primary" style="width: {{$studentProgress}}%" data-toggle="tooltip" data-placement="top" title="{{$studentProgress}}%">
                                                            </span>
                                                        </div>
                                                        <div class="mr-15 font-14 text-gray d-flex align-items-center">
                                                            <i class="collapse-chevron-icon" data-feather="chevron-down" height="20" href="#collapseWebinar{{ $purchased->webinar->id }}" aria-controls="collapseWebinar{{ $purchased->webinar->id }}" data-toggle="collapse" aria-expanded="false"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 text-left mt-3 d-flex align-items-center" style="font-size: 10px">
                                                    <span class="@if($studentProgress) text-primary @else text-warning @endif">{{$studentProgress}} %</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="collapseWebinar{{ $purchased->webinar->id }}" class="collapse" role="tabpanel" aria-labelledby="webinar_{{ $purchased->webinar->id }}">
                                <div class="accordion-content mt-5 container">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="accordion-content-wrapper" id="chaptersAccordion" role="tablist" aria-multiselectable="true">
                                                @foreach($purchased->webinar->chapters as $chapter)
                                                    @php
                                                      $chapterProgress =  \App\Helpers\WebinarHelper::chapterProgress($chapter, $student);
                                                    @endphp
                                                    @if((!empty($chapter->chapterItems) and count($chapter->chapterItems)))
                                                        <div class="accordion-row rounded-sm border mt-20 p-15" data-toggle="tooltip" data-placement="top" title="{{$chapterProgress['progress'] > 50 ? 'Satisfactory' : 'Unsatisfactory'}}">
                                                            <div class="row" role="tab" id="chapter_{{ $chapter->id }}">
                                                                <div class="col-4">
                                                                    <div class="js-chapter-collapse-toggle d-flex align-items-center" href="#collapseChapter{{ $chapter->id }}" aria-controls="collapseChapter{{ $chapter->id }}" data-parent="#chaptersAccordion" role="button" data-toggle="collapse" aria-expanded="true">
                                                                        <span class="chapter-icon mr-15">
                                                                            <i data-feather="grid" class=""></i>
                                                                        </span>

                                                                        <span class="font-weight-bold text-secondary font-14">{{ $chapter->title }}</span>
                                                                        <br>
                                                                        <br>
                                                                    </div>
                                                                </div>

                                                                <div class="col-4 text-gray font-weight-bold text-gray d-flex align-items-center" style="font-size: 12px;">
                                                                    <div class="row">
                                                                        <div class="col-4 text-left">
                                                                            Lessons ({{$chapterProgress['lessonsLearnt']}}/{{count($chapter->textLessons)}})
                                                                        </div>
                                                                        <div class="col-4 text-left">
                                                                            Quizzes ({{$chapterProgress['passedQuizzes']}}/{{count($chapter->quizzes)}})
                                                                        </div>
                                                                        <div class="col-4 text-left">
                                                                            Files ({{$chapterProgress['filesVisited']}}/{{count($chapter->files)}})
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-1"></div>
                                                                <div class="col-3">
                                                                    <div class="row">

                                                                        <div class="col-12 d-flex align-items-center">
                                                                            {{-- <span class="mr-15 font-14" >
                                                                                {{ $chapter->getTopicsCount(true) }} {{ trans('public.parts') }}
                                                                                {{ !empty($chapter->getDuration()) ? ' - ' . convertMinutesToHourAndMinute($chapter->getDuration()) .' '. trans('public.hr') : '' }}
                                                                            </span> --}}
                                                                            <div class="progress course-progress flex-grow-1 shadow-xs rounded-sm">
                                                                                <span class="progress-bar bg-primary" style="width: {{$chapterProgress['progress']}}%" data-toggle="tooltip" data-placement="top" title="{{$chapterProgress['progress']}}%">
                                                                                </span>
                                                                            </div>

                                                                            <i class="collapse-chevron-icon" data-feather="chevron-down" height="20" href="#collapseChapter{{ !empty($chapter) ? $chapter->id :'record' }}" aria-controls="collapseChapter{{ !empty($chapter) ? $chapter->id :'record' }}" data-parent="#chaptersAccordion" role="button" data-toggle="collapse" aria-expanded="true"></i>
                                                                        </div>
                                                                        <div class="col-12 text-left mt-3 d-flex align-items-center" style="font-size: 10px">
                                                                            <span class="@if($chapterProgress['progress']) text-primary @else text-warning @endif">{{$chapterProgress['progress']}} %</span>
                                                                        </div>
                                                                    </div>

                                                                </div>

                                                            </div>
                                                            <div id="collapseChapter{{ $chapter->id }}" aria-labelledby="chapter_{{ $chapter->id }}" class=" collapse" role="tabpanel">
                                                                <div class="panel-collapse">
                                                                    @if(!empty($chapter->chapterItems) and count($chapter->chapterItems))
                                                                        @foreach($chapter->chapterItems as $chapterItem)
                                                                            {{-- if there is any requirement for chapter sessions, use the below commented code --}}
                                                                            {{-- @if($chapterItem->type == \App\Models\WebinarChapterItem::$chapterSession and !empty($chapterItem->session) and $chapterItem->session->status == 'active')
                                                                                @include('web.default.course.tabs.contents.sessions' , ['session' => $chapterItem->session, 'accordionParent' => 'chaptersAccordion'])
                                                                            @endif --}}
                                                                            @if($chapterItem->type == \App\Models\WebinarChapterItem::$chapterFile and !empty($chapterItem->file) and $chapterItem->file->status == 'active')
                                                                                @php
                                                                                    $islearnt =  \App\Helpers\WebinarHelper::isLearnt('file', $chapterItem->file->id, $student);
                                                                                    $fileType = ($chapterItem->file->file_type == 'archive') ? 'Scorm' :
                                                                                    (($chapterItem->file->file_type == 'video') ? 'Video' :
                                                                                    (($chapterItem->file->file_type == 'power point') ? 'Power point' :
                                                                                    (($chapterItem->file->file_type == 'pdf') ? 'Pdf' :
                                                                                    'File')));

                                                                                    $fileStatus = ($chapterItem->file->file_type == 'archive') ? 'Commenced' : 'Completed';
                                                                                @endphp
                                                                            <div class="container mt-3 mb-3">
                                                                                <div class="row rounded-sm border pb-5">
                                                                                    <div class="col-1">
                                                                                        <div class="font-12 text-gray"><span class="pl-10"><span class="chapter-icon chapter-content-icon">
                                                                                            <i data-feather="{{$chapterItem->file->getIconByType()}}" width="20" height="20" class="text-gray"></i>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-5 font-weight-bold text-secondary font-14 file-title text-left d-flex align-items-center">
                                                                                    {{$chapterItem->file->title}}
                                                                                    </div>
                                                                                    <div class="col-3 @if($islearnt) ? text-secondary font-weight-bold @else text-gray @endif font-14 file-title text-left d-flex align-items-center">
                                                                                    {{$fileType}}

                                                                                    </div>
                                                                                    <div class="col-3 text-secondary font-14 file-title text-left d-flex align-items-center"> {{ $islearnt ? $fileStatus : 'Not ' . $fileStatus }}</div>
                                                                                </div>
                                                                            </div>
                                                                            @elseif($chapterItem->type == \App\Models\WebinarChapterItem::$chapterTextLesson and !empty($chapterItem->textLesson) and $chapterItem->textLesson->status == 'active')
                                                                                @php
                                                                                    $islearnt =  \App\Helpers\WebinarHelper::isLearnt('text_lesson', $chapterItem->textLesson->id, $student);
                                                                                @endphp
                                                                                <div class="container mt-3 mb-3">
                                                                                    <div class="row rounded-sm border pb-5">
                                                                                        <div class="col-3 col-md-1">
                                                                                            <div class="font-12 text-gray"><span class="pl-10"><span class="chapter-icon chapter-content-icon">
                                                                                                <i data-feather="file-text" width="20" height="20" class="text-gray"></i>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-9 col-md-5 font-weight-bold text-secondary font-14 file-title text-left d-flex align-items-center">
                                                                                        {{$chapterItem->textLesson->title}}
                                                                                        </div>
                                                                                        <div class="col-12 col-md-3 text-secondary font-14 file-title text-left d-flex align-items-center">
                                                                                        Text Lesson
                                                                                        </div>
                                                                                        <div class="col-12 col-md-3 {{($islearnt) ? 'text-secondary font-weight-bold' : 'text-gray'}} font-14 file-title text-left d-flex align-items-center"> {{$islearnt ? 'Completed' : 'Not Started'}} </div>

                                                                                        @if (!empty($chapterItem->textLesson->quizzes) and count($chapterItem->textLesson->quizzes) > 0)

                                                                                        @foreach ($chapterItem->textLesson->quizzes as $key => $quiz)
                                                                                        <div class="col-12 mb-3">
                                                                                            @if ($quiz->chapter_id === $chapterItem->textLesson->chapter->id)
                                                                                                @include('web.default.course.courseProgress.includes.quiz_with_progress', [
                                                                                                    'quiz' => $quiz,
                                                                                                    'isChapterQuiz' => true,
                                                                                                    'student' => $student
                                                                                                ])
                                                                                            @endif

                                                                                        </div>
                                                                                        @endforeach

                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            {{-- uncomment the following @elseif in case of chapter assignment progress code --}}
                                                                            {{-- @elseif($chapterItem->type == \App\Models\WebinarChapterItem::$chapterAssignment and !empty($chapterItem->assignment) and $chapterItem->assignment->status == 'active') --}}
                                                                            @elseif($chapterItem->type == \App\Models\WebinarChapterItem::$chapterQuiz and !empty($chapterItem->quiz) and $chapterItem->quiz->status == 'active' and !$chapterItem->quiz->text_lesson_id)
                                                                                <div class="col-12 mb-3">

                                                                                    @include('web.default.course.courseProgress.includes.quiz_with_progress', [
                                                                                        'quiz' => $chapterItem->quiz,
                                                                                        'isChapterQuiz' => true,
                                                                                        'student' => $student
                                                                                    ])
                                                                                </div>
                                                                            @endif
                                                                        @endforeach
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                @if (count($purchased->webinar->quizzes->whereNull('chapter_id')) > 0)
                                <div class="container">
                                    <div class="rounded-sm border mt-20 p-15">
                                        <div class="row">
                                            <div class="col-12 text-left">
                                                <span class="font-weight-bold text-secondary font-14">Assessments (Without chapter)</span>
                                            </div>
                                            <div class="col-12">
                                                @foreach ($purchased->webinar->quizzes->whereNull('chapter_id') as  $quizWithNoChapter)


                                                @include('web.default.course.courseProgress.includes.quiz_with_progress', [
                                                    'quiz' => $quizWithNoChapter,
                                                    'isChapterQuiz' => false,
                                                    'student' => $student
                                                ])


                                            @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
{{-- course stats section endss from here --}}
@endsection
