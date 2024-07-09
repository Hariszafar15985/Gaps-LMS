@if($quizzesResults->count() > 0)
            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive">
                            <table class="table custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('public.instructor') }}</th>
                                    <th>{{ trans('quiz.quiz') }}</th>
                                    <th class="text-center">{{ trans('quiz.quiz_grade') }}</th>
                                    <th class="text-center">{{ trans('quiz.my_grade') }}</th>
                                    <th class="text-center">{{ trans('quiz.checked_by') }}</th>
                                    <th class="text-center">{{ trans('public.status') }}</th>
                                    <th class="text-center">{{ trans('public.date') }}</th>
                                    @if (!isset($disableActions) || !$disableActions)
                                        <th></th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($quizzesResults as $result)
                                    <tr>
                                        <td class="text-left">
                                            <div class="user-inline-avatar d-flex align-items-center">
                                                <div class="avatar">
                                                    <img src="{{ $result->quiz->creator->getAvatar() }}" class="img-cover" alt="">
                                                </div>
                                                <div class=" ml-5">
                                                    <span class="d-block">{{ $result->quiz->creator->full_name }}</span>
                                                    <span class="mt-5 font-12 text-gray d-block">{{ $result->quiz->creator->email }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-left">
                                            <span class="d-block">{{ $result->quiz->title }}</span>
                                            <span class="font-12 text-gray d-block">{{ $result->quiz->webinar->title }}</span>
                                        </td>
                                        <td class="align-middle">{{ $result->quiz->quizQuestions->sum('grade') }}</td>

                                        <td class="align-middle">{{ $result->user_grade }}</td>

                                        @php
                                            $checkingInstructor = $result->assessingTeacher()->first();
                                        @endphp
                                        <td class="align-middle">{{ 
                                            (isset($checkingInstructor->full_name) && strlen($checkingInstructor->full_name) > 0) ? ucwords(trim($checkingInstructor->full_name)) : "" 
                                        }}</td>
                                        <td class="align-middle">
                                        <span class="d-block text-{{ ($result->status == 'passed') ? 'primary' : ($result->status == 'waiting' ? 'warning' : 'danger') }}">
                                            {{ trans('quiz.'.$result->status) }}
                                        </span>

                                            @if($result->status =='failed' and $result->can_try)
                                                <span class="d-block font-12 text-gray">{{ trans('quiz.quiz_chance_remained',['count' => $result->count_can_try]) }}</span>
                                            @endif
                                            @if (isset($disableActions) && $disableActions)
                                                @if($result->status != 'waiting')
                                                    <a target="_blank" 
                                                    href="{{ (auth()->user()->isAdmin()) ? route('admin_quiz_result_detail', ['quizResultId' => $result->id]) : route('panel_quiz_result_detail', ['quizResultId' => $result->id])}}" 
                                                    class="webinar-actions d-block mt-10">{{ trans('public.view_answers') }}</a>
                                                @endif
                                            @endif
                                        </td>

<td class="align-middle">{{ dateTimeFormat($result->created_at,'F j, Y ')}} at {{ dateTimeFormat($result->created_at,'h:i a')}}</td>



                                        @if (!isset($disableActions) || !$disableActions)
                                        <td class="align-middle text-right font-weight-normal">
                                            <div class="btn-group dropdown table-actions table-actions-lg table-actions-lg">
                                                <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    {{-- @if(!$result->can_try and $result->status != 'waiting') --}}
                                                    @if($result->status != 'waiting')
                                                        <a href="{{route('panel_quiz_result_detail', ['quizResultId' => $result->id])}}" class="webinar-actions d-block mt-10">{{ trans('public.view_answers') }}</a>
                                                    @endif

                                                    @if($result->status != 'passed')
                                                        @if($result->can_try)
                                                            <a href="/panel/quizzes/{{ $result->quiz->id }}/start" class="webinar-actions d-block mt-10">{{ trans('public.try_again') }}</a>
                                                        @endif
                                                    @endif

                                                    <a href="{{ $result->quiz->webinar->getUrl() }}" class="webinar-actions d-block mt-10">{{ trans('webinars.webinar_page') }}</a>
                                                </div>
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'result.png',
                'title' => trans('quiz.quiz_result_no_result'),
                'hint' => trans('quiz.quiz_result_no_result_hint'),
            ])
        @endif