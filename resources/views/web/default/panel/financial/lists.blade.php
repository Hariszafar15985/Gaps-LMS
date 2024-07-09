@extends(getTemplate() .'.panel.layouts.panel_layout')


@section('content')
    <h3>{{ trans('panel.organization_summary') }}</h3>
    @if($sales->count() > 0)
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped font-14">
                    <tr>
                        <th>#</th>
                        <th class="text-left">{{ trans('admin/main.student') }}</th>
                        <th class="text-left">{{ trans('admin/main.course') }}</th>
                        <th class="text-left">{{ trans('admin/main.paid_amount') }}</th>
                        <th class="text-left">{{ trans('admin/main.organization_site') }}</th>
                        <th class="text-left">{{ trans('admin/main.referred_by') }}</th>
                        <th class="text-left">{{ trans('admin/main.date') }}</th>
                        <th class="text-left">{{ trans('admin/main.status') }}</th>
                        {{-- <th width="120" class='text-right'>{{ trans('admin/main.actions') }}</th> --}}
                    </tr>
        
                    @foreach($sales as $sale)
                        <tr>
                            <td>{{ $sale->id }}</td>
        
                            <td class="text-left">
                                @php
                                    $soldTo = (!empty($sale->paid_for) && $sale->paidFor->isUser()) ? $sale->paidFor : $sale->buyer;
                                @endphp
                                {{ $soldTo->full_name }}
                                <div class="text-primary text-small font-600-bold">ID : {{  $soldTo->id }}</div>
                                @if (isset($soldTo->role_name) && $soldTo->isUser())
                                <a href="{{route('get.user.profile', ['id' => $soldTo->id])}}" target="_blank" class="btn-transparent d-inline-block text-primary" data-toggle="tooltip" data-placement="top" title="Student Profile">
                                    <i class="fa fa-user-shield"></i>
                                </a>
                                @endif
                            </td>
        
                            <td class="text-left">
                                <div class="media-body">
                                    <div>{{ $sale->item_title }}</div>
                                    <div class="text-primary text-small font-600-bold">ID : {{ $sale->item_id }}</div>
                                </div>
                            </td>
                            
                            <td>
                                @if($sale->payment_method == \App\Models\Sale::$subscribe)
                                    <span class="">{{ trans('admin/main.subscribe') }}</span>
                                @else
                                    @if(!empty($sale->total_amount))
                                        <span class="">{{ $currency }}{{ number_format($sale->total_amount, 2, ".", "")+0 }}</span>
                                    @else
                                        <span class="">{{ trans('public.free') }}</span>
                                    @endif
                                @endif
                            </td>
        
                            <td class="text-left">
                                @if(isset($soldTo->organizationSites))
                                @php
                                    $lineBreak = $soldTo->organizationSites->count() > 1 ? '<br />' : ''; 
                                @endphp
                                    @foreach($soldTo->organizationSites as $organizationSite)
                                        {{ $organizationSite->name . $lineBreak }}
                                    @endforeach
                                @endif
                            </td>
                            
                            <td class="text-left">
                                @if(!empty($soldTo->manager->full_name))
                                    {{ $soldTo->manager->full_name }}
                                @endif
                            </td>
        
                            <td>{{ dateTimeFormat($sale->created_at, 'j F Y H:i') }}</td>
        
                            <td>
                                @php
                                    $statusText = '';
                                    $statusTextClass = ''; 
                                    if(!empty($sale->refund_at)) {
                                        $statusText = trans('admin/main.refund');
                                        $statusTextClass = 'text-warning';
                                    }
                                    else {
                                        if ($sale->total_amount && (float)$sale->total_amount > 0) {
        
                                            foreach(\App\Models\Sale::$paymentStatus as $statusDescription => $statusValue) {
                                                if ((int)$sale->payment_status === $statusValue) {
                                                    $statusText = trans("admin/main.{$statusDescription}");
                                                    if ($statusText === trans("admin/main.pushed_to_xero")) {
                                                        $statusTextClass = 'text-primary';
                                                    } elseif ($statusText === trans("admin/main.paid")) {
                                                        $statusTextClass = 'text-success';
                                                    } elseif ($statusText === trans("admin/main.refunded")) {
                                                        $statusTextClass = 'text-danger';
                                                    } else { 
                                                        //($statusText === trans("admin/main.pending")) {
                                                        //default case for pending and also to serve as fallback
                                                        //for future statuses
                                                        $statusTextClass = 'text-dark';
                                                    }
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                    //Old default value as fallback
                                    if (empty($statusText)) {
                                        $statusText = trans('admin/main.success');
                                        $statusTextClass = 'text-success';
                                    }
                                @endphp
                                    <span class="{{ $statusTextClass }}">{{ $statusText }}</span>
                            </td>
        
                            {{-- <td class='text-right'>
                                @if(!empty($sale->webinar_id))
                                    <a href="/admin/financial/sales/{{ $sale->id }}/invoice" target="_blank" title="{{ trans('admin/main.invoice') }}"><i class="fa fa-print" aria-hidden="true"></i></a>
                                @endif
                            </td> --}}
                        </tr>
                    @endforeach
        
                </table>
            </div>
        </div>
    @else


        @include(getTemplate() . '.includes.no-result',[
            'file_name' => 'financial.png',
            'title' => trans('financial.financial_summary_no_result'),
            'hint' => nl2br(trans('financial.financial_summary_no_result_hint')),
        ])
    @endif
    <div class="my-30">
        {{ $sales->links('vendor.pagination.panel') }}
    </div>
    {{-- <div class="card-footer text-center">
        {{ $sales->links() }}
    </div> --}}
@endsection