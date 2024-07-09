<section class="mt-40">
    <div class="col-12 col-md-12">
        {{ $error }}
    </div>
    {{-- @if(isset($nextLessonId) && !empty($nextLessonId))
        <div class="col-12 mt-2 text-center">
            <a href="{{ url('/course/' . $courseSlug ) . '/lessons/' . $nextLessonId . '/read' }}"
                class="btn btn-sm btn-primary">
                {{ trans('public.next_lesson') }}
            </a>               
        </div>
    @endif --}}
</section>