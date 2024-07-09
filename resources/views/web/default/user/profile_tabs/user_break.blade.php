@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">

    <style>
        #userBreakRecords th, #userBreakRecords td {
            text-align: center;
        }
    </style>

@endpush

@if(session()->has('breakSuccess'))
    <div class="container-fluid">
        <div class="row">
            <div class="col col-12 alert alert-success">
                {{ session()->get('breakSuccess') }}
            </div>
        </div>
    </div>
@endif

@if(session()->has('breakError'))
    <div class="container-fluid">
        <div class="row">
            <div class="col col-12 alert alert-warning">
                {{ session()->get('breakError') }}
            </div>
        </div>
    </div>
@endif

<section class="mb-40">
    @if(!empty($user) and !empty($user->occupiedBreaks) and $user->occupiedBreaks->count() > 0)
        <div class="mt-40">
            <h3 class="font-16 font-weight-bold text-dark-blue">{{ trans('public.existing_breaks') }}</h3>
        </div>

        <div class="mt-40" id="userBreakRecords">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{{ trans('admin/main.break_from') }}</th>
                        <th>{{ trans('admin/main.break_to') }}</th>
                        <th>{{ trans('admin/main.requested_by') }}</th>
                        <th>{{ trans('admin/main.request_date') }}</th>
                        <th>{{ trans('admin/main.status') }}</th>
                        <th class="text-right" width="120">{{ trans('admin/main.actions') }}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($user->occupiedBreaks as $break)
                        <tr>
                            <td>
                                <div class="media-body">
                                    {{\Carbon\Carbon::createFromFormat('Y-m-d', $break->from)}}
                                </div>
                            </td>
                            <td>
                                <div class="media-body">
                                    {{\Carbon\Carbon::createFromFormat('Y-m-d', $break->to)}}
                                </div>
                            </td>
                            <td>
                                <div class="media-body">
                                    {{$break->requestedBy->full_name}}
                                </div>
                            </td>
                            <td>
                                <div class="media-body">
                                    {{ $break->created_at }}
                                </div>
                            </td>
                            <td>
                                <div class="media-body
                                @if ($break->status === \App\Models\UserBreak::$status['pending'])
                                text-warning
                                @elseif  ($break->status === \App\Models\UserBreak::$status['approved'])
                                text-primary
                                @elseif  ($break->status === \App\Models\UserBreak::$status['rejected'])
                                text-danger
                                @endif
                                ">
                                    {{ ucfirst($break->status) }}
                                </div>
                            </td>

                            <td class="text-right mb-2" width="120">
                                @if ($break->status === \App\Models\UserBreak::$status['pending'])
                                <a href="{{route('admin.breakRequest.approve', ['id' => $break->id])}}" class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top" title="Approve">
                                    <i class="fas fa-check"></i>
                                </a>
                                <a href="{{route('admin.breakRequest.reject', ['id' => $break->id])}}" class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top" title="Reject">
                                    <i class="fa fa-ban"></i>
                                </a>
                                @endif
                                {{-- @include('admin.includes.delete_button',['url' => route('admin.breakRequest.delete', ['id' => $break->id]) , 'btnClass' => '']) --}}
                                <a href="{{route('admin.breakRequest.delete', ['id' => $break->id])}}">
                                    <button class="btn-transparent text-primary"
                                            data-confirm="{{ trans('admin/main.delete_confirm_msg') }}"
                                            data-confirm-href="{{route('admin.breakRequest.delete', ['id' => $break->id])}}"
                                            data-confirm-text-yes="{{ trans('admin/main.yes') }}"
                                            data-confirm-text-cancel="{{ trans('admin/main.cancel') }}"

                                            data-toggle="tooltip" data-placement="top" title="{{ !empty($tooltip) ? $tooltip : trans('admin/main.delete') }}"
                                    >
                                        <i class="fa fa-times" aria-hidden="true"></i>
                                    </button>
                                </a>

                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="mt-40">
            <h3 class="font-16 font-weight-bold text-dark-blue">{{ trans('public.no_break_records_for_user') }}</h3>
        </div>

    @endif
</section>


