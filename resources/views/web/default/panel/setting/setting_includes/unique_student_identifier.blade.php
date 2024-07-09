<section id="usi_section">
    <h3 class="section-title after-line mt-35">{{ trans('site.unique_student_identifier') }}</h3>

    <div class="row mt-20">
        <div class="col-12 col-lg-6">

            <div class="form-group">
                <label class="input-label">{{ trans('panel.sr_usi') }}</label>
                <input type="text" name="usi_number" value="{{ old('usi_number', $userInfo->usi_number ?? '') }}"
                    class="form-control @error('usi_number')  is-invalid @enderror" placeholder="" />
                @error('usi_number')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div
                class="form-group mt-30 d-flex align-items-center justify-content-between @error('can_gaps_search_usi')  is-invalid @enderror">
                <label class="cursor-pointer input-label"
                    for="newsletterSwitch">{{ trans('panel.sr_have_usi') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="can_gaps_search_usi"
                        class="custom-control-input @error('can_gaps_search_usi')  is-invalid @enderror" id="have-usi"
                        value="1" {{ isset($userInfo) && $userInfo->can_gaps_search_usi ? 'checked' : '' }}>
                    <label class="custom-control-label" for="have-usi">{{ trans('panel.yes_no') }}</label>
                </div>
            </div>
            @error('can__search_usi')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror

            <div
                class="form-group mt-30 d-flex align-items-center justify-content-between @error('rto_permission')  is-invalid @enderror">
                <label class="cursor-pointer input-label"
                    for="newsletterSwitch">{{ trans('panel.sr_dont_have_usi') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="rto_permission"
                        class="custom-control-input  @error('rto_permission')  is-invalid @enderror" id="dont-have-usi"
                        value="1" {{ isset($userInfo) && $userInfo->rto_permission ? 'checked' : '' }}>
                    <label class="custom-control-label" for="dont-have-usi">{{ trans('panel.yes_no') }}</label>
                </div>
            </div>
            @error('rto_permission')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
            @if (!isset($usiSupportingDoc) || !$usiSupportingDoc)
                <div
                    class="form-group mt-30 d-flex align-items-center justify-content-between @error('usi_doc')  is-invalid @enderror">
                    <label class="cursor-pointer input-label"
                        for="newsletterSwitch">{{ trans('panel.upload_100_points_identification') }}</label>
                    <div class="custom-control">
                        {{-- <input type="file" name="usi_doc[]" id="usi_doc" class="@error('usi_doc') is-invalid @enderror" multiple required disabled /> --}}
                        <button type="button" class="btn btn-primary m-2" data-toggle="modal"
                            data-target="#exampleModal">
                            {{ trans('public.upload_document') }}
                        </button>
                    </div>
                    {{-- <div class="mb-3">
                        <label for="formFileMultiple" class="form-label">Multiple files input example</label>
                        <input class="form-control d-none @error('usi_doc') is-invalid @enderror" type="file" id="usi_doc" name="usi_doc" multiple required disabled>
                    </div> --}}
                </div>
                @error('usi_doc')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            @else
                @if (!empty($usiDocuments))
                    {{-- @dd($usiDocuments); --}}
                    <button type="button" class="btn btn-primary m-2" data-toggle="modal" data-target="#exampleModal">
                        {{ trans('public.upload_document') }}
                    </button>
                    <table class="table table-striped">
                        <tr>
                            <th>#</th>
                            <th>{{ trans('public.title') }}</th>
                            <th>{{ trans('public.document_side') }} </th>
                            <th>{{ trans('public.type') }} </th>
                            <th>{{ trans('public.action') }} </th>
                        </tr>
                        <tbody>
                            @foreach ($usiDocuments as $usiDocument)
                                <tr>
                                    <td> {{ $loop->iteration }} </td>
                                    <td>
                                        <a download="{{ $usiDocument->document }}"
                                            href="{{ asset('store/' . $user->id . '/user_documents/' . $usiDocument->document) }}">
                                            {{ $usiDocument->title }} </a>
                                    </td>
                                    <td>
                                        {{ $usiDocument->document_side }}
                                    </td>
                                    <td>
                                        <div class="row">
                                            @if (in_array(getFileType($usiDocument->document), ['png', 'jpg', 'jpeg', 'webp']))
                                                <div class="col-12">
                                                    <i class="fa-4x text-primary fa fa-file-image"></i>
                                                </div>
                                            @elseif(in_array(getFileType($usiDocument->document), ['pdf']))
                                                <div class="col-12">
                                                    <i class="fa-4x text-danger fa fa-file-pdf"></i>
                                                </div>
                                            @elseif(in_array(getFileType($usiDocument->document), ['csv', 'xlsb', 'xls', 'xlsm', 'xlsx']))
                                                <div class="col-12">
                                                    <i class="fa-4x fa fa-file-csv"></i>
                                                </div>
                                            @elseif(in_array(getFileType($usiDocument->document), ['docs', 'docx', 'dot']))
                                                <div class="col-12">
                                                    <i class="fa-4x fa fa-file-word"></i>
                                                </div>
                                            @elseif(in_array(getFileType($usiDocument->document), ['txt']))
                                                <div class="col-12">
                                                    <i class="fa-4x fa fa-file"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ asset('store/' . $user->id . '/user_documents/' . $usiDocument->document) }}"
                                            data-id="{{ $usiDocument->id }}" class="btn btn-sm rounded btn-primary"
                                            target="_blank" title="{{ trans('public.view_document') }}"> <i
                                                class="fa fa-eye"></i> </a>
                                        <a href="{{ route('delete.docs', [$usiDocument->id]) }}"
                                            class="btn btn-sm rounded btn-danger deleteUSIDoc"
                                            title="{{ trans('public.delete_document') }}"> <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endif
        </div>
    </div>
    {{-- Bootstrap modal for uploading the usi document --}}

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Save Documents</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label for="docTitle"> {{ trans('public.title') }} </label>
                    <input name="docTitle" type="text" id="docTitle" required class="form-control mb-2">

                    <br><label for="docType"> {{ trans('public.type') }} </label>
                    <select name="docType" id="docType" required class="form-control mb-2">
                        @if (config('students.document_types'))
                            <option value="Enrolment">{{ trans('public.default') }}</option>
                            @foreach (config('students.student_can_upload_documents') as $d)
                                <option value="{{ str_replace(' ', '-', $d) }}">{{ $d }}</option>
                            @endforeach
                        @endif
                    </select>

                    <label for="docSide"> {{ trans('public.document_side') }} </label>
                    <select name="docSide" id="docSide" required class="form-control mb-2">
                        <option value="front">Front</option>
                        <option value="back">Back</option>
                    </select>

                    <label for="chooseFile"> {{ trans('public.choose_document') }}</label>
                    <input type="file" name="usi_doc[]" required id="usi_doc"
                        class="mb-2 form-control @error('usi_doc') is-invalid @enderror"required />

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-dismiss="modal">{{ trans('public.close') }}</button>
                    <button type="button" id=""
                        class="saveDocumentsBtn btn btn-primary">{{ trans('public.save') }}</button>
                </div>
            </div>
        </div>
    </div>
    {{-- End Bootstrap modal for uploading the usi document --}}
</section>



@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/fontawesome/css/all.min.css" />
    <style>
        .multifile_remove_input {
            color: red;
            text-decoration: none;
        }

        #exampleModal .error {
            color: red;
        }
    </style>
@endpush

@push('scripts_bottom')
    <script src="/assets/admin/vendor/bootstrap/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"
        integrity="sha512-rstIgDs0xPgmG6RX1Aba4KV5cWJbAMcvRCVmglpam9SoHZiUCyQVDdH2LPlxoHtrv17XWblE/V/PP+Tr04hbtA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="text/javascript"></script>
    <script>
        $(document).ready(function() {
            $("#userSettingForm").validate();
            $(".saveDocumentsBtn").on("click", function() {
                $("#userSettingForm").submit();
            })

            $(".deleteUSIDoc").on("click", function() {
                var check = confirm("Are you sure to delete this document?");
                if (check == false) {
                    return false;
                };
            })

            $("#usi_section").closest('form').attr('enctype', 'multipart/form-data');
            if ($("#have-usi").prop("checked", false)) {
                if ($("#usi_doc")) {
                    $("#usi_doc").removeAttr("disabled");
                    $("#usi_doc").removeClass('d-none');
                }
            }
            $(`#have-usi`).on('click', function() {
                if ($(this).is(':checked')) {
                    $("#dont-have-usi").prop("checked", false);
                    if ($("#usi_doc")) {
                        $("#usi_doc").attr("disabled", "disabled");
                        $("#usi_doc").addClass('d-none');
                    }
                } else {
                    $("#dont-have-usi").prop("checked", true);
                    if ($("#usi_doc")) {
                        $("#usi_doc").removeAttr("disabled");
                        $("#usi_doc").removeClass('d-none');
                    }
                }
            });
            $(`#dont-have-usi`).on('click', function() {
                if ($(this).is(':checked')) {
                    $("#have-usi").prop("checked", false);
                    if ($("#usi_doc")) {
                        $("#usi_doc").removeAttr("disabled");
                        $("#usi_doc").removeClass('d-none');
                    }
                } else {
                    $("#have-usi").prop("checked", true);
                    if ($("#usi_doc")) {
                        $("#usi_doc").attr("disabled", "disabled");
                        $("#usi_doc").addClass("d-none");
                    }
                }
            });
        })
    </script>
@endpush
