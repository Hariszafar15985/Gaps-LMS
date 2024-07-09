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
                <th class="text-left">{{ trans('admin/main.xero_invoice_id') }}</th>
                <th class="text-left">{{ trans('admin/main.date') }}</th>
                <th class="text-left">{{ trans('admin/main.status') }}</th>
                @if (!isset($isAjaxRequest) || $isAjaxRequest === false)
                    <th width="120" class='text-right'>{{ trans('admin/main.actions') }}</th>
                @endif
            </tr>

            @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->id }}</td>

                    <td class="text-left">
                        {{ $sale->buyer->full_name }}
                        <div class="text-primary text-small font-600-bold">ID : {{  $sale->buyer->id }}</div>
                        @if (isset($isAjaxRequest) && $isAjaxRequest)
                        <a href="{{route('get.user.profile', ['id' => $sale->buyer->id])}}" target="_blank" class="btn-transparent d-inline-block text-primary" data-toggle="tooltip" data-placement="top" title="Student Profile">
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
                        @if(isset($sale->buyer) && !empty($sale->buyer->organizationSites()) 
                            && $sale->buyer->role_name === \App\Models\Role::$user)
                            @php 
                                $userorganizationSites = $sale->buyer->organizationSites; 
                                // $organizationSites =  $this->organizationSites;
                                $organizationSites =  $userorganizationSites;
                                $organizationSitesArray = [];
                                foreach($organizationSites as $organizationSite) {
                                    $organizationSitesArray[] = $organizationSite->name;
                                }
                            @endphp
                            @if (!empty($organizationSitesArray) && is_array($organizationSitesArray))
                                {{ implode(',', $organizationSitesArray) }}
                            @endif
                        @endif
                    </td>
                    
                    <td class="text-left">
                        @if(isset($sale->buyer->manager))
                            {{ $sale->buyer->manager->full_name }}
                        @endif
                    </td>

                    <td class="text-left">
                        @if(isset($sale->xero_invoice_id) && strlen($sale->xero_invoice_id) > 0)
                            {{ $sale->xero_invoice_id }}
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

                    @if (!isset($isAjaxRequest) || $isAjaxRequest === false)
                        <td class='text-right'>
                            @can('admin_sales_invoice')
                                @if(!empty($sale->webinar_id))
                                    <a href="/admin/financial/sales/{{ $sale->id }}/invoice" target="_blank" title="{{ trans('admin/main.invoice') }}"><i class="fa fa-print" aria-hidden="true"></i></a>
                                @endif
                            @endcan

                            @if ($sale->total_amount && (float)$sale->total_amount > 0 && (int)$sale->payment_status === 0)
                            <a href="{{ route('admin_push_sale_to_xero', ['saleId' => $sale->id]) }}" target="_blank" title="{{ trans('admin/main.push_to_xero') }}">
                                <i class="fas fa-book"></i>
                            </a>
                            @endif

                            @can('admin_sales_refund')
                                @if(empty($sale->refund_at) and $sale->payment_method != \App\Models\Sale::$subscribe)
                                    @include('admin.includes.delete_button',[
                                            'url' => '/admin/financial/sales/'. $sale->id .'/refund',
                                            'tooltip' => trans('admin/main.refund'),
                                            'btnIcon' => 'fa-times-circle'
                                        ])
                                @endif
                            @endcan
                        </td>
                    @endif
                </tr>
            @endforeach

        </table>
    </div>
</div>