@push('styles_top')
    <style>
        #student-declaration p {
            margin: unset;
            margin-bottom:1rem;
        }
        ul.declaration-ul li {
            margin: unset;
            margin-left:2em;
            padding-left:1em;
            list-style:unset;
        }
        #container-text {
            padding-left:1rem;
        }
    </style>
@endpush
<section id="student-declaration">
    @if(session()->has("no-match"))
<div class="alert alert-danger text-center text-white"> {{ session()->get("no-match") }} </div>
@endif
    <h3 class="section-title after-line mt-35">{{ trans('panel.student_declaration_heading') }}</h3>
    <div id="container-text" class="mt-40">
        {!! trans('declaration.consent_text') !!}
    </div>

    <div class="container-fluid mt-40">
        <div class="row">
            <div class="col col-6 form-inline">
                <label class="font-weight-bold">
                    {{ trans('declaration.student_name') }}:  &nbsp;
                </label>

                <span id="student_name_span" name="student_name_span"></span>
            </div>
            <div class="col col-6 form-inline">
                <label class="font-weight-bold">
                    {{ trans('declaration.date') }}: &nbsp;
                </label>
                {{date('Y-m-d', time())}}
            </div>
        </div>
    </div>

</section>

@push('scripts_bottom')
    <script>
        $(document).ready(function() {
            $("#student_name").on('keyup', function() {
                $("#student_name_span").html($(this).val());
            });
            $("#student_name").trigger('keyup');

            $("#student_name").blur(function(){
                var original_name = '<?php echo auth()->user()->full_name; ?>'
                var given_name = $(this).val()
                const result = original_name.toUpperCase() === given_name.toUpperCase();
            });
        })
    </script>
@endpush
