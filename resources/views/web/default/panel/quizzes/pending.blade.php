@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h2>{{ trans('admin/pages/quizResults.quiz_result_pending_list_page_title') }}</h2>
        </div>

        @if($quizzesResults->count() > 0)

            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive">
                            <table class="table custom-table">
                                <thead>
                                    <tr>
                                        <th class="text-left">{{ trans('admin/main.title') }}</th>
                                        <th class="text-left">{{ trans('quiz.student') }}</th>
                                        <th class="text-left">{{ trans('admin/main.instructor') }}</th>
                                        <th class="text-left">{{ trans('admin/main.organization') }}</th>
                                        <th class="text-center">{{ trans('admin/main.grade') }}</th>
                                        <th class="text-center">{{ trans('admin/main.quiz_date') }}</th>
                                        <th class="text-center">{{ trans('admin/main.status') }}</th>
                                        <th>{{ trans('admin/main.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($quizzesResults as $result)
                                    <tr>
                                        <td class="text-left">
                                            <span>{{ isset($result->quiz->title) ? $result->quiz->title : "" }}</span>
                                            <small class="d-block text-left text-primary">({{ isset($result->quiz->webinar->title) ? ucwords(trim($result->quiz->webinar->title)) : "" }})</small>
                                        </td>
                                        <td class="text-left">{{ isset($result->user->full_name) ? ucwords(trim($result->user->full_name)) : "" }}</td>
                                        <td class="text-left">
                                            {{ isset($result->quiz->teacher->full_name) ? ucwords(trim($result->quiz->teacher->full_name)) : "" }}
                                        </td>
                                        <td class="text-left">
                                            {{ isset($result->quiz->teacher->organization->full_name) ? ucwords(trim($result->quiz->teacher->organization->full_name)) : "" }}
                                        </td>
                                        <td class="text-center">
                                            <span>{{ $result->user_grade }}</span>
                                        </td>
                                        <td class="text-center">{{ dateTimeformat($result->created_at, 'j F Y') }}</td>
                                        <td class="text-center">
                                            @switch($result->status)
                                                @case(\App\Models\QuizzesResult::$passed)
                                                <span class="text-success">{{ trans('quiz.passed') }}</span>
                                                @break

                                                @case(\App\Models\QuizzesResult::$failed)

                                                <span class="text-danger">{{ trans('quiz.failed') }}</span>
                                                @break

                                                @case(\App\Models\QuizzesResult::$waiting)
                                                <span class="text-warning">{{ trans('quiz.waiting') }}</span>
                                                @break

                                            @endswitch
                                        </td>

                                    
                                        <td class="align-middle text-right">
                                            <div class="btn-group dropdown table-actions table-actions-lg table-actions-lg">
                                                <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20"></i>
                                                </button>
                                                <div class="dropdown-menu font-weight-normal">
                                                    @if($result->status != 'waiting')
                                                        <a href="/panel/quizzes/{{ $result->id }}/result" class="webinar-actions d-block mt-10">{{ trans('public.view') }}</a>
                                                    @endif

                                                    @if($result->status == 'waiting')
                                                        <a href="/panel/quizzes/{{ $result->id }}/edit-result" class="webinar-actions d-block mt-10">{{ trans('public.review') }}</a>
                                                    @endif

                                                    <a href="/panel/quizzes/results/{{ $result->id }}/delete" class="webinar-actions d-block mt-10 delete-action">{{ trans('public.delete') }}</a>
                                                </div>
                                            </div>
                                        </td>
                                        
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
    </section>
    
    <div class="my-30">
        {{ $quizzesResults->links('vendor.pagination.panel') }}
    </div>
@endsection

@push('scripts_bottom')

@endpush
