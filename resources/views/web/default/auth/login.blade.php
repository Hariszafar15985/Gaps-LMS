<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    @php
    $rtlLanguages = !empty($generalSettings['rtl_languages']) ? $generalSettings['rtl_languages'] : [];

    $isRtl = ((in_array(mb_strtoupper(app()->getLocale()), $rtlLanguages)) or (!empty($generalSettings['rtl_layout']) and
    $generalSettings['rtl_layout'] == 1));
    @endphp
    <head>
        <meta charset="UTF-8">
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
        @include(getTemplate().'.includes.metas')
        <title>
            {{ $pageTitle ?? '' }}{{ !empty($generalSettings['site_name']) ? (' | '.$generalSettings['site_name']) : '' }}
        </title>

        
        
        <!-- General CSS File from users -->
        <link href="/assets/default/css/font.css" rel="stylesheet">
        <link rel="stylesheet" href="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
        <link rel="stylesheet" href="/assets/default/vendors/toast/jquery.toast.min.css">
        <link rel="stylesheet" href="/assets/default/vendors/simplebar/simplebar.css">
        <link rel="stylesheet" href="/assets/default/css/app.css">
        
        <!-- General CSS File from admin -->
        <link rel="stylesheet" href="/assets/admin/css/style.css">
        <link rel="stylesheet" href="/assets/admin/css/components.css">
        <link rel="stylesheet" href="/assets/admin/vendor/bootstrap/bootstrap.min.css"/>
        <link rel="stylesheet" href="/assets/admin/vendor/fontawesome/css/all.min.css"/>
        
        @if($isRtl)
        <link rel="stylesheet" href="/assets/default/css/rtl-app.css">
        @endif

        <style>
            {
                ! ! !empty(getCustomCssAndJs('css')) ? getCustomCssAndJs('css'): '' ! !
            }
        </style>

        @if(!empty($generalSettings['preloading']) and $generalSettings['preloading'] == '1')
        @include('admin.includes.preloading')
        @endif

    </head>
    <body class="@if($isRtl) rtl @endif">

    <div id="app">
        @php
            $siteGeneralSettings = getGeneralSettings();
            $getPageBackgroundSettings = getPageBackgroundSettings();

        @endphp

        <section class="section">
            <div class="d-flex flex-wrap align-items-stretch">
                <div class="col-lg-4 col-md-6 col-12 order-lg-1 min-vh-100 order-2 bg-white">
                    <div class="p-4 m-3">
                        <img src="{{ $siteGeneralSettings['logo'] ?? '' }}" alt="logo" width="40%" class="mb-5 mt-2">


                        <div class="container">
                            @if(!empty(session()->has('msg')))
                            <div class="alert alert-info alert-dismissible fade show mt-30" role="alert">
                                {{ session()->get('msg') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            @endif
                
                            <div class="row">
                                <div class="col-12 col-md-12">
                                    <div class="">
                                        <h4 class="font-20 font-weight-bold">{{ trans('auth.login_h1') }}</h4>
                
                                        <form method="Post" action="/login" class="mt-35">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <div class="form-group">
                                                <label class="input-label" for="username">{{ trans('auth.email_or_mobile') }}:</label>
                                                <input name="username" type="text"
                                                    class="form-control @error('username') is-invalid @enderror" id="username"
                                                    value="{{ old('username') }}" aria-describedby="emailHelp">
                                                @error('username')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                
                                            <div class="form-group">
                                                <label class="input-label" for="password">{{ trans('auth.password') }}:</label>
                                                <input name="password" type="password"
                                                    class="form-control @error('password')  is-invalid @enderror" id="password"
                                                    aria-describedby="passwordHelp">
                
                                                @error('password')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                
                                            <button type="submit"
                                                class="btn btn-primary btn-block mt-20">{{ trans('auth.login') }}</button>
                                        </form>
                
                                        <div class="text-center mt-20">
                                            <span
                                                class="badge badge-circle-gray300 text-secondary d-inline-flex align-items-center justify-content-center">{{ trans('auth.or') }}</span>
                                        </div>
                
                                        <a href="/google" target="_blank"
                                            class="social-login mt-20 p-10 text-center d-flex align-items-center justify-content-center">
                                            <img src="/assets/default/img/auth/google.svg" class="mr-auto" alt=" google svg" />
                                            <span class="flex-grow-1">{{ trans('auth.google_login') }}</span>
                                        </a>
                
                                        <a href="{{url('/facebook/redirect')}}" target="_blank"
                                            class="social-login mt-20 p-10 text-center d-flex align-items-center justify-content-center ">
                                            <img src="/assets/default/img/auth/facebook.svg" class="mr-auto" alt="facebook svg" />
                                            <span class="flex-grow-1">{{ trans('auth.facebook_login') }}</span>
                                        </a>
                
                                        <div class="mt-30 text-center">
                                            <a href="/forget-password" target="_blank">{{ trans('auth.forget_your_password') }}</a>
                                        </div>
                
                                        {{-- <div class="mt-20 text-center">
                                            <span>{{ trans('auth.dont_have_account') }}</span>
                                            <a href="/register" class="text-secondary font-weight-bold">{{ trans('auth.signup') }}</a>
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>

            <div class="col-lg-8 col-12 order-lg-2 order-1 min-vh-100 background-walk-y position-relative overlay-gradient-bottom" data-background="{{ $getPageBackgroundSettings['admin_login'] ?? '' }}">
            <div class="absolute-bottom-left index-2">
                <div class="text-light p-5 pb-2">
                <div class="mb-2 pb-3">
                    <h1 class="mb-2 display-4 font-weight-bold">{{trans('public.lms_name')}}</h1>
                    <h5 class="font-weight-normal text-muted-transparent">fully-featured educational platform</h5>
                </div>
                {{-- All rights reserved for <a class="text-light bb" target="_blank" href="/">{{trans('public.lms_name')}}</a> --}}
                </div>
            </div>
                </div>
            
            </div>
        </section>
    </div>
    <!-- General JS Scripts -->
    <script src="/assets/admin/vendor/jquery/jquery-3.3.1.min.js"></script>
    <script src="/assets/admin/vendor/poper/popper.min.js"></script>
    <script src="/assets/admin/vendor/bootstrap/bootstrap.min.js"></script>
    <script src="/assets/admin/vendor/nicescroll/jquery.nicescroll.min.js"></script>
    <script src="/assets/admin/vendor/moment/moment.min.js"></script>
    <script src="/assets/admin/js/stisla.js"></script>


    <!-- Template JS File -->
    <script src="/assets/admin/js/scripts.js"></script>
    <script src="/assets/admin/js/custom.js"></script>

    </body>
</html>
