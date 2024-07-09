<div class="d-flex">
        <select class="lessonsListing" name="formal">
            @if (!empty($textLessonChapters))
                <div class="shadow-sm rounded-lg bg-white px-15 px-md-25 py-20 mt-30">
                    <h3 class="category-filter-title font-16 font-weight-bold text-dark-blue">
                        {{ trans('public.course_sessions') }}</h3>
                    <div class="p-0 m-0 pt-10">
                        @foreach ($textLessonChapters as $textLessonChapter)
                            @foreach ($textLessonChapter->textLessons as $lesson)
                            @php
                                $lessonUrl = !empty($courseUrl) ? "{$courseUrl}/lessons/{$lesson->id}/read" : "";
                            @endphp
                            @if($lesson->drip_feed == 0 || userHasDripFeedAccess($lesson->id, auth()->user()->id))
                            <option class="currentOption" value="{{ $lessonUrl }}" {{ $lesson->id == $quizLessonId ? "selected" : "" }}>
                                {{ $lesson->title }}
                                @if($lesson->id === $quizLessonId)
                                    @php break; @endphp
                                @endif
                            </option>
                            @endif
                            @endforeach
                        @endforeach
                    </div>
                </div>
            @endif
        </select>
        <span class="openLesson btn btn-sm btn-secondary mr-1">Open</span>
        <span class="btn btn-sm btn-danger closeListing mr-20">X</span>
</div>
@push("styles_top")
<style>
    .lessonsListing {
        width: 275px;
        padding: 7px;
    }
</style>
@endpush

@push("scripts_bottom")
 <script>
    $(document).ready(function(){
        $(".closeListing").on("click", function(){
                $(".goPreviousLesson").show(1000)
                $(".lessonsList").hide(1000)
            })

            $(".goPreviousLesson").on("click", function(){
                $(this).hide(1000)
                $(".lessonsList").show(1000)
            })

            $(".openLesson").on("click", function(){
                var val = $(".lessonsListing").val();
                window.location = val
            })
    })
 </script>
@endpush
