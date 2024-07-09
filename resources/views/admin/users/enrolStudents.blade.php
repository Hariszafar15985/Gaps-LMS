@extends('admin.layouts.app')

@push('libraries_top')
<link rel="stylesheet" href="/assets/default/css/app.css">
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Enrol Student</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="/admin/">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item active"><a href="/admin/users">{{ trans('admin/main.users') }}</a>
                </div>
                <div class="breadcrumb-item">Enrol</div>
            </div>
        </div>

        @if(!empty(session()->has('msg')))
            <div class="alert alert-success my-25">
                {{ session()->get('msg') }}
            </div>
        @endif
    </section>

    <section class="mt-25">
    @if(!empty($webinars) and !$webinars->isEmpty())
            @foreach($webinars as $webinar)
                @php
                    $lastSession = $webinar->lastSession();
                    $nextSession = $webinar->nextSession();
                    $isProgressing = false;

                    if($webinar->start_date <= time() and !empty($lastSession) and $lastSession->date > time()) {
                        $isProgressing=true;
                    }
                @endphp

                <div class="row mt-30">
                    <div class="col-12">
                        <div class="webinar-card webinar-list d-flex">
                            <div class="image-box">
                                <img src="{{ $webinar->getImage() }}" class="img-cover" alt="">

                                @if($webinar->type == 'webinar')
                                    @if($webinar->start_date > time())
                                        <span class="badge badge-primary">{{  trans('panel.not_conducted') }}</span>
                                    @elseif($webinar->isProgressing())
                                        <span class="badge badge-secondary">{{ trans('webinars.in_progress') }}</span>
                                    @else
                                        <span class="badge badge-secondary">{{ trans('public.finished') }}</span>
                                    @endif
                                @elseif(!empty($webinar->downloadable))
                                    <span class="badge badge-secondary">{{ trans('home.downloadable') }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ trans('webinars.'.$webinar->type) }}</span>
                                @endif

                                @php
                                    $percent = $webinar->getProgress();

                                    if($webinar->isWebinar()){
                                        if($webinar->isProgressing()) {
                                            $progressTitle = trans('public.course_learning_passed',['percent' => $percent]);
                                        } else {
                                            $progressTitle = $webinar->sales_count .'/'. $webinar->capacity .' '. trans('quiz.students');
                                        }
                                    } else {
                                           $progressTitle = trans('public.course_learning_passed',['percent' => $percent]);
                                    }
                                @endphp

                                <div class="progress cursor-pointer" data-toggle="tooltip" data-placement="top" title="{{ $progressTitle }}">
                                    <span class="progress-bar" style="width: {{ $percent }}%"></span>
                                </div>
                            </div>

                            <div class="webinar-card-body w-100 d-flex flex-column">
                                <div class="d-flex align-items-center justify-content-between">
                                    <a href="{{ $webinar->getUrl() }}">
                                        <h3 class="webinar-title font-weight-bold font-16 text-dark-blue">
                                            {{ $webinar->title }}
                                            <span style="color:#fff; border-radius:0px;" class="badge badge-dark ml-10 status-badge-dark">{{ trans('webinars.'.$webinar->type) }}</span>
                                        </h3>
                                    </a>
                                    @php
                                        $userHasBought = $webinar->customCheckUserHasBought($user->id);
                                        $canSale = ($webinar->canSale() and !$userHasBought);
                                    @endphp
                                    @if($webinar->price > 0)
                                        {{-- <button type="{{ $canSale ? 'submit' : 'button' }}" @if(!$canSale) disabled @endif class="btn btn-primary">
                                            @if($userHasBought)
                                                {{ trans('panel.purchased') }}
                                            @else
                                                {{ trans('public.add_to_cart') }}
                                            @endif
                                        </button> --}}
                                        <a class="enrol_stu btn btn-primary @if(!$canSale) disabled @endif" @if(!$canSale) disabled @endif data-toggle="modal" data-target="#enrolModal" href="javascript::void(0);" data-course="{{ $webinar->title }}" data-prie="{{$webinar->price}}" data-name="{{  $user->full_name }}" data-url="{{ $canSale ? '/admin/students/course/'. $webinar->slug .'/paid/'. $user->id : '#' }}" style="float:right;">
                                            @if($userHasBought)
                                                {{ trans('panel.purchased') }}
                                            @else
                                                {{ trans('panel.enrol_dollars') }}
                                            @endif
                                        </a>

                                        @if($canSale and $webinar->subscribe)
                                            <a href="{{ $canSale ? '/subscribes/apply/'. $webinar->slug : '#' }}" class="btn btn-outline-primary btn-subscribe mt-20 @if(!$canSale) disabled @endif">{{ trans('public.subscribe') }}</a>
                                        @endif
                                    @else
                                        {{-- <a class="enrol_stu" data-toggle="modal" data-target="#enrolModal" href="javascript::void(0);" data-course="{{ $webinar->title }}" data-prie="{{$webinar->price}}" data-name="{{  $user->full_name }}" data-url="{{ $canSale ? '/admin/students/course/'. $webinar->slug .'/free/'. $user->id : '#' }}" style="float:right;">
                                            <h3 class="webinar-title font-weight-bold font-16 text-dark-blue">
                                                <span style="color:#fff; border-radius:0px;" class="badge badge-dark ml-10 status-badge-dark">Enrol</span>
                                            </h3>
                                        </a> --}}
                                        <a class="enrol_stu btn btn-primary @if(!$canSale) disabled @endif" @if(!$canSale) disabled @endif data-toggle="modal" data-target="#enrolModal" href="javascript::void(0);" data-course="{{ $webinar->title }}" data-prie="{{$webinar->price}}" data-name="{{  $user->full_name }}" data-url="{{ $canSale ? '/admin/students/course/'. $webinar->slug .'/free/'. $user->id : '#' }}" style="float:right;">
                                            @if($userHasBought)
                                                {{ trans('panel.purchased') }}
                                            @else
                                                <span>{{ trans('panel.enrol') }}</span>
                                            @endif
                                        </a>
                                    @endif

                                    {{-- <div class="btn-group dropdown table-actions">
                                        <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i data-feather="more-vertical" height="20"></i>
                                        </button>

                                        <div class="dropdown-menu">
                                            @if(!empty($webinar->start_date) and ($webinar->start_date > time() or ($webinar->isProgressing() and !empty($nextSession))))
                                                <button type="button" data-webinar-id="{{ $webinar->id }}" class="join-purchase-webinar webinar-actions btn-transparent d-block">{{ trans('footer.join') }}</button>
                                            @endif

                                            @if(!empty($webinar->downloadable) or (!empty($webinar->files) and count($webinar->files)))
                                                <a href="{{ $webinar->getUrl() }}?tab=content" target="_blank" class="webinar-actions d-block mt-10">{{ trans('home.download') }}</a>
                                            @endif

                                            @if($webinar->price > 0)
                                                <a href="/panel/webinars/{{ $webinar->id }}/invoice" target="_blank" class="webinar-actions d-block mt-10">{{ trans('public.invoice') }}</a>
                                            @endif

                                            <a href="{{ $webinar->getUrl() }}?tab=reviews" target="_blank" class="webinar-actions d-block mt-10">{{ trans('public.feedback') }}</a>
                                        </div>
                                    </div> --}}
                                </div>

                                @include(getTemplate() . '.includes.webinar.rate',['rate' => $webinar->getRate()])

                                <div class="webinar-price-box mt-15">
                                    @if($webinar->price > 0)
                                        @if($webinar->bestTicket() < $webinar->price)
                                            <span class="real">{{ $currency }}{{ number_format($webinar->bestTicket(), 2, ".", "")+0 }}</span>
                                            <span class="off ml-10">{{ $currency }}{{ number_format($webinar->price, 2, ".", "")+0 }}</span>
                                        @else
                                            <span class="real">{{ $currency }}{{ number_format($webinar->price, 2, ".", "")+0 }}</span>
                                        @endif
                                    @else
                                        <span class="real">{{ trans('public.free') }}</span>
                                    @endif
                                </div>

                                <div class="d-flex align-items-center justify-content-between flex-wrap mt-auto">
                                    <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class="stat-title">{{ trans('public.item_id') }}:</span>
                                        <span class="stat-value">{{ $webinar->id }}</span>
                                    </div>

                                    <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class="stat-title">{{ trans('public.category') }}:</span>
                                        <span class="stat-value">{{ !empty($webinar->category_id) ? $webinar->category->title : '' }}</span>
                                    </div>

                                    @if($webinar->type == 'webinar')
                                        @if($webinar->isProgressing() and !empty($nextSession))
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('webinars.next_session_duration') }}:</span>
                                                <span class="stat-value">{{ convertMinutesToHourAndMinute($nextSession->duration) }} Hrs</span>
                                            </div>

                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('webinars.next_session_start_date') }}:</span>
                                                <span class="stat-value">{{ dateTimeFormat($nextSession->date,'j F Y') }}</span>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('public.duration') }}:</span>
                                                <span class="stat-value">{{ convertMinutesToHourAndMinute($webinar->duration) }} Hrs</span>
                                            </div>

                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('public.start_date') }}:</span>
                                                <span class="stat-value">{{ dateTimeFormat($webinar->start_date,'j F Y') }}</span>
                                            </div>
                                        @endif
                                    @endif

                                    <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class="stat-title">{{ trans('public.instructor') }}:</span>
                                        <span class="stat-value">{{ $webinar->teacher->full_name }}</span>
                                    </div>

                                    <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                        @if(in_array($webinar->id, $purchasedCourseIds))
                                        <span class="stat-title">{{ trans('panel.purchase_date') }}:</span>
                                        <span class="stat-value">{{ Carbon\Carbon::create(getEnrolDate($webinar->id, $user->id))->format('j F Y') }}</span>
                                        @else
                                        <span class="stat-value">{{ trans("public.not_enroled") }}</span>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </section>
    <!-- Modal begin -->
    <div id="enrolModal" class="modal fade" role="dialog">
        <div class="modal-dialog" style="max-width:90%;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title mx-auto">Enrol Student</h4>
                    <button type="button" class="close float-right ml-1" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <h1>Do you confirm you would like to enrol <span id="stu_name"></span> into <span id="course_name"></span>
                    for <span id="price"></span>? This amount will be invoiced to your site</h1>
                </div>
                <div class="modal-footer">
                    <a id="enrol_url" href="" class="js-join-btn btn btn-primary">Confirm Enrolment</a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('admin/main.close') }}</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal end -->
@endsection

@push('scripts_bottom')
<script>
$( document ).ready(function() {
    $('#tooltips').tooltip()

    $(".enrol_stu").on("click", function () {
        var url = $(this).data('url');
        var stu_name = $(this).data('name');
        var course_name = $(this).data('course');
        var price = $(this).data('price');
        $("#stu_name").html("");
        $("#course_name").html("");
        $("#price").html("");
        $("#stu_name").html(stu_name);
        $("#course_name").html(course_name);
        $("#price").html(price);
        $("#enrol_url").attr("href", url)
    });
});
</script>
@endpush
