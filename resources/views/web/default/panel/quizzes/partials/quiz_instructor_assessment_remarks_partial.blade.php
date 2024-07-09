@if(!$authUser->isUser())

<div data-questionId="{{ $question->id }}" class="assessment-div form-group text-right mt-35">
    <input  class="form-control questionAssessmentInput d-none" type="text" name="question[{{ $question->id }}][assessment]" value="{{ (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["assessment"])) ? $userAnswers[$question->id]["assessment"] : "" }}" />
    @if ( empty($userAnswers[$question->id]["assessment"]))
        <button type="button" value="{{ trans('quiz.passed') }}"
            {{( empty($userAnswers[$question->id]["assessment"]) || (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["assessment"]) and $userAnswers[$question->id]["assessment"] === trans('quiz.passed'))) ? "" : "disabled"}}
            class="assessment-btn {{ trans('quiz.passed') }} btn btn-sm btn-primary mr-20 px-1">{{ trans('quiz.passed') }}</button>
        <button type="button" value="{{ trans('quiz.failed') }}"
            {{(empty($userAnswers[$question->id]["assessment"]) || (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["assessment"]) and $userAnswers[$question->id]["assessment"] === trans('quiz.failed'))) ? "" : "disabled"}}
            class="assessment-btn {{ trans('quiz.failed') }} btn btn-danger btn-sm mr-auto px-1">{{ trans('quiz.failed') }}</button>
    @endif
    <span id="assessment_text_{{ $question->id }}"
        @if(!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["assessment"]))
        class="float-right font-weight-bold mb-3
            {{($userAnswers[$question->id]["assessment"] === trans('quiz.passed')) ? " text-primary " : " text-danger "}}
        "
        @endif
        >{{(!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["assessment"])) ? $userAnswers[$question->id]["assessment"]."!" : ""}}</span>
</div>
<div id="instructor_remarks_{{ $question->id }}" class="form-group
    {{(!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["instructor_remarks"]))
        || (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["assessment"]) and $userAnswers[$question->id]["assessment"] === trans('quiz.failed')
    ) ? "" : "d-none"}}">
    <label class="input-label text-secondary">{{ trans('quiz.instructor_remarks') }}</label>
    <textarea rows="5" name="question[{{ $question->id }}][instructor_remarks]"
        @if (
            (empty($newQuizStart) or !in_array($authUser->id, $allowedEditors))
            or (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["status"]) and $userAnswers[$question->id]["status"])
        )
            disabled
        @endif
        class="form-control">{{ (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["instructor_remarks"])) ? $userAnswers[$question->id]["instructor_remarks"] : "" }}</textarea>
</div>

@endif

