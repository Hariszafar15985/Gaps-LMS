@push('styles_top')
    <link rel="stylesheet" href="{{ asset('assets/vendors/summernote/summernote-bs4.min.css') }}">
    <style>
        #courseNotesBtn {
            /* background: rgb(120, 228, 87); */
            background: rgb(53 137 161);
            color: #fff;
            font-size: 21px !important;
            font-weight: 500;
            line-height: 38px;
            box-shadow: 0 0 25px rgb(23 23 23 / 25%);
            padding: 0;
            position: fixed;
            right: 45px;
            height: 75px;
            width: 75px;
            border-radius: 100%;
            text-decoration: none;
            top: 74%;
            z-index: 1029;
            letter-spacing: initial;
        }

        .note-editable {
            min-height: 260px;
        }

        #loaderBtn {
            display: none;
        }

        .showAlert {
            display: none;
        }

        #autoSaveNote {
            margin: 4px;
        }
    </style>
@endpush
<div>
    <!-- If you do not have a consistent goal in life, you can not live it in a consistent way. - Marcus Aurelius -->
    <!-- Button trigger modal -->
    <button id="courseNotesBtn" type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalCenter">
        <i class='fa fa-pencil-alt' aria-hidden="true"></i>
    </button>
    <!-- Modal -->
    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="exampleModalLongTitle">{{ trans('public.lesson_notes') }}</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div align="right">
                        <input id="autoSaveNote" value="" type="checkbox" class="">
                        <label for="autoSaveNote">Auto-save </label>
                        <i class="mx-2 fa fa-question-circle" data-toggle="tooltip" data-placement="top"
                            title="{{ trans('public.notes_auto_save_info') }}"></i>
                    </div>
                    {{-- <p class="text-danger">NOTE: if auto save is on, your note will be auto save</p> --}}

                    <div class="alert alert-success showAlert"></div>
                    <form id="notesForm">
                        @if (isset($textlesson))
                            <input type="text" name="lessonId" hidden id="lessonId" value="{{ $textLesson->id }}"
                                class="form-control">
                            <input type="text" name="webinarId" hidden id="webinarId"
                                value="{{ $textLesson->webinar_id }}" class="form-control">
                            <input type="text" name="type" hidden id="type" value="text_lesson"
                                class="form-control">
                        @endif

                        @if (isset($file))
                            <input type="text" name="fileId" hidden id="fileId" value="{{ $file->id }}"
                                class="form-control">
                            <input type="text" name="webinarId" hidden id="webinarId" value="{{ $file->webinar_id }}"
                                class="form-control">
                            <input type="text" name="type" hidden id="type" value="file"
                                class="form-control">
                        @endif

                        <textarea id="summernote" rows="5" name="description" class="summernote noteText form-control "
                            placeholder="Minimum 300 words. HTML and images supported." style=""> {{ isset($courseNotes->note_text) ? $courseNotes->note_text : '' }} </textarea>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="saveNoteBtn" class="btn btn-primary"> Save </button>
                    <button type="button" id="loaderBtn" class="btn btn-primary">
                        <div class="spinner-border text-success" role="status">
                        </div> &nbsp; Saving...
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@push('scripts_bottom')
    <script src="{{ asset('assets/vendors/summernote/summernote-bs4.min.js') }}"></script>
    <script>
        $('.summernote').summernote();
        var file = @json($file);

        function saveNotesData(data) {

            var action = "{{route('add.notes')}}";
            $.post(action, data, function(result) {
                if (result.status == "success") {
                    $("#saveNoteBtn").show()
                    $("#loaderBtn").hide()
                    $(".showAlert").show()
                    $(".showAlert").text(result.message)
                }
            }).fail(err => {
                console.log(err.responseJSON);
            });
        }

        $(document).ready(function() {
            setInterval(() => {
                if ($('#autoSaveNote').is(":checked")) {
                    var data = $("#notesForm").serialize()
                    saveNotesData(data)
                }
            }, 10000);

            $("#saveNoteBtn").on("click", function() {
                $(this).hide()
                $("#loaderBtn").show()
                var data = $("#notesForm").serialize()
                saveNotesData(data)
            })
        })
    </script>
@endpush
