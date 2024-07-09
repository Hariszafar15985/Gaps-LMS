<div class="tab-pane mt-3 fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
    <div class="row">
        <div class="col-12 col-md-6">
            <form action="/admin/settings/sidebar/save" method="post">
                {{ csrf_field() }}

                <div class="form-group custom-switches-stacked">
                    <label class="custom-switch pl-0">
                        <input type="hidden" name="classes" value="0">
                        <input type="checkbox" name="classes" id="rtlSwitch" value="1" {{ (!empty($settings->new_class)) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                        <span class="custom-switch-indicator"></span>
                        <label class="custom-switch-description mb-0 cursor-pointer" for="rtlSwitch">{{ trans('classes_new') }}</label>
                    </label>
                </div>

                <div class="form-group custom-switches-stacked">
                    <label class="custom-switch pl-0">
                        <input type="hidden" name="quizess" value="0">
                        <input type="checkbox" name="quizess" id="preloadingSwitch" value="1" {{ (!empty($settings->new_quiz)) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                        <span class="custom-switch-indicator"></span>
                        <label class="custom-switch-description mb-0 cursor-pointer" for="preloadingSwitch">{{ trans('admin/main.new_quizzes') }}</label>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">{{ trans('admin/main.save_change') }}</button>
            </form>
        </div>
    </div>
</div>

@push('scripts_bottom')
    <script src="/assets/default/js/admin/settings/general_basic.min.js"></script>
@endpush
