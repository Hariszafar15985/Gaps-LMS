<!-- Modal -->
<div class="d-none" id="webinarFileModal">
    <h3 class="section-title after-line font-20 text-dark-blue mb-25">{{ trans('public.add_file') }}</h3>
    <form action="/admin/files/store" method="post">
        <input type="hidden" name="webinar_id" value="{{  !empty($webinar) ? $webinar->id :''  }}">

        @if(!empty(getGeneralSettings('content_translate')))
            <div class="form-group">
                <label class="input-label">{{ trans('auth.language') }}</label>
                <select name="locale" class="form-control ">
                    @foreach($userLanguages as $lang => $language)
                        <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                    @endforeach
                </select>
                @error('locale')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        @else
            <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
        @endif

        <div class="form-group">
            <label class="input-label">{{ trans('public.title') }}</label>
            <input type="text" name="title" class="form-control" placeholder="{{ trans('forms.maximum_50_characters') }}"/>
            <div class="invalid-feedback"></div>
        </div>

        <div class="form-group">
            <label class="input-label">{{ trans('public.chapter') }}</label>
            <select class="custom-select" name="chapter_id">
                <option value="">{{ trans('admin/main.no_chapter') }}</option>

                @if(!empty($chapters))
                    @foreach($chapters->where('type',\App\Models\WebinarChapter::$chapterFile) as $chapter)
                        <option value="{{ $chapter->id }}">{{ $chapter->title }}</option>
                    @endforeach
                @endif
            </select>
            <div class="invalid-feedback"></div>
        </div>

        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label class="input-label">{{ trans('public.accessibility') }}</label>
                    <select class="custom-select" name="accessibility" required>
                        <option selected disabled>{{ trans('public.choose_accessibility') }}</option>
                        <option value="free">{{ trans('public.free') }}</option>
                        <option value="paid">{{ trans('public.paid') }}</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-6">
                <div class="form-group">
                    <label class="input-label">{{ trans('public.source') }}</label>
                    <select class="custom-select js-file-storage" name="storage">
                        <option value="local" selected>{{ trans('webinars.upload') }}</option>
                        <option value="online">{{ trans('webinars.youtube_vimeo') }}</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>

        <div class="local-input form-group">
            <label class="input-label">{{ trans('public.file') }}</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <button type="button" class="input-group-text admin-file-manager" data-input="str_file_path" data-preview="holder">
                        <i class="fa fa-arrow-up"></i>
                    </button>
                </div>
                <input type="text" name="str_file_path" id="str_file_path" class="form-control"/>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="online-inputs form-group d-none">
            <div class="input-group mt-3">
                <div class="input-group-prepend">
                    <span class="input-group-text">
                        <i class="fa fa-link"></i>
                    </span>
                </div>
                <input type="text" name="file_path" value="" class="js-ajax-file_path form-control" placeholder="{{ trans('webinars.file_online_placeholder') }}"/>
                <div class="invalid-feedback"></div>
            </div>

            <div class="row mt-2">
                <div class="col-6">
                    <label class="input-label">{{ trans('webinars.file_type') }}</label>

                    <select name="file_type" class="js-ajax-file_type form-control">
                        <option value="">{{ trans('webinars.select_file_type') }}</option>

                        @foreach(\App\Models\File::$fileTypes as $fileType)
                            <option value="{{ $fileType }}">{{ $fileType }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-6">
                    <label class="input-label">{{ trans('webinars.file_volume') }}</label>
                    <input type="text" name="volume" value="" class="js-ajax-volume form-control" placeholder="{{ trans('webinars.online_file_volume') }}"/>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="input-label">{{ trans('public.description') }}</label>
            <textarea name="description" class="form-control" rows="6"></textarea>
            <div class="invalid-feedback"></div>
        </div>

        <div class="js-downloadable-file form-group mt-3">
            <div class="d-flex align-items-center justify-content-between">
                <label class="cursor-pointer input-label" for="downloadableSwitch_record">{{ trans('home.downloadable') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="downloadable" checked class="custom-control-input" id="downloadableSwitch_record">
                    <label class="custom-control-label" for="downloadableSwitch_record"></label>
                </div>
            </div>
        </div>

        <div class="js-file-status form-group mt-3">
            <div class="d-flex align-items-center justify-content-between">
                <label class="cursor-pointer input-label" for="fileStatusSwitch_record">{{ trans('admin/main.active') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="status" checked class="custom-control-input" id="fileStatusSwitch_record">
                    <label class="custom-control-label" for="fileStatusSwitch_record"></label>
                </div>
            </div>
        </div>

        <div class="mt-30 d-flex align-items-center justify-content-end">
            <button type="button" id="saveFile" class="btn btn-primary">{{ trans('public.save') }}</button>
            <button type="button" class="btn btn-danger ml-2 close-swl">{{ trans('public.close') }}</button>
        </div>
    </form>
</div>
