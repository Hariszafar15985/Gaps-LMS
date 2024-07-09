@extends(getTemplate().'.layouts.app')
@section('content')
    <div class="container">
        <section class="mt-40">
            <h2 class="font-weight-bold font-16 text-dark-blue">{{ $quizResult->quiz->title }}</h2>
            <p class="text-gray font-14 mt-5">
                <a href="{{ $quizResult->quiz->webinar->getUrl() }}" target="_blank" class="text-gray">{{ $quizResult->quiz->webinar->title }}</a>
                | {{ trans('public.by') }}
                <span class="font-weight-bold">
                    <a href="{{ $quizResult->quiz->creator->getProfileUrl() }}" target="_blank" class=""> {{ $quizResult->quiz->creator->full_name }}</a>
                </span>
            </p>

            <div class="activities-container shadow-sm rounded-lg mt-25 p-20 p-lg-35">
                <div class="row">
                    <div class="col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                        <div class="d-flex flex-column align-items-center text-center">
                            <img src="/assets/default/img/activity/58.svg" width="64" height="64" alt="">
                            <strong class="font-30 font-weight-bold text-secondary mt-5">{{ $quizResult->quiz->pass_mark }}/{{ $questionsSumGrade }}</strong>
                            <span class="font-16 text-gray font-weight-500">{{ trans('public.min') }} {{ trans('quiz.grade') }}</span>
                        </div>
                    </div>

                    <div class="col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                        <div class="d-flex flex-column align-items-center text-center">
                            <img src="/assets/default/img/activity/88.svg" width="64" height="64" alt="">
                            <strong class="font-30 font-weight-bold text-secondary mt-5">{{ $numberOfAttempt }}/{{ $quizResult->quiz->attempt }}</strong>
                            <span class="font-16 text-gray font-weight-500">{{ trans('quiz.attempts') }}</span>
                        </div>
                    </div>

                    <div class="col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                        <div class="d-flex flex-column align-items-center text-center">
                            <img src="/assets/default/img/activity/45.svg" width="64" height="64" alt="">
                            <strong class="font-30 font-weight-bold text-secondary mt-5">{{ $quizResult->user_grade }}/{{  $questionsSumGrade }}</strong>
                            <span class="font-16 text-gray font-weight-500">{{ trans('quiz.your_grade') }}</span>
                        </div>
                    </div>

                    <div class="col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                        <div class="d-flex flex-column align-items-center text-center">
                            <img src="/assets/default/img/activity/44.svg" width="64" height="64" alt="">
                            <strong class="font-30 font-weight-bold text-{{ ($quizResult->status == 'passed') ? 'primary' : ($quizResult->status == 'waiting' ? 'warning' : 'danger') }} mt-5">
                                {{ trans('quiz.'.$quizResult->status) }}
                            </strong>
                            <span class="font-16 text-gray font-weight-500">{{ trans('public.status') }}</span>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <section class="mt-30 quiz-form">
            <form id="quizForm" action="{{ !empty($newQuizStart) ? '/panel/quizzes/'. $newQuizStart->quiz->id .'/update-result' : '' }} " method="post">
                {{ csrf_field() }}
                <input type="hidden" name="quiz_result_id" value="{{ !empty($newQuizStart) ? $newQuizStart->id : ''}}" class="form-control" placeholder=""/>
                <input type="hidden" name="attempt_number" value="{{  $numberOfAttempt }}" class="form-control" placeholder=""/>
                <input type="hidden" class="js-quiz-question-count" value="{{ $quizResult->quiz->quizQuestions->count() }}"/>

                @foreach($quizResult->quiz->quizQuestions as $key => $question)

                    <fieldset data-questionNum="{{$question->id}}" data-questionSerial="{{ $key + 1 }}" class="question_fieldset_{{$question->id}} question-step question-step-{{ $key + 1 }}">
                        <div class="rounded-lg shadow-sm py-25 px-20">
                            <div class="quiz-card">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class="font-weight-bold font-16 text-secondary">
                                            @if ($question->type === \App\Models\QuizzesQuestion::$fillInBlank)
                                                {{ trans('quiz.fillInBlank') }}
                                            @elseif ($question->type === \App\Models\QuizzesQuestion::$informationText)
                                                {{ trans('quiz.information_text') }}
                                            @else
                                                {{ $question->title }}
                                            @endif
                                        </h3>
                                        @if ($question->type != \App\Models\QuizzesQuestion::$informationText)
                                        <p class="text-gray font-14 mt-5">
                                            <span>{{ trans('quiz.question_grade') }} : {{ $question->grade }}</span> | <span>{{ trans('quiz.your_grade') }} : {{ (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["grade"])) ? $userAnswers[$question->id]["grade"] : 0 }}</span>
                                        </p>
                                        @endif
                                    </div>

                                    <div class="rounded-sm border border-gray200 p-15 text-gray">{{ $key + 1 }}/{{ $quizResult->quiz->quizQuestions->count() }}</div>
                                </div>
                                @if($question->type === \App\Models\QuizzesQuestion::$fillInBlank)
                                    @php
                                        $userAnswer = (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["answer"])) ? $userAnswers[$question->id]["answer"] : null;
                                        $questionTitle['user'] = $question->title;
                                        $questionTitle['correct'] = $question->title;
                                        $count = 0;
                                        $textToReplace = '{blank}';
                                        $correctAnswer =  (!empty($question->correct) ? json_decode($question->correct, true) : null);
                                        while(strpos($questionTitle['user'], $textToReplace) !== false) {
                                            $pos = strpos($questionTitle['user'], $textToReplace);
                                            $bgClass = ($userAnswer[$count] == $correctAnswer[$count]) ? 'bg-success' : 'bg-danger';
                                            if ($pos !== false) {
                                                $replacementText = ' <textarea type="text" name="question['. $question->id .'][answer]['.$count.']" value="'.$userAnswer[$count] .'" disabled required class="form-control textareaField'. $bgClass .'" >'.$userAnswer[$count] .' </textarea> ';
                                                $questionTitle['user'] = substr_replace($questionTitle['user'], $replacementText, $pos,  strlen($textToReplace));
                                            }
                                            $count++;
                                        }
                                        $count = 0;
                                        while(strpos($questionTitle['correct'], $textToReplace) !== false) {
                                            $pos = strpos($questionTitle['correct'], $textToReplace);
                                            if ($pos !== false) {
                                                $replacementText = ' <span class="font-weight-bold text-decoration-underline"> '. $correctAnswer[$count] .' </span> ';
                                                $questionTitle['correct'] = substr_replace($questionTitle['correct'], $replacementText, $pos,  strlen($textToReplace));
                                            }
                                            $count++;
                                        }

                                    @endphp
                                    <div class="form-group mt-35">
                                        <label class="input-label text-secondary">{{ trans('quiz.student_answer') }}</label>
                                        <div class="form-inline">{!! $questionTitle['user'] !!}</div>
                                    </div>
                                    <div class="form-group mt-35">
                                        <label class="input-label text-secondary">{{ trans('quiz.correct_answer') }}</label>
                                        <p @if(empty($newQuizStart) or !in_array($authUser->id, $allowedEditors) ) disabled @endif class="form-inline">{!! $questionTitle['correct'] !!}</p>
                                    </div>
                                    @if(!empty($newQuizStart) and in_array($authUser->id, $allowedEditors))
                                        <div class="form-group mt-35">
                                            <label class="font-16 text-secondary">{{ trans('quiz.grade') }}</label>
                                            <input type="number" name="question[{{ $question->id }}][grade]" min="0" max="{{ $question->grade }}" value="{{ (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["grade"])) ? $userAnswers[$question->id]["grade"] : 0 }}" class="form-control gradeInput">
                                        </div>
                                    @endif
                                    @include(getTemplate() .'.panel.quizzes.partials.quiz_instructor_assessment_remarks_partial')
                                @elseif(in_array($question->type, [\App\Models\QuizzesQuestion::$matchingListText, \App\Models\QuizzesQuestion::$matchingListImage]))
                                    @php
                                        $userAnswer = (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["answer"])) ? $userAnswers[$question->id]["answer"] : null;
                                        $correctPairs = (!empty($question->correct) ? json_decode($question->correct, true) : null);
                                        $questionTitle['user'] = $question->title;
                                        $questionTitle['correct'] = $question->title;
                                    @endphp
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col" class="text-left">{{trans('quiz.text')}}</th>
                                                <th scope="col">{{trans('quiz.userAnswer')}}</th>
                                                <th scope="col">{{trans('quiz.correctAnswer')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($correctPairs as $key => $pair)
                                                @php $actualAnswer = ($question->type === \App\Models\QuizzesQuestion::$matchingListText) ? $pair['description'] : $pair['image']  @endphp
                                                <tr
                                                @if ($userAnswer[$key] == $actualAnswer)
                                                    class="bg-success"
                                                @else
                                                    class="bg-danger"
                                                @endif
                                                >
                                                    <th scope="row">{{($key + 1)}}</th>
                                                    <td>{{$pair['text']}}</td>
                                                    <td class="text-center">
                                                        @if($question->type === \App\Models\QuizzesQuestion::$matchingListText)
                                                            {{$userAnswer[$key]}}
                                                        @else
                                                            <img src='{{$userAnswer[$key]}}' width="auto" height="auto" style="max-width: 150px; max-height:150px;"/>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($question->type === \App\Models\QuizzesQuestion::$matchingListText)
                                                            {{$actualAnswer}}
                                                        @else
                                                            <img src='{{$actualAnswer}}' width="auto" height="auto" style="max-width: 150px; max-height:150px;"/>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @if(!empty($newQuizStart) and in_array($authUser->id, $allowedEditors))
                                        <div class="form-group mt-35">
                                            <label class="font-16 text-secondary">{{ trans('quiz.grade') }}</label>
                                            <input type="number" name="question[{{ $question->id }}][grade]" min="0" max="{{ $question->grade }}" value="{{ (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["grade"])) ? $userAnswers[$question->id]["grade"] : 0 }}" class="form-control gradeInput">
                                        </div>
                                    @endif
                                    @include(getTemplate() .'.panel.quizzes.partials.quiz_instructor_assessment_remarks_partial')
                                @elseif($question->type === \App\Models\QuizzesQuestion::$descriptive)

                                    <div class="form-group mt-35">
                                        <label class="input-label text-secondary">{{ trans('quiz.student_answer') }}</label>
                                        <textarea name="question[{{ $question->id }}][answer]" rows="10" disabled class="form-control">{{ (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["answer"])) ? $userAnswers[$question->id]["answer"] : '' }}</textarea>
                                    </div>

                                    <div class="form-group mt-35">
                                        <label class="input-label text-secondary">{{ trans('quiz.correct_answer') }}</label>
                                        <textarea rows="10" name="question[{{ $question->id }}][correct_answer]" @if(empty($newQuizStart) or !in_array($authUser->id, $allowedEditors) ) disabled @endif class="form-control">{{ $question->correct }}</textarea>
                                    </div>

                                    @if(!empty($newQuizStart) and in_array($authUser->id, $allowedEditors))
                                        <div class="form-group mt-35">
                                            <label class="font-16 text-secondary">{{ trans('quiz.grade') }}</label>
                                            <input type="number" name="question[{{ $question->id }}][grade]" min="0" max="{{ $question->grade }}" value="{{ (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["grade"])) ? $userAnswers[$question->id]["grade"] : 0 }}" class="form-control gradeInput">
                                        </div>
                                    @endif

                                    @include(getTemplate() .'.panel.quizzes.partials.quiz_instructor_assessment_remarks_partial')

                                @elseif ($question->type === \App\Models\QuizzesQuestion::$fileUpload)
                                    <div class="form-group mt-35">
                                        <h4>{{trans('quiz.attached_files')}}</h4>
                                        @php
                                            $files = (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["answer"])) ? json_decode($userAnswers[$question->id]["answer"], true) : [];
                                            $attachmentCount =  1;
                                        @endphp
                                        @foreach ($files as $file)
                                            <a href="{{route('get.quiz.attachment').'?filePath='.$file}}" target="_blank">{{trans('quiz.attachment')}} {{$attachmentCount}}</a>
                                            <br />
                                            @php $attachmentCount++; @endphp
                                        @endforeach
                                    </div>
                                    @if(!empty($newQuizStart) and in_array($authUser->id, $allowedEditors))
                                        <div class="form-group mt-35">
                                            <label class="font-16 text-secondary">{{ trans('quiz.grade') }}</label>
                                            <input type="number" name="question[{{ $question->id }}][grade]" min="0" max="{{ $question->grade }}" value="{{ (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["grade"])) ? $userAnswers[$question->id]["grade"] : 0 }}" class="form-control gradeInput">
                                        </div>
                                    @endif
                                    @include(getTemplate() .'.panel.quizzes.partials.quiz_instructor_assessment_remarks_partial')

                                @elseif($question->type === \App\Models\QuizzesQuestion::$informationText)
                                    <div class="form-group mt-35">
                                        <h3 class="font-16 text-secondary">{!! $question->title !!}</h3>
                                    </div>
                                @else
                                    <div class="question-multi-answers mt-35">
                                        @foreach($question->quizzesQuestionsAnswers as $key => $answer)
                                            <div class="answer-item">
                                                @if($answer->correct)
                                                    <span class="badge badge-primary correct">{{ trans('quiz.correct') }}</span>
                                                @endif

                                                <input id="asw-{{ $answer->id }}" type="radio" disabled name="question[{{ $question->id }}][answer]" value="{{ $answer->id }}" {{ (!empty($userAnswers[$question->id]) and (int)$userAnswers[$question->id]["answer"] === $answer->id) ? 'checked' : '' }}>

                                                @if(!$answer->image)
                                                    <label for="asw-{{ $answer->id }}" class="answer-label {{($answer->correct === $answer->title) ? 'answer-label-correct' : 'answer-label-wrong'}} font-16 d-flex text-dark-blue align-items-center justify-content-center ">
                                                        <span class="answer-title">
                                                            {{ $answer->title }}
                                                            @if(!empty($userAnswers[$question->id]) and (int)$userAnswers[$question->id]["answer"] ===  $answer->id)
                                                                <span class="d-block">({{ trans('quiz.student_answer') }})</span>
                                                            @endif
                                                        </span>
                                                    </label>
                                                @else
                                                    <label for="asw-{{ $answer->id }}" class="answer-label font-16 d-flex align-items-center text-dark-blue justify-content-center ">
                                                        <div class="image-container">
                                                            @if(!empty($userAnswers[$question->id]) and (int)$userAnswers[$question->id]["answer"] ===  $answer->id)
                                                                <span class="selected font-14">{{ trans('quiz.student_answer') }}</span>
                                                            @endif
                                                            <img src="{{ config('app_url') . $answer->image }}" class="img-cover" alt="">
                                                        </div>
                                                    </label>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                @endforeach
                <div id="quizErrorAlert" class="alert alert-warning mt-2 py-2 d-none">

                </div>

                <div class="d-flex align-items-center mt-30">
                    <button type="button" disabled class="previous btn btn-sm btn-primary mr-20 p-0">{{ trans('quiz.previous_question') }}</button>
                    <button type="button" class="next btn btn-primary btn-sm mr-auto p-0">{{ trans('quiz.next_question') }}</button>

                    @if(!empty($newQuizStart))
                        <button type="submit" class="finish btn btn-sm btn-danger p-0">{{ trans('public.finish') }}</button>
                    @endif
                </div>
            </form>
        </section>
    </div>
@endsection

@push("styles_top")
<style>
    .textareaField{
        width: 100% !important;
        resize: both !important;
    }
</style>
@endpush

@push('scripts_bottom')
    <script src="/assets/default/js/parts/quiz-start.min.js"></script>
    <script>
        $('.assessment-btn').on('click', function() {
            let btnValue = $(this).val();
            let parentElem = $(this).closest('.assessment-div');
            let questionId = parentElem.attr('data-questionId');
            let inputElem = parentElem.find('input');
            let gradeElem = $(this).closest('.question-step').find('input.gradeInput');
            let assessmentSpan = $(`#assessment_text_${questionId}`);
            inputElem.val(btnValue);
            assessmentSpan.addClass('float-left');
            assessmentSpan.addClass('font-weight-bold');
            assessmentSpan.addClass('py-1');
            if (btnValue === "{{ trans('quiz.passed') }}") {
                assessmentSpan.removeClass('text-danger');
                assessmentSpan.addClass('text-primary');
                gradeElem.val("1"); //1 mark if question is satisfactory
            } else {
                assessmentSpan.removeClass('text-primary');
                assessmentSpan.addClass('text-danger');
                gradeElem.val("0"); //0 marks if question is unsatisfactory
            }
            $(`#instructor_remarks_${questionId}`).removeClass('d-none');
            assessmentSpan.text(`${btnValue}!`);
        });
        $('.gradeInput').on('focusout', function() {
            if ($(this).val() > $(this).attr('max')) {
                alert(`{!! trans("quiz.reward_grade_exceeds_total_grade") !!}`);
                $(this).focus();
            }
        });
        $('#quizForm .finish').on('click', function() {
            $('#quizErrorAlert').text('');
            $('#quizErrorAlert').addClass('d-none');
            //is any question graded more than its max marks?
            let allottedGrades = $('#quizForm .gradeInput');
            if (allottedGrades.length > 0) {
                for(let i=0; i < allottedGrades.length; i++) {
                    if ($(allottedGrades[i]).val() > $(allottedGrades[i]).attr('max')) {
                        $('#quizErrorAlert').text('{{ trans("quiz.incorrect_question_graded_please_review") }}');
                        $('#quizErrorAlert').removeClass('d-none');
                        let errorFieldset = allottedGrades[i].closest('.question-step');
                        let errorQuestionNum = errorFieldset.getAttribute('data-questionNum');
                        let questionSerial = errorFieldset.getAttribute('data-questionSerial');
                        let currentFieldSetSerial = $(`.question-step.activeFieldset`).attr('data-questionSerial');
                        if (questionSerial > currentFieldSetSerial) {
                            while(questionSerial > currentFieldSetSerial) {
                                $('button.next').click();
                                currentFieldSetSerial++;
                            }
                        } else if (questionSerial < currentFieldSetSerial) {
                            while(questionSerial < currentFieldSetSerial) {
                                $('button.previous').click();
                                currentFieldSetSerial--;
                            }
                        }
                        return false;
                    }
                }
            }
            //has any question assessment been missed out?
            let allAssessmentMarked = true;
            $(`input.questionAssessmentInput`).each( function() {
                let inputVal = $(this).val();
                if (inputVal.length < 1 || inputVal == "") {
                    $('#quizErrorAlert').text('{{ trans("quiz.all_questions_not_assessed") }}');
                    $('#quizErrorAlert').removeClass('d-none');
                    let errorFieldset = $(this).closest('.question-step');
                    let errorQuestionNum = errorFieldset.attr('data-questionNum');
                    let questionSerial = errorFieldset.attr('data-questionSerial');
                    let currentFieldSetSerial = $(`.question-step.activeFieldset`).attr('data-questionSerial');
                    if (questionSerial > currentFieldSetSerial) {
                        while(questionSerial > currentFieldSetSerial) {
                            $('button.next').click();
                            currentFieldSetSerial++;
                        }
                    } else if (questionSerial < currentFieldSetSerial) {
                        while(questionSerial < currentFieldSetSerial) {
                            $('button.previous').click();
                            currentFieldSetSerial--;
                        }
                    }
                    allAssessmentMarked = false;
                    return false;
                }
            });

            if (!allAssessmentMarked) {
                return false;
            }

            return true;    //if we are here, then no issues were encountered.
        });
    </script>
@endpush
