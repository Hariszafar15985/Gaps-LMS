@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    {{-- <link rel="stylesheet" href="/assets/default/vendors/persian-datepicker/persian-datepicker.min.css"/> --}}
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
    
@endpush

@section('content')
{{-- 
    user_id - done
    from (datepicker)
    to (datepicker)
    status  -   shouldn't be filled (should be handled at server level upon request)
    type (enum - select field)
    description (reason for break)
    requested by - shouldn't be filled (should be handled at server level upon request)
--}}

    <h3>{{trans('panel.create_break_request')}}</h3>

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
        <form action="{{ route('panel.break.save') }}" method="post" id="breakRequestForm">
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

@endsection

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
    </script>

@endpush