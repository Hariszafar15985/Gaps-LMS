@extends('admin.layouts.app')


@section('content')
  {{-- 7f820360-f584-4676-9027-3e4bd20e7758 --}}
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.settings') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="#">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item">{{ trans('admin/main.settings') }}</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">{{ trans('admin/main.overview') }}</h2>
            <p class="section-lead">
                {{ trans('admin/main.overview_hint') }} <br/>
              Version : 1.4
            </p>

            <div class="row">
                @can('admin_settings_general')
                    <div class="col-lg-6">
                        <div class="card card-large-icons">
                            <div class="card-icon bg-primary text-white">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="card-body">
                                <h4>{{ trans('admin/main.general_card_title') }}</h4>
                                <p>{{ trans('admin/main.general_card_hint') }}</p>
                                <a href="/admin/settings/general" class="card-cta">{{ trans('admin/main.change_setting') }}<i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                @endcan

                @can('admin_settings_financial')
                    <div class="col-lg-6">
                        <div class="card card-large-icons">
                            <div class="card-icon bg-primary text-white">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="card-body">
                                <h4>{{ trans('admin/main.financial_card_title') }}</h4>
                                <p>{{ trans('admin/main.financial_card_hint') }}</p>
                                <a href="/admin/settings/financial" class="card-cta">{{ trans('admin/main.change_setting') }}<i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                @endcan

                @can('admin_settings_personalization')
                    <div class="col-lg-6">
                        <div class="card card-large-icons">
                            <div class="card-icon bg-primary text-white">
                                <i class="fas fa-wrench"></i>
                            </div>
                            <div class="card-body">
                                <h4>{{ trans('admin/main.personalization_card_title') }}</h4>
                                <p>{{ trans('admin/main.personalization_card_hint') }}</p>
                                <a href="/admin/settings/personalization/page_background" class="card-cta">{{ trans('admin/main.change_setting') }}<i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                @endcan

                @can('admin_settings_notifications')
                    <div class="col-lg-6">
                        <div class="card card-large-icons">
                            <div class="card-icon bg-primary text-white">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="card-body">
                                <h4>{{ trans('admin/main.notifications_card_title') }}</h4>
                                <p>{{ trans('admin/main.notifications_card_hint') }}</p>
                                <a href="/admin/settings/notifications" class="card-cta">{{ trans('admin/main.change_setting') }}<i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                @endcan

                @can('admin_settings_seo')
                    <div class="col-lg-6">
                        <div class="card card-large-icons">
                            <div class="card-icon bg-primary text-white">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="card-body">
                                <h4>{{ trans('admin/main.seo_card_title') }}</h4>
                                <p>{{ trans('admin/main.seo_card_hint') }}</p>
                                <a href="/admin/settings/seo" class="card-cta">{{ trans('admin/main.change_setting') }}<i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                @endcan
				{{-- 7f8758 --}}
                @can('admin_settings_customization')
                    <div class="col-lg-6">
                        <div class="card card-large-icons">
                            <div class="card-icon bg-primary text-white">
                                <i class="fas fa-list-alt"></i>
                            </div>
                            <div class="card-body">
                                <h4>{{ trans('admin/main.customization_card_title') }}</h4>
                                <p>{{ trans('admin/main.customization_card_hint') }}</p>
                                <a href="/admin/settings/customization" class="card-cta text-primary">{{ trans('admin/main.change_setting') }}<i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                @endcan

                <div class="col-lg-6">
                    <div class="card card-large-icons">
                        <div class="card-icon bg-primary text-white">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="card-body">
                            <h4>Instructor</h4>
                            <p>Settings for instructor's sidebar</p>
                            <a href="/admin/settings/sidebar" class="card-cta">{{ trans('admin/main.change_setting') }}<i class="fas fa-chevron-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card card-large-icons">
                        <div class="card-icon bg-primary text-white">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="card-body">
                            <h4>{{ trans('admin/main.organization_financial_tab') }}</h4>
                            <p>{{ trans('admin/main.organization_financial_tab_setting') }}</p>
                            <a href="/admin/settings/organization_financial" class="card-cta">{{ trans('admin/main.change_setting') }}<i class="fas fa-chevron-right"></i></a>
                        </div>
                    </div>
                </div>

                {{-- course General settings --}}
                <div class="col-lg-6">
                    <div class="card card-large-icons">
                        <div class="card-icon bg-primary text-white">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="card-body">
                            <h4>Course</h4>
                            <p>General settings for course</p>
                            <a href="{{route('settings.course.customization')}}" class="card-cta">{{ trans('admin/main.change_setting') }}<i class="fas fa-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection