<?php

namespace App\Http\Controllers\Admin;

use App\Exports\salesExport;
use App\Http\Controllers\Controller;
use App\Models\Accounting;
use App\Models\Order;
use App\Models\Sale;
use App\Models\SaleLog;
use App\Models\Webinar;
use App\User;
use Carbon\Carbon;
//use Dcblogdev\Xero\Xero;
use Dcblogdev\Xero\Facades\Xero;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_sales_list');

        $query = Sale::query();

        $totalSales = [
            'count' => deepClone($query)->count(),
            'amount' => deepClone($query)->sum('total_amount'),
        ];

        $classesSales = [
            'count' => deepClone($query)->whereNotNull('webinar_id')->count(),
            'amount' => deepClone($query)->whereNotNull('webinar_id')->sum('total_amount'),
        ];
        $appointmentSales = [
            'count' => deepClone($query)->whereNotNull('meeting_id')->count(),
            'amount' => deepClone($query)->whereNotNull('meeting_id')->sum('total_amount'),
        ];
        $failedSales = Order::where('status', Order::$fail)->count();

        $salesQuery = $this->getSalesFilters($query, $request);

        $salesQuery = $salesQuery->orderBy('created_at', 'desc')
            ->with([
                'buyer' => function($query) {
                    $query->with('organizationSites');
                },
                'webinar',
                'meeting',
                'subscribe',
                'promotion',
                'seller',
                'paidFor',
                'referenceSale',
                'isReferenceFor'
            ]);
        if ($request->ajax()) {
            $sales = $salesQuery->get();
        }
        else {
            $sales = $salesQuery->paginate(10);
        }

        foreach ($sales as $sale) {
            $sale = $this->makeTitle($sale);

            if (empty($sale->saleLog)) {
                SaleLog::create([
                    'sale_id' => $sale->id,
                    'viewed_at' => time()
                ]);
            }
        }

        $data = [
            'pageTitle' => trans('admin/pages/financial.sales_page_title'),
            'sales' => $sales,
            'totalSales' => $totalSales,
            'classesSales' => $classesSales,
            'appointmentSales' => $appointmentSales,
            'failedSales' => $failedSales,
        ];

        $teacher_ids = $request->get('teacher_ids');
        $student_ids = $request->get('student_ids');
        $webinar_ids = $request->get('webinar_ids');

        if (!empty($teacher_ids)) {
            $data['teachers'] = User::select('id', 'full_name')
                ->whereIn('id', $teacher_ids)->get();
        }

        if (!empty($student_ids)) {
            $data['students'] = User::select('id', 'full_name')
                ->whereIn('id', $student_ids)->get();
        }

        if (!empty($webinar_ids)) {
            $data['webinars'] = Webinar::select('id')
                ->whereIn('id', $webinar_ids)->get();
        }

        if ($request->ajax()) {
            $data['isAjaxRequest'] = true;
            return (string) view('admin.financial.sales.sales_table_partial', $data);
        }

        return view('admin.financial.sales.lists', $data);
    }

    private function makeTitle($sale)
    {
        if (!empty($sale->webinar_id)) {
            $sale->item_title = $sale->webinar->title;
            $sale->item_id = $sale->webinar_id;
            $sale->item_seller = $sale->webinar->creator->full_name;
            $sale->seller_id = $sale->webinar->creator->id;
            $sale->sale_type = $sale->webinar->creator->id;
        } elseif (!empty($sale->meeting_id)) {
            $sale->item_title = trans('panel.meeting');
            $sale->item_id = $sale->meeting_id;
            $sale->item_seller = $sale->meeting->creator->full_name;
            $sale->seller_id = $sale->meeting->creator->id;
        } elseif (!empty($sale->subscribe_id) and !empty($sale->subscribe)) {
            $sale->item_title = $sale->subscribe->title;
            $sale->item_id = $sale->subscribe_id;
            $sale->item_seller = 'Admin';
            $sale->seller_id = '';
        } elseif (!empty($sale->promotion_id) and !empty($sale->promotion)) {
            $sale->item_title = $sale->promotion->title;
            $sale->item_id = $sale->promotion_id;
            $sale->item_seller = 'Admin';
            $sale->seller_id = '';
        } else {
            $sale->item_title = '---';
            $sale->item_id = '---';
            $sale->item_seller = '---';
            $sale->seller_id = '';
        }

        return $sale;
    }

    private function getSalesFilters($query, $request)
    {
        $item_title = $request->get('item_title');
        $from = $request->get('from');
        $to = $request->get('to');
        $status = $request->get('status');
        $webinar_ids = $request->get('webinar_ids', []);
        $teacher_ids = $request->get('teacher_ids', []);
        $student_ids = $request->get('student_ids', []);
        $userIds = array_merge($teacher_ids, $student_ids);

        if (!empty($item_title)) {
            $ids = Webinar::whereTranslationLike('title', "%$item_title%")->pluck('id')->toArray();
            $webinar_ids = array_merge($webinar_ids, $ids);
        }

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($status)) {
            if ($status == 'success') {
                $query->whereNull('refund_at');
            } elseif ($status == 'refund') {
                $query->whereNotNull('refund_at');
            }
        }

        if (!empty($webinar_ids) and count($webinar_ids)) {
            $query->whereIn('webinar_id', $webinar_ids);
        }

        if (!empty($userIds) and count($userIds)) {
            $query->where(function ($query) use ($userIds) {
                $query->whereIn('buyer_id', $userIds);
                $query->orWhereIn('seller_id', $userIds);
            });
        }

        return $query;
    }

    public function refund($id)
    {
        $this->authorize('admin_sales_refund');

        $sale = Sale::findOrFail($id);

        if (!empty($sale->amount) or $sale->amount > 0 or !empty($sale->order_id)) {
            //dd('here');
            $order = Order::findOrFail($sale->order_id);
            Accounting::refundAccounting($order);
        }


        $sale->update(['refund_at' => time()]);

        return back();
    }

    public function invoice($id)
    {
        $this->authorize('admin_sales_invoice');

        $sale = Sale::where('id', $id)
            ->with([
                'order',

                'buyer' => function ($query) {
                    $query->select('id', 'full_name');
                },
                'webinar' => function ($query) {
                    $query->with([
                        'teacher' => function ($query) {
                            $query->select('id', 'full_name');
                        },
                        'creator' => function ($query) {
                            $query->select('id', 'full_name');
                        },
                        'webinarPartnerTeacher' => function ($query) {
                            $query->with([
                                'teacher' => function ($query) {
                                    $query->select('id', 'full_name');
                                },
                            ]);
                        }
                    ]);
                }
            ])
            ->first();

        if (!empty($sale)) {
            $webinar = $sale->webinar;

            if (!empty($webinar)) {
                $data = [
                    'pageTitle' => trans('webinars.invoice_page_title'),
                    'sale' => $sale,
                    'webinar' => $webinar
                ];

                return view('admin.financial.sales.invoice', $data);
            }
        }

        abort(404);
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('admin_sales_export');

        $query = Sale::query();

        $salesQuery = $this->getSalesFilters($query, $request);

        $sales = $salesQuery->orderBy('created_at', 'desc')
            ->with([
                'buyer',
                'webinar',
                'meeting',
                'subscribe',
                'promotion'
            ])
            ->get();

        foreach ($sales as $sale) {
            $sale = $this->makeTitle($sale);
        }

        $export = new salesExport($sales);

        return Excel::download($export, 'sales.xlsx');
    }

    /**
     * Function to generate Invoice at Xero, against the sale generated in the system.
     *
     * @param integer $saleId
     * @return void
     */
    public function pushSaleToXero(int $saleId)
    {
        $accessToken = Xero::getAccessToken();
        $saleId = (int)$saleId;
        $message = trans('admin/main.xero_authentication_failed');
        if (!empty($accessToken)) {
            //get Sale data
            $sale = Sale::where('id', $saleId)
                ->orderBy('created_at', 'desc')
                ->with([
                    'buyer',
                    'webinar',
                    'meeting',
                    'subscribe',
                    'promotion',
                    'seller',
                    'paidFor',
                    'referenceSale',
                    'isReferenceFor'
                ])->first();
            
            if (!empty($sale) && empty($sale->xero_invoice_id)) {
                //search if this contact already exists in Xero
                $buyer = $sale->buyer;
                $xeroContact = null;
                if (!empty($buyer) && !empty($buyer->xero_contact_id)) {
                    $xeroContact = Xero::contacts()->find($buyer->xero_contact_id);
                } 
                
                if (empty($xeroContact) && !empty($buyer)) {
                    //create contact in Xero
                    $contactData = [
                        'Name'          =>  $buyer->full_name,
                        'EmailAddress'  =>  $buyer->email,
                        'Addresses'       =>  [
                            [
                                'AddressType'   =>  'STREET',
                                'AddressLine1' =>  $buyer->address,
                            ]
                        ],
                        'Phones'        =>  [
                            [
                                'PhoneType'     =>  'DEFAULT',
                                'PhoneNumber'   =>  $buyer->mobile
                            ]

                        ],
                    ];

                    $xeroContact = Xero::contacts()->store($contactData);

                    $buyer->xero_contact_id = $xeroContact['ContactID'];
                    $buyer->save();
                }

                //contact id
                //$xeroContact->ContactId
                if (!empty($xeroContact) && isset($xeroContact['ContactID'])) {
                    //prepare invoice line-item
                    $saleItem = null;
                    $saleItemType = null;
                    $saleItemName = null;
                    $sellerName = '';
                    if (!empty($seller) ) {
                        $sellerName = $seller->full_name;
                    }
                    
                    if (!empty($sale->webinar)) {
                        $saleItem = $sale->webinar;
                        $saleItemType = Sale::$xeroSaleItemPrefixes[$sale->webinar->type];
                        $saleItemName = $saleItem->title . " (by {$sellerName})";
                    } else if (!empty($sale->meeting)) {
                        $saleItem = $sale->meeting;
                        $saleItemType = Sale::$xeroSaleItemPrefixes['meeting']; 
                        $saleItemName = 'Meeting with '. $sellerName;
                    } else if (!empty($sale->subscribe)) {
                        $saleItem = $sale->subscribe;
                        $saleItemType = Sale::$xeroSaleItemPrefixes['subscribe'];
                        $saleItemName = 'Subscription (' .$saleItem->subscribe_id . ') purchased';
                    } else if (!empty($sale->promotion)) {
                        $saleItem = $sale->promotion;
                        $saleItemType = Sale::$xeroSaleItemPrefixes['promotion'];
                        $saleItemName = 'Promotion (' .$saleItem->promotion_id . ') purchased';
                    }
                    $itemCodeForXero = $saleItemType . '_' . $saleItem->id;
                    
                    $lineItem = [
                        "Code"  => $itemCodeForXero,
                        "Name"  => $saleItemName,
                        /* "SalesDetails" => [
                            "UnitPrice" => $sale->amount,
                            "AccountCode" => "200"
                        ], */
                        "Quantity"  =>  1,
                        "UnitAmount"  =>  $sale->amount,
                        "TaxType" => "OUTPUT",
                        "TaxAmount" => $sale->tax,
                        "LineAmount" => $sale->amount,
                        "AccountCode" => env('XERO_SALE_ACCOUNT_CODE', 200) //fallback to default Xero Sale Account Code
                    ];
                    $lineItemDescription = $saleItem->seo_description ?? $saleItem->description ?? null;
                    if (!empty($lineItemDescription)) {
                        $lineItem['Description'] = $lineItemDescription;
                        $lineItem['PurchaseDescription'] = $lineItemDescription;
                    }
                    //prepare invoice data
                    $invoiceData = [
                        'Type'      =>  'ACCREC',
                        'Contact'   =>  $xeroContact,
                        'LineItems' =>  [$lineItem],
                        'Date'      =>  Carbon::createFromTimestamp($sale->created_at)->format('Y-m-d'),
                        'DueDate'   =>  Carbon::createFromTimestamp($sale->created_at)->addDays(14)->format('Y-m-d'),
                        'Status'    =>  'AUTHORISED',
                        "LineAmountTypes"   =>  'Exclusive',
                        "SubTotal"  =>  $sale->amount,
                        "TotalTax"  =>  $sale->tax,
                        "Total"     =>  $sale->total_amount,
                        
                    ];
                    //create Xero invoice
                    $xeroInvoice = Xero::invoices()->store($invoiceData);

                    //update invoice reference in sales table
                    $sale->xero_invoice_id = $xeroInvoice['InvoiceID'];
                    //update status to pushed to Xero
                    $sale->payment_status = Sale::$paymentStatus['pushed_to_xero'];
                    $sale->save();
                    
                    $message = trans('admin/main.invoice_pushed_to_xero');
                    //return back with a success message
                    return redirect()->back()->with('success', $message);
                }

            }
            $message = trans('admin/main.incorrect_missing_sale_reference');
            //return back with an error message
            return redirect()->back()->with('error', $message);
        }
        /* return back with an authorization error
        although, execution will never reach here, as checking for access token will redirect user
        to authenticate if token doesn't exist or is expired  */
        return redirect()->back()->with('error', $message);

    }
}