{{--
    user_id - done
    from (datepicker)
    to (datepicker)
    status  -   shouldn't be filled (should be handled at server level upon request)
    type (enum - select field)
    description (reason for break)
    requested by - shouldn't be filled (should be handled at server level upon request)
--}}

    <h3 class="font-16 font-weight-bold text-dark-blue">{{trans('panel.create_break_request')}}</h3>

    @if (session('error'))
        <section class="mt-20">
            <div class="row">
                <div class="col-sm-12">
                    <div class="alert  alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if(!empty($user) and !empty($user->id) and $user->id > 0)
        <form action="{{ route('admin.breakRequest.save') }}" method="post" id="breakRequestForm">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="day" id="selectedDay" value="">
            <input type="hidden" name="user_id" id="user_id" value="{{ $user->id }}">

            <div class="row">

                <div class="col mt-40">
                    <h3 class="font-16 font-weight-bold text-dark-blue">{{ trans('panel.select_break_range') }}</h3>
                    <div class="row">
                        <div class="col col-md-6 text-left form-inline">
                            <h4 class="input-label" for="from">{{trans('panel.from')}}</h4>
                            <input type="text" id="from" name="from"
                                class="ml-20 form-control @error('from') is-invalid @enderror" />
                            @error('from')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="col col-md-6 text-left form-inline">
                            <h4 class="input-label" for="to">{{trans('panel.to')}}</h4>
                            <input type="text" id="to" name="to"
                                class="ml-20 form-control @error('to') is-invalid @enderror" />
                            @error('to')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-40">
                <div class="col-md-6">
                    <div class="form-group">
                        <h4>{{ trans('panel.break_type') }}</h4>
                        <select class="form-control" id="type" name="type">
                            @if(
                                isset(\App\Models\UserBreak::$breakTypes)
                                && is_array(\App\Models\UserBreak::$breakTypes)
                                && count(\App\Models\UserBreak::$breakTypes) > 0
                            )
                                @foreach(\App\Models\UserBreak::$breakTypes as $breakType)
                                    <option value="{{$breakType}}"
                                    >{{$breakType}}</option>
                                @endforeach
                            @else
                                <option disabled selected>{{ trans('panel.select_break_type') }}</option>
                            @endif
                        </select>
                        @error('type')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <div class="mt-40">
                            <h4 class="font-16 text-dark-blue font-weight-bold">{{ trans('panel.break_reason') }}</h4>

                            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"></textarea>
                            @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-sm btn-primary mt-30">{{ trans('panel.create_request') }}</button>

        </form>

    @else

        @include(getTemplate() . '.includes.no-result',[
        'file_name' => 'meet.png',
        'title' => trans('panel.verify_student_reference'),
        'hint' => '',
        ])

    @endif


@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>

    <script>
        let disabledDates = [];
        @php
            if (isset($breaks) && count($breaks) > 0) {
                foreach ($breaks as $break) {
                    $breakFrom = $break->from;
                    $fromDate = \Carbon\Carbon::createFromFormat('Y-m-d',  $breakFrom);
                    $breakTo = $break->to;
                    $toDate = \Carbon\Carbon::createFromFormat('Y-m-d',  $breakTo);
                    $date = $fromDate;
                    while($date <= $toDate) {
                        $dateString = $date->format('Y-m-d');
        @endphp
                        disabledDates.push('{{$dateString}}');
        @php
                        $date->add(1, 'day');
                    }
                }
            }
        @endphp
    </script>
    <script src="/assets/default/js/jquery_ui.js"></script>
    <script>
        $( function() {
            var dateFormat = "yy-mm-dd",
            from = $( "#from" )
            .datepicker({
                changeMonth: true,
                numberOfMonths: 1,
                dateFormat: "yy-mm-dd",
                minDate: 0,
                beforeShowDay: function(date){
                    let string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                    return [ disabledDates.indexOf(string) == -1 ]
                }
            })
            .on( "change", function() {
                to.datepicker( "option", "minDate", getPickerDate( this ) );
            }),
            to = $( "#to" ).datepicker({
                changeMonth: true,
                numberOfMonths: 1,
                dateFormat: "yy-mm-dd",
                minDate: 0,
                beforeShowDay: function(date){
                    let string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                    return [ disabledDates.indexOf(string) == -1 ]
                }
            })
            .on( "change", function() {
                from.datepicker( "option", "maxDate", getPickerDate( this ) );
            });

            function getPickerDate( element ) {
                var date;
                try {
                    date = $.datepicker.parseDate( dateFormat, element.value );
                } catch( error ) {
                    date = null;
                }

                return date;
            }
        } );
        $('[data-toggle=confirmation]').confirmation({
            rootSelector: '[data-toggle=confirmation]'
        });
    </script>

@endpush
