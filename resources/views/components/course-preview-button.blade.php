<div>
    <a id='previewCourse' name='previewCourse' href="javascript:;" {{ $attributes->merge(['class' => 'btn btn-outline-primary mt-2']) }} role='button'
        target="_blank">
        {{ trans('admin/main.preview_course') }}
    </a>
</div>
@push('scripts_bottom')
<script>
    $(document).ready(function() {

        $('select#course').change(function() {
            let courseId = $(this).val();
            selectedCourseOption = $("select#course option:selected");
            let courseUrl = selectedCourseOption.attr('data-url');
            if (
                typeof courseUrl === 'undefined' || !courseUrl ||
                typeof courseId === 'undefined' || !courseId
            ) {
                $('#previewCourse').attr('href', 'javascript:;');
                $('#previewCourse').removeAttr('target');
                console.log("here");
                return false;
            }

            $('#previewCourse').attr('href', courseUrl);
            $('#previewCourse').attr('target', '_blank');
        });
        $('select#course').trigger('change');

    });
</script>
@endpush
