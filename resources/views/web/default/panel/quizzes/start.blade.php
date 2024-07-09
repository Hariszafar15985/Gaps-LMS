@extends(getTemplate() . '.layouts.app')
@section('content')
    @if (isset($invalid) && $invalid === true)
        <section class="container text-course-content-section mt-10 mt-md-40">
            <div class="row">
                <div class="col-12 col-md-8 pt-4">
                    @if (!empty($quiz->title))
                        <h3>
                            {{ $quiz->title }}
                        </h3>
                    @endif
                    <div class="row">
                        <div class="col-12 py-2 px-0">
                            <x-quiz-attempt-error error="{{ $validityErrorMessage }}" courseSlug="{{ $courseSlug }}"
                                nextLessonId="{{ $nextLessonId }}" />
                            <x-course.lesson-navigation-buttons :webinar_id={{ $quiz->webinar->id }}
                                :previousLesson=$previousLesson :nextLesson=$nextLesson :showFullScreenButton=false />
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <x-course.sidebar :webinarId=$webinarId />
                </div>
            </div>
        </section>
    @else
        <div class="container">
            <section class="mt-40">
                <h2 class="font-weight-bold font-16 text-dark-blue">{{ $quiz->title }}</h2>
                <p class="text-gray font-14 mt-5">
                    <a href="{{ $quiz->webinar->getUrl() }}" target="_blank"
                        class="text-gray">{{ $quiz->webinar->title }}</a>
                    | {{ trans('public.by') }}
                    <span class="font-weight-bold">
                        <a href="{{ $quiz->creator->getProfileUrl() }}" target="_blank" class="font-14">
                            {{ $quiz->creator->full_name }}</a>
                    </span>
                </p>

                <div class="activities-container shadow-sm rounded-lg mt-25 p-20 p-lg-35">
                    <div class="row">
                        <div class="col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                            <div class="d-flex flex-column align-items-center text-center">
                                <img src="/assets/default/img/activity/58.svg" width="64" height="64" alt="">
                                <strong
                                    class="font-30 font-weight-bold text-secondary mt-5">{{ $quiz->pass_mark }}/{{ $quizQuestions->sum('grade') }}</strong>
                                <span class="font-16 text-gray">{{ trans('public.min') }} {{ trans('quiz.grade') }}</span>
                            </div>
                        </div>

                        <div class="col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                            <div class="d-flex flex-column align-items-center text-center">
                                <img src="/assets/default/img/activity/88.svg" width="64" height="64" alt="">
                                <strong
                                    class="font-30 font-weight-bold text-secondary mt-5">{{ $attempt_count }}/{{ $quiz->attempt }}</strong>
                                <span class="font-16 text-gray">{{ trans('quiz.attempts') }}</span>
                            </div>
                        </div>

                        <div
                            class="col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                            <div class="d-flex flex-column align-items-center text-center">
                                <img src="/assets/default/img/activity/47.svg" width="64" height="64" alt="">
                                <strong
                                    class="font-30 font-weight-bold text-secondary mt-5">{{ $quizQuestions->count() }}</strong>
                                <span class="font-16 text-gray">{{ trans('public.questions') }}</span>
                            </div>
                        </div>

                        <div
                            class="col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                            <div class="d-flex flex-column align-items-center text-center">
                                <img src="/assets/default/img/activity/clock.svg" width="64" height="64"
                                    alt="">
                                @if (!empty($quiz->time))
                                    <strong class="font-30 font-weight-bold text-secondary mt-5">
                                        <div class="d-flex align-items-center timer"
                                            data-minutes-left="{{ $quiz->time }}"></div>
                                    </strong>
                                @else
                                    <strong
                                        class="font-30 font-weight-bold text-secondary mt-5">{{ trans('quiz.unlimited') }}</strong>
                                @endif
                                <span class="font-16 text-gray">{{ trans('quiz.remaining_time') }}</span>
                            </div>
                        </div>


                    </div>
                </div>
            </section>

            <section class="mt-30 quiz-form">
                <form id="quizForm" action="/panel/quizzes/{{ $quiz->id }}/store-result" method="post" class=""
                    enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="quiz_result_id" value="{{ $newQuizStart->id }}" class="form-control"
                        placeholder="" />
                    <input type="hidden" name="attempt_number" value="{{ $attempt_count }}" class="form-control"
                        placeholder="" />
                    <input type="hidden" name="next_btn_click" value="1" class="nextBtnClick" placeholder="" />
                    @php
                        $firstEmptyShown = false; //to track that we've made the first unanswered question empty
                    @endphp

                    @foreach ($quizQuestions as $key => $question)
                        {{-- php code starts from here --}}
                        @php
                        $userAnswer = (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]['answer'])) ? $userAnswers[$question->id]['answer'] : null;
                        @endphp
                        {{-- php code ends here --}}
                        <fieldset data-questionNum="{{ $question->id }}" data-questionSerial="{{ $key + 1 }}"
                            class="question_fieldset_{{ $question->id }} question-step question-step-{{ $key + 1 }} @if (!$firstEmptyShown) activeFieldset @php
                        $firstEmptyShown = true;
                        @endphp @endif">
                            <div class="rounded-lg shadow-sm py-25 px-20">
                                <div class="quiz-card">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>

                                            @if ($question->type === \App\Models\QuizzesQuestion::$fillInBlank)
                                                <div class="form-group mt-35">
                                                    <h3 class="font-weight-bold font-16 text-secondary">
                                                        {{ trans('quiz.fillInBlank') }} </h3>
                                                </div>
                                            @elseif ($question->type === \App\Models\QuizzesQuestion::$matchingListText)
                                                <div class="form-group mt-35">
                                                    <h3 class="font-weight-bold font-16 text-secondary">
                                                        {{ trans('quiz.pleaseMatchCorrectTexts') }} </h3>
                                                </div>
                                            @elseif ($question->type === \App\Models\QuizzesQuestion::$informationText)
                                                <div class="form-group mt-35">
                                                    <h3 class="font-weight-bold font-16 text-secondary">
                                                        {{ trans('quiz.information_text') }} </h3>
                                                </div>
                                            @else
                                                <h3 class="font-weight-bold font-16 text-secondary">{{ $question->title }}?
                                                </h3>
                                            @endif
                                            @if ($question->type != \App\Models\QuizzesQuestion::$informationText)
                                                <p class="text-gray font-14 mt-5">
                                                    <span>{{ trans('quiz.question_grade') }} : {{ $question->grade }}
                                                    </span>
                                                </p>
                                            @endif
                                            @if (isset($prefilledQuestions[$question->id]['grade']) && strlen(trim($prefilledQuestions[$question->id]['grade'])) > 0)
                                                <p class="text-gray font-14 mt-5">
                                                    <span>{{ trans('quiz.you_previously_scored') }} :
                                                        {{ $prefilledQuestions[$question->id]['grade'] }} </span>
                                                </p>
                                            @endif

                                            @if (isset($prefilledQuestions[$question->id]['instructor_remarks']) &&
                                                    strlen(trim($prefilledQuestions[$question->id]['instructor_remarks'])) > 0)
                                                <p class="text-gray font-14 mt-5">
                                                    <span class="font-weight-bold">{{ trans('quiz.instructor_remarks') }} :
                                                    </span> <span
                                                        class="font-italic">{{ $prefilledQuestions[$question->id]['instructor_remarks'] }}
                                                    </span>
                                                </p>
                                            @endif
                                        </div>

                                        <div class="rounded-sm border border-gray200 questionCounter p-15 text-gray">
                                            {{ $key + 1 }}/{{ $quizQuestions->count() }}</div>
                                    </div>
                                    @if ($question->type === \App\Models\QuizzesQuestion::$descriptive)
                                        @php
                                            $descriptiveCorrectAnswer = $question->correct;
                                            $userDescriptiveAnswer = isset($userAnswers[$question->id]['answer']) ? $userAnswers[$question->id]['answer'] : null;
                                        @endphp
                                        <div class="form-group mt-35">
                                            <textarea name="question[{{ $question->id }}][answer]" rows="15" class="questionAnswerField form-control"{{ (strcasecmp($descriptiveCorrectAnswer,$userDescriptiveAnswer) === 0)? 'readonly' : '' }}>@if (strcasecmp($descriptiveCorrectAnswer,$userDescriptiveAnswer) === 0){{ $userAnswer }} @endif</textarea>
                                        </div>
                                    @elseif($question->type === \App\Models\QuizzesQuestion::$fillInBlank)
                                        <div class="form-group mt-35 pt-35 px-35">
                                            @php
                                                $correctBlanks = json_decode($question->correct);
                                                $userBlankAnswers = isset($userAnswers[$question->id]['answer']) ? $userAnswers[$question->id]['answer'] : null;
                                                $isTrue = false;
                                                if ($correctBlanks && $userBlankAnswers && is_array($correctBlanks) && is_array($userAnswers)) {
                                                    foreach ($correctBlanks as $key => $blankAnswer) {
                                                        //strcasecmp is function, that returns 0 if the both strings are matched irrespective of case sensitivity
                                                        if (strcasecmp($blankAnswer, $userBlankAnswers[$key]) === 0) {
                                                            $isTrue = true;
                                                        } else {
                                                            $isTrue = false;
                                                        }
                                                    }
                                                }
                                                $questionTitle = $question->title;
                                                $count = 0;
                                                $ocurrencesToReplace = 1;
                                                $textToReplace = '{blank}';
                                                while (strpos($questionTitle, $textToReplace) !== false) {
                                                    $pos = strpos($questionTitle, $textToReplace);
                                                    if ($pos !== false) {
                                                        $replacementText = '<textarea type="text" name="question[' . $question->id . '][answer][' . $count . ']" required class="questionAnswerField textareaField form-control" ' . ($isTrue ? ' readonly' : '') . '>' . ($isTrue ? $userBlankAnswers[$count] : '') . '</textarea>';

                                                        $questionTitle = substr_replace($questionTitle, $replacementText, $pos, strlen($textToReplace));
                                                    }
                                                    $count++;
                                                }
                                            @endphp
                                            {!! $questionTitle !!}
                                        </div>
                                    @elseif($question->type === \App\Models\QuizzesQuestion::$matchingListText)
                                        {{-- php starts from here --}}
                                        @php
                                        $answers = json_decode($question->correct, true);
                                        $userTextMatchingAnswers = isset($userAnswers[$question->id]['answer']) ? $userAnswers[$question->id]['answer'] : null;
                                        $isTrue = false;
                                        $texts = [];
                                        $descriptions = [];
                                        if ($answers && is_array($answers) && (!empty($userTextMatchingAnswers) && is_array($userTextMatchingAnswers))) {
                                            foreach ($answers as $key => $answer) {
                                                if ($answer['description'] === $userTextMatchingAnswers[$key]) {
                                                    $isTrue = true;
                                                } else {
                                                    $isTrue = false;
                                                }

                                            }
                                        }
                                        if ($isTrue && $userTextMatchingAnswers) {
                                            foreach ($userTextMatchingAnswers as $index => $userAnswer) {
                                                $texts[$index] = $answers[$index]['text'];
                                                $descriptions[$index] =  $userAnswer;
                                            }
                                        } else {
                                            foreach ($answers as $key => $answer) {
                                                $texts[$key] = $answer['text'];
                                                $descriptions[$key] = $answer['description'];
                                            }
                                            shuffle($descriptions);
                                        }
                                        @endphp
                                        {{-- php ends here --}}
                                        <div
                                            class="container-fluid mx-10 matching_question question_parent question_parent_{{ $question->id }}">
                                            <div class="row mt-10 px-20">
                                                <div class="col-3 text-right ml-auto">
                                                    @if(!$isTrue)
                                                    <button class="btn btn-sm btn-warning" type="button"
                                                        onclick="resetMatchingQuestion({{ $question->id }})">{{ trans('quiz.reset') }}
                                                    </button>
                                                    @endif

                                                </div>
                                            </div>
                                            <div class="row mt-35 pt-35 px-35">

                                                {{-- List of options --}}
                                                <div class="col-12 w-100" style="min-height:6rem">
                                                    @if (!$isTrue)
                                                        <ul id="initial_droppable_{{ $question->id }}"
                                                            class="col-12 droppable initial_droppable position-absolute w-100 h-100">
                                                            @php $count = 0; @endphp
                                                            @foreach ($descriptions as $description)
                                                                <li id="question_{{ $question->id }}_{{ $count }}"
                                                                    style="z-index:100;"
                                                                    class='draggable draggable-answer question_{{ $question->id }}_option btn btn-sm btn-primary'
                                                                    data-initialTarget="initial_droppable_{{ $question->id }}"
                                                                    data-description="{{ $description }}">
                                                                    {{ $description }}</li>
                                                                @php $count++; @endphp
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col">#</th>
                                                                    <th scope="col" class="text-left">
                                                                        {{ trans('quiz.text') }}</th>
                                                                    <th scope="col">{{ trans('quiz.userAnswer') }}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($userTextMatchingAnswers as $key => $pair)
                                                                    <tr class="bg-success">
                                                                        <th scope="row">{{ $key + 1 }}</th>
                                                                        <td>{{ $answers[$key]['text'] }}</td>
                                                                        <td class="text-center">
                                                                            {{ $pair }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                        @foreach ($texts as $key => $value)
                                                        <div class="row matching-answer-row mt-35 pt-35 px-35 d-none">
                                                            <div class="heading-col col-4">{{ $value }}</div>
                                                            <div class="col-8">
                                                                <div id="question_{{ $question->id }}_optionDiv"
                                                                    class="w-100 answer-col position-absolute droppable ui-state-empty">
                                                                    <input type="hidden"
                                                                        name="question[{{ $question->id }}][answer][{{ $key }}]"
                                                                        class="questionAnswerField" value="{{$descriptions[$key]}}"/>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    @endif

                                                </div>
                                            </div>
                                            @if (!$isTrue)
                                            <div class="row font-weight-bold mt-35 pt-35 px-35">
                                                <div class="col-4">
                                                    <h4>{{ trans('quiz.text') }}</h4>
                                                </div>
                                                <div class="col-8">{{ trans('quiz.description') }}</div>
                                            </div>
                                            @foreach ($texts as $key => $value)
                                            <div class="row matching-answer-row mt-35 pt-35 px-35">
                                                <div class="heading-col col-4">{{ $value }}</div>
                                                <div class="col-8">
                                                    <div id="question_{{ $question->id }}_optionDiv"
                                                        class="w-100 answer-col position-absolute droppable ui-state-empty">
                                                        <input type="hidden"
                                                            name="question[{{ $question->id }}][answer][{{ $key }}]"
                                                            class="questionAnswerField"/>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                           @endif

                                        </div>
                                    @elseif($question->type === \App\Models\QuizzesQuestion::$matchingListImage)
                                        {{-- php code starts from here --}}
                                        @php
                                        $answers = json_decode($question->correct, true);
                                        $userImageMatchingAnswers = isset($userAnswers[$question->id]['answer']) ? $userAnswers[$question->id]['answer'] : null;
                                        $isTrue = false;
                                        $texts = [];
                                        $imageAnswers = [];
                                        if ($answers && is_array($answers) && (!empty($userImageMatchingAnswers) && is_array($userImageMatchingAnswers))) {
                                            foreach ($answers as $key => $answer) {
                                                if ($answer['image'] === $userImageMatchingAnswers[$key]) {
                                                    $isTrue = true;
                                                } else {
                                                    $isTrue = false;
                                                }

                                            }
                                        }
                                        if ($isTrue && $userImageMatchingAnswers) {
                                            foreach ($userImageMatchingAnswers as $index => $userAnswer) {
                                                $texts[$index] = $answer[$index]['text'];
                                                $imageAnswers[$index] =  $userAnswer;
                                            }
                                        } else {
                                            foreach ($answers as $key => $answer) {
                                                $texts[$key] = $answer['text'];
                                                $imageAnswers[$key] = $answer['image'];
                                            }
                                            shuffle($imageAnswers);
                                        }
                                        @endphp
                                        {{-- php code ends here --}}
                                        <div
                                            class="container-fluid mx-10 matching_question question_parent question_parent_{{ $question->id }}">
                                            <div class="row mt-10 px-20">
                                                <div class="col-3 text-right ml-auto">
                                                    @if (!$isTrue)
                                                    <button class="btn btn-sm btn-warning" type="button"
                                                        onclick="resetMatchingQuestion({{ $question->id }})">{{ trans('quiz.reset') }}
                                                    </button>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="row mt-35 pt-35 px-35">

                                                {{-- List of options --}}
                                                <div class="col-12 w-100 height-20rem">
                                                    @if (!$isTrue)
                                                        <ul id="initial_droppable_{{ $question->id }}"
                                                            class="row droppable initial_droppable position-absolute w-100">
                                                            @php $count = 0; @endphp
                                                            @foreach ($imageAnswers as $imageAnswer)
                                                                <li id="question_{{ $question->id }}_{{ $count }}"
                                                                    style="z-index:100; height:150px;"
                                                                    class='draggable draggable-answer question_{{ $question->id }}_option btn btn-sm btn-background-transparent'
                                                                    data-initialTarget="initial_droppable_{{ $question->id }}"
                                                                    data-description="{{ $imageAnswer }}">
                                                                    <div class="col-3">

                                                                        <img src="{{ $imageAnswer }}" width="auto"
                                                                            height="auto"
                                                                            style="max-width:150px; max-height:150px" />
                                                                    </div>
                                                                </li>
                                                                @php $count++; @endphp
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">#</th>
                                                                <th scope="col" class="text-left">
                                                                    {{ trans('quiz.text') }}</th>
                                                                <th scope="col">{{ trans('quiz.userAnswer') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($userImageMatchingAnswers as $key => $pair)
                                                                <tr class="bg-success">
                                                                    <th scope="row">{{ $key + 1 }}</th>
                                                                    <td>{{$answers[$key]['text'] }}</td>
                                                                    <td class="text-center">
                                                                        <img src="{{$pair }}" width="auto"
                                                                            height="auto"
                                                                            style="max-width:150px; max-height:150px" />
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                    @foreach ($texts as $key => $value)
                                                    <div class="row matching-answer-row mt-35 pt-35 px-35 d-none">
                                                        <div class="heading-col col-4">{{ $value }}</div>
                                                        <div class="col-8">
                                                            <div id="question_{{ $question->id }}_optionDiv"
                                                                class="w-100 answer-col position-absolute droppable ui-state-empty">
                                                                <input type="hidden"
                                                                    name="question[{{ $question->id }}][answer][{{ $key }}]"
                                                                    class="questionAnswerField" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                            @if (!$isTrue)
                                            <div class="row font-weight-bold mt-35 pt-35 px-35">
                                                <div class="col-4">
                                                    <h4>{{ trans('quiz.text') }}</h4>
                                                </div>
                                                <div class="col-8">{{ trans('quiz.image') }}</div>
                                            </div>
                                            @foreach ($texts as $key => $value)
                                                <div class="row matching-answer-row mt-35 pt-35 px-35">
                                                    <div class="heading-col col-4">{{ $value }}</div>
                                                    <div class="col-8">
                                                        <div id="question_{{ $question->id }}_optionDiv"
                                                            class="w-100 answer-col position-absolute droppable ui-state-empty">
                                                            <input type="hidden"
                                                                name="question[{{ $question->id }}][answer][{{ $key }}]"
                                                                class="questionAnswerField" />
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            @endif
                                        </div>
                                    @elseif($question->type === \App\Models\QuizzesQuestion::$fileUpload)
                                        <div class="form-group mt-35">
                                            {{-- <textarea name="question[{{ $question->id }}][answer]" rows="15" class="questionAnswerField form-control">{{(isset($prefilledQuestions[$question->id]['answer']) && strlen(trim($prefilledQuestions[$question->id]['answer'])) > 0) ? trim($prefilledQuestions[$question->id]['answer']) : ""}}</textarea> --}}
                                            <input type="file" class="questionAnswerField form-control"
                                                name="question[{{ $question->id }}][answer][]" multiple />
                                            <input type="hidden" class="questionAnswerField form-control"
                                                name="question[{{ $question->id }}][type]"
                                                value="{{ \App\Models\QuizzesQuestion::$fileUpload }}" />
                                        </div>
                                    @elseif($question->type === \App\Models\QuizzesQuestion::$informationText)
                                        <div class="form-group mt-35">
                                            <h3 class="font-16 text-secondary">{!! $question->title !!}</h3>
                                        </div>
                                    @else
                                        <div data-questionId="{{ $question->id }}"
                                            class="multi_question_{{ $question->id }} question-multi-answers mt-35">
                                            @if (!empty($userAnswer))
                                                @foreach ($question->quizzesQuestionsAnswers as $key => $answer)
                                                    @if ((int) $userAnswer === $answer->id)
                                                        <div class="answer-item">
                                                            <input id="asw-{{ $answer->id }}" type="radio"
                                                                name="question[{{ $question->id }}][answer]"
                                                                value="{{ $answer->id }}"
                                                                @if (isset($prefilledQuestions[$question->id]['answer']) &&
                                                                        (int) $prefilledQuestions[$question->id]['answer'] === (int) $answer->id) checked="checked"
                                                    @else
                                                    {{ (!empty($userAnswer) and (int) $userAnswer === $answer->id) ? 'checked' : '' }} @endif>
                                                            @if (!$answer->image)
                                                                <label for="asw-{{ $answer->id }}"
                                                                    class="answer-label font-16 text-dark-blue d-flex align-items-center justify-content-center">
                                                                    <span class="answer-title">
                                                                        {{ $answer->title }}
                                                                    </span>
                                                                </label>
                                                            @else
                                                                <label for="asw-{{ $answer->id }}"
                                                                    class="answer-label font-16 text-dark-blue d-flex align-items-center justify-content-center">
                                                                    <div class="image-container">
                                                                        <img src="{{ config('app_url') . $answer->image }}"
                                                                            class="img-cover" alt="">
                                                                    </div>
                                                                </label>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @else
                                                @foreach ($question->quizzesQuestionsAnswers as $key => $answer)
                                                    {{-- {{(isset($prefilledQuestions[$question->id]['grade']) && $prefilledQuestions[$question->id]['grade'] > 0) ?  'disabled' : ''}} --}}
                                                    <div class="answer-item">
                                                        <input id="asw-{{ $answer->id }}" type="radio"
                                                            name="question[{{ $question->id }}][answer]"
                                                            value="{{ $answer->id }}"
                                                            @if (isset($prefilledQuestions[$question->id]['answer']) &&
                                                                    (int) $prefilledQuestions[$question->id]['answer'] === (int) $answer->id) checked="checked"
                                                    @else
                                                    {{ (!empty($userAnswer) and (int) $userAnswer === $answer->id) ? 'checked' : '' }} @endif>
                                                        @if (!$answer->image)
                                                            <label for="asw-{{ $answer->id }}"
                                                                class="answer-label font-16 text-dark-blue d-flex align-items-center justify-content-center">
                                                                <span class="answer-title">
                                                                    {{ $answer->title }}
                                                                </span>
                                                            </label>
                                                        @else
                                                            <label for="asw-{{ $answer->id }}"
                                                                class="answer-label font-16 text-dark-blue d-flex align-items-center justify-content-center">
                                                                <div class="image-container">
                                                                    <img src="{{ config('app_url') . $answer->image }}"
                                                                        class="img-cover" alt="">
                                                                </div>
                                                            </label>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </fieldset>
                    @endforeach

                    <div id="quizErrorAlert" class="alert alert-warning mt-2 py-2 d-none">

                    </div>

                    <div class="d-flex align-items-center mt-30">
                        <div class="lessonsList" style="display:none">
                            <x-course.sidebar :webinarId=$webinarId :quizId='$quiz->id' dropDownListing="true" />
                        </div>

                        @if (!empty($previousLesson))
                            <span
                                class="goPreviousLesson btn btn-primary btn-sm mr-20">{{ trans('public.previous_lesson') }}</span>
                        @endif
                        @if ($quizQuestions->count() > 1)
                            <button type="button"
                                class="previous saveProgress previousBtn btn btn-sm btn-primary mr-20 p-0">{{ trans('quiz.previous_question') }}</button>
                            <button type="button"
                                class="next nextBtn saveProgress btn btn-sm btn-primary mr-auto p-0">{{ trans('quiz.next_question') }}</button>
                        @else
                            <span class="previous mr-20 p-0"></span>
                            <span class="next mr-auto p-0"></span>
                        @endif

                        <button type="button" class="btn btn-sm btn-gray mr-1 saveBtn saveProgress">Save</button>
                        <button type="submit" name="finishQuizBtn"
                            class="btn mr-1 finish btn-sm btn-gray">Submit</button>
                        <button type="submit" name="finishQuizBtn"
                            class="finish finishBtn btn btn-sm btn-danger p-0">{{ trans('public.finish') }}</button>
                    </div>
                </form>
            </section>
        </div>
    @endif
@endsection

@push('styles_top')
    @if (Request::segment(4) == 'start')
        <style>
            input[type="radio"]:checked+label {
                box-shadow: 0 10px 30px 0 rgba(25, 135, 84, 0.7);
                border: 3px solid #198754 !important;
                background-color: #fff;
                transition: all .3s ease !important;
            }
        </style>
    @endif
    <style>
        .goPreviousLesson {
            background: #3589a1;
            box-shadow: none;
            border: none;
        }

        .goPreviousLesson:hover {
            background: #3589a1;
        }

        .textareaField {
            width: 100% !important;
            resize: both !important;
            display: block !important;
        }

        .matching-answer-row,
        .matching-answer-row .heading-col,
        .matching-answer-row .answer-col {
            min-height: 3rem;
        }

        .matching-answer-row .heading-col {
            border-right: 2px dotted black;
            border-width: 80%;
        }

        .ui-state-highlight {
            border: 1px solid #dad55e;
            background: #fffa90;
            color: #777620;
        }

        .ui-state-error {
            border: 1px solid #f1a899;
            background: #fddfdf;
            color: #5f3f3f;
        }

        .ui-state-empty,
        .initial_droppable {
            border: 1px solid black;
            background: lightgrey;
            color: black;
        }

        .height-20rem {
            height: 20rem;
            min-height: 20rem;
        }

        .question_87_option {
            margin-top: 30px;
            height: auto;
            max-height: 120px;
        }

        .finishBtn {
            display: none;
        }
    </style>
@endpush

@push('scripts_bottom')
    <script src="/assets/default/vendors/jquery.simple.timer/jquery.simple.timer.js"></script>
    <script src="/assets/default/js/parts/quiz-start.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="/assets/default/js/jquery_ui.js"></script>
    @if (empty($invalid))
        <script>
            $(document).ready(function() {

                var currentQuestion = 1
                $(".saveBtn").on("click", function() {
                    Swal.fire({
                        position: 'top-center',
                        icon: 'success',
                        title: 'Your answers have been saved',
                        showConfirmButton: false,
                        timer: 1000
                    })
                })

                $(".nextBtn").on("click", function() {
                    var quizCount = {{ $quizQuestions->count() }}
                    if (currentQuestion < quizCount) {
                        currentQuestion = currentQuestion + 1
                    }
                    if (quizCount == currentQuestion) {
                        $(".finishBtn").show()
                    }
                })

                $(".previousBtn").on("click", function() {
                    if (currentQuestion > 1) {
                        currentQuestion = currentQuestion - 1
                    }
                    $(".finishBtn").hide()
                })

                @if (!empty($prefilledQuestions))
                    allQuestionsAttempted();
                    $("#quizErrorAlert").addClass("d-none");
                @endif
                $(".saveProgress").on("click", function() {
                    var str = $("#quizForm").serialize();
                    var actionLink = $("#quizForm").attr("action")
                    $.ajax({
                        type: "POST",
                        data: str,
                        url: "{{ route('panel.quiz.submit', ['id' => $quiz->id]) }}",
                        success: function(response) {
                            /* On success response */
                        },
                        error: {
                            /* On error */
                        }
                    })
                })
            })

            $(function() {
                $(".draggable").each(function() {
                    $(this).attr('data-originalLeft', $(this).css('left'));
                    $(this).attr('data-originalTop', $(this).css('top'));
                });
                $(".draggable").draggable({
                    zIndex: 100,
                    snap: ".droppable",
                    snapMode: "inner",
                    scope: "matching_question",
                    revert: "invalid",
                });
                $(".droppable").droppable({
                    accept: ".draggable-answer",
                    tolerance: "touch",
                    scope: "matching_question",
                    drop: function(event, ui) {
                        if (!$(this).hasClass('initial_droppable')) {
                            let inputVal = $(this).find("input").val();
                            if (inputVal.length > 0 && inputVal !== "") {
                                ui.draggable.draggable("option", "revert", true);
                            } else {
                                let description = ui.draggable.attr("data-description");
                                $(this)
                                    .addClass("ui-state-highlight")
                                    .removeClass("ui-state-error")
                                    .removeClass("ui-state-empty")
                                    .find("input")
                                    .val(description);
                                $(this).attr('data-inputVal', description);
                                $(this).droppable('disable');
                            }
                        }
                    }
                });
            });

            function resetMatchingQuestion(questionId) {
                $(`.question_parent_${questionId} .droppable:not(.initial-droppable)`).droppable('disable');
                $(`.question_parent_${questionId} .draggable`).each(function() {
                    $(this).position({
                        of: "#" + $(this).attr("data-initialTarget")
                    });
                    $(this).css({
                        'left': '0px'
                    });
                });
                $(`.question_parent_${questionId} .droppable:not(initial-droppable)`).each(function() {
                    $(this).removeClass("ui-state-highlight")
                        .removeClass("ui-state-error")
                        .addClass("ui-state-empty");
                    $('input', $(this)).val("");

                });
                $(`.question_parent_${questionId} .droppable:not(initial-droppable)`).droppable('enable');
            }

            /**
             * Method to validate whether all non-multiple questions have been attempted.
             * Control will automatically be transferred to any question that hasn't been attempted yet
             *
             * @return boolean
             */
            function questionAnswersValidation() {
                //check if all non-multiple questions have been answered
                let answersFilled = true;
                $(`.questionAnswerField`).each(function() {

                    let inputVal = $(this).val();
                    if (
                        (inputVal.length < 1 || inputVal == "") ||
                        ($(this).attr('type') == 'file' && $(this)[0].files.length === 0)
                    ) {
                        $('#quizErrorAlert').text('{{ trans('quiz.all_questions_not_attempted') }}');
                        $('#quizErrorAlert').removeClass('d-none');
                        let errorFieldset = $(this).closest('.question-step');
                        let errorQuestionNum = errorFieldset.attr('data-questionNum');
                        let questionSerial = errorFieldset.attr('data-questionSerial');

                        let currentFieldSetSerial = $(`.question-step.activeFieldset`).attr('data-questionSerial');
                        if (questionSerial > currentFieldSetSerial) {
                            while (questionSerial > currentFieldSetSerial) {
                                $('button.next').click();
                                currentFieldSetSerial++;
                            }
                        } else if (questionSerial < currentFieldSetSerial) {
                            while (questionSerial < currentFieldSetSerial) {
                                $('button.previous').click();
                                currentFieldSetSerial--;
                            }
                        }
                        answersFilled = false;
                        return false;
                    }
                });
                return answersFilled;
            }

            /**
             * Method to validate whether all Multiple-type questions have been attempted.
             * Control will automatically be transferred to any question that hasn't been attempted yet
             *
             * @return boolean
             */
            function multipleQuestionsValidation() {
                let answersFilled = true;
                let multiQuestions = [];
                $(`.question-multi-answers`).each(function() {
                    let questionId = $(this).attr('data-questionId');
                    if (!multiQuestions.includes(questionId)) {
                        multiQuestions.push(questionId);
                    }
                });
                //check if there's at least one checked element in retrieved questions
                $.each(multiQuestions, function(index, val) {
                    //val is the questionId
                    if (!$(`.multi_question_${val} input[type="radio"]`).is(':checked')) {
                        $('#quizErrorAlert').text('{{ trans('quiz.all_questions_not_attempted') }}');
                        $('#quizErrorAlert').removeClass('d-none');
                        let questionSerial = $(`.question_fieldset_${val}`).attr('data-questionSerial');
                        let currentFieldSetSerial = $(`.question-step.activeFieldset`).attr('data-questionSerial');
                        if (questionSerial > currentFieldSetSerial) {
                            while (questionSerial > currentFieldSetSerial) {
                                $('button.next').click();
                                currentFieldSetSerial++;
                            }
                        } else if (questionSerial < currentFieldSetSerial) {
                            while (questionSerial < currentFieldSetSerial) {
                                $('button.previous').click();
                                currentFieldSetSerial--;
                            }
                        }
                        answersFilled = false;
                        return false;
                    }
                });

                return answersFilled;
            }

            /**
             * Method to validate whether Entire Assessment has been attempted.
             * Control will automatically be transferred to any question that hasn't been attempted yet
             *
             * @return boolean
             */
            function allQuestionsAttempted() {
                //check if all non-multiple questions have been answered
                let answersFilled = questionAnswersValidation();
                if (!answersFilled) {
                    return false;
                }

                //check if any multiple questions have been missed out
                let multiQuestionFilled = multipleQuestionsValidation();
                if (!multiQuestionFilled) {
                    return false;
                }

                //if we are here, then all questions were answered
                return true;
            }

            $(`#quizForm .finish.btn`).on('click', function() {
                $(this).show()
                $('#quizErrorAlert').text('');
                $(".nextBtnClick").val(0);
                $('#quizErrorAlert').addClass('d-none');
                //check if all quiz questions have been attempted
                let quizAttempted = allQuestionsAttempted();

                if (!quizAttempted) {
                    return false;
                }

                return true; //if we are here, then all questions have been answered
            });
        </script>
    @endif
@endpush
