@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.main_general') }} {{ trans('admin/main.settings') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="/admin/">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item active"><a href="/admin/settings">{{ trans('admin/main.settings') }}</a></div>
                <div class="breadcrumb-item ">{{ trans('admin/main.main_general') }}</div>
            </div>
        </div>

        <div class="section-body">

            <div class="row">
                <div class="col-12">
                    @php
                        if (!empty($settings) && isset($settings['organization_financial'])) {
                            if (isset($settings['organization_financial']->value)) {
                                $itemValue = $settings['organization_financial']->value;
                                if (!empty($itemValue) and !is_array($itemValue)) {
                                    $itemValue = json_decode($itemValue, true);
                                }
                            }
                        }
                    @endphp


                    <div class="tab-pane mt-3 fade @if(empty($social)) show active @endif" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <form action="/admin/settings/main" method="post">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="page" value="organization_financial">
                                    <input type="hidden" name="name" value="organization_financial">

                                    <div class="form-group custom-switches-stacked">
                                        <label class="custom-switch pl-0">
                                            <label class="custom-switch-description mb-0 cursor-pointer mr-3" for="financeTabSwitch">{{ trans('admin/main.show_organization_financial_tab') }}</label>
                                            <input type="hidden" name="value[financeTab]" value="0">
                                            <input type="checkbox" name="value[financeTab]" id="financeTabSwitch" value="1" {{ (!empty($itemValue) and !empty($itemValue['financeTab']) and $itemValue['financeTab']) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                            <span class="custom-switch-indicator"></span>
                                            <label class="custom-switch-description mb-0 cursor-pointer" for="financeTabSwitch">{{ trans('admin/main.no_yes') }}</label>
                                        </label>
                                    </div>

                                    <button type="submit" class="btn btn-primary">{{ trans('admin/main.save_change') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection