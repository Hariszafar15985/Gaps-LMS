<div class="rounded-sm shadow-lg border mt-20 p-15">
    {{-- @dd($quiz->id) --}}
    <div class="row align-items-center">
        <div class="col-sm-12 col-md-6 d-flex align-items-center">

            <span class="mr-15 d-flex">
                <i data-feather="award" width="20" height="20"class="text-gray"></i>
            </span>

            <div class="">
                <span class="font-weight-bold font-14 text-secondary d-block">{{ $quiz->title }}</span>
                <span class="font-12 text-gray d-block">{{ $quiz->quizQuestions->count() }}
                    {{ trans('public.questions') }}, @if($quiz->time) {{ $quiz->time }} {{ trans('public.min') }} @endif
                </span>
            </div>
        </div>

        <div class="col-12 col-md-3 text-secondary font-14 file-title text-left d-flex align-items-center">
            Quiz
        </div>
        <div class="col-12 col-md-3 text-gray file-title text-left d-flex align-items-center">


            @php
                $result = \App\Helpers\WebinarHelper::studentQuizResult($quiz->id, $student->id);
                // dd($result);
            @endphp

            @if ($result == 'passed')
                <span class="font-14 font-weight-bold text-secondary">{{ trans('quiz.passed') }}</span>
            @elseif ($result == 'failed')
                <span class="font-14 text-gray">{{ trans('quiz.failed') }}</span>
            @elseif ($result == 'waiting')
                <span class="font-14 text-gray">{{ trans('quiz.waiting') }}</span>
            @elseif ($result == 'attempting')
                <span class="font-14 text-gray">{{ trans('quiz.attempting') }}</span>
            @else
                <span class="font-14 text-gray">{{ trans('quiz.notAttempted') }}</span>
            @endif

        </div>

    </div>
</div>
