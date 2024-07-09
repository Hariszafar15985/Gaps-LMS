<div class="mt-30 row align-items-center">
    <div class="col-12 col-md-5">

    </div>

    @if (Request::segment(5) == 'read')
        <div class="col-12 text-right">
            @if ($showFullScreenButton)
                <button id="fullScreenBtn" class="btn btn-sm btn-danger"
                    onclick="openFullscreen();">{{ trans('public.fullscreen_mode') }}</button>
            @endif
        </div>
    @endif

    <div class="col-12 col-md-12 text-right mt-10">
        @if (!empty($previous))
            @php
                $linkP = null;
                if ($previous->type == 'text_lesson') {
                    $linkP = '/course/'.$course->slug.'/lessons/' . $previous->item_id . '/read';
                } elseif ($previous->type == 'file') {
                    if ($previous->file->file_type == 'archive') {
                        $linkP = '/course/'.$course->slug.'/file/' . $previous->item_id . '/showHtml';
                    } else {
                        $linkP = '/course/'.$course->slug.'/file/' . $previous->item_id . '/play';
                    }
                } elseif ($previous->type == 'quiz') {
                    $linkP = '/panel/quizzes/' . $previous->item_id . '/start';
                }
            @endphp
            <x-course.navigation-button :params=$linkP>
                {{-- {{ trans('public.previous_lesson') }} --}}
                Previous
            </x-course.navigation-button>
        @endif
        @if (!empty($next))
            @php
                $linkN = null;
                if ($next->type == 'text_lesson') {
                    $linkN = '/course/'.$course->slug.'/lessons/' . $next->item_id . '/read';
                } elseif ($next->type == 'file') {
                    if ($next->file->file_type == 'archive') {
                        $linkN = '/course/'.$course->slug.'/file/' . $next->item_id . '/showHtml';
                    } else {
                        $linkN = '/course/'.$course->slug.'/file/' . $next->item_id . '/play';
                    }

                } elseif ($next->type == 'quiz') {
                    $linkN = '/panel/quizzes/' . $next->item_id . '/start';
                }
            @endphp
            <x-course.navigation-button :params=$linkN>
                {{-- {{ trans('public.next_lesson') }} --}}
                Next
            </x-course.navigation-button>
        @endif

    </div>
</div>
