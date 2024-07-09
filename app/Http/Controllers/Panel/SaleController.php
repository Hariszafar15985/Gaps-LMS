<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Sale;
use App\Models\SaleLog;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Sale::where('seller_id', $user->id)
            ->whereNull('refund_at');

        $studentIds = deepClone($query)->pluck('buyer_id')->toArray();
        $students = User::select('id', 'full_name')
            ->whereIn('id', array_unique($studentIds))
            ->get();

        $getStudentCount = count($studentIds);
        $getWebinarsCount = count(array_filter(deepClone($query)->pluck('webinar_id')->toArray()));
        $getMeetingCount = count(array_filter(deepClone($query)->pluck('meeting_id')->toArray()));


        $query = $this->filters($query, $request);

        $sales = $query->orderBy('created_at', 'desc')
            ->with('webinar')
            ->paginate(10);

        $userWebinars = Webinar::select('id')
            ->where('status', 'active')
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id);
            })->get();

        $data = [
            'pageTitle' => trans('admin/pages/financial.sales_page_title'),
            'sales' => $sales,
            'studentCount' => $getStudentCount,
            'webinarCount' => $getWebinarsCount,
            'meetingCount' => $getMeetingCount,
            'totalSales' => $user->getSaleAmounts(),
            'userWebinars' => $userWebinars,
            'students' => $students,
        ];

        return view(getTemplate() . '.panel.financial.sales', $data);
    }

    public function organizationSummary(Request $request)
    {
        $authUser = auth()->user();
        if (!$authUser->isOrganization() || !isFinancialTabEnabled()) {
            abort(403);
        }
        $query = Sale::query()->where('buyer_id', $authUser->id);

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

        $sales = $salesQuery->orderBy('created_at', 'desc')
            ->with([
                'buyer',
                'webinar',
                'meeting',
                'subscribe',
                'promotion',
                'seller',
                'paidFor' => function($query) {
                    $query->with('organizationSites', 'manager');
                },
                'referenceSale',
                'isReferenceFor'
            ])
            ->paginate(10);
        
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

        return view(getTemplate() . '.panel.financial.lists', $data);
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

    private function filters($query, $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $student_id = $request->input('student_id');
        $webinar_id = $request->input('webinar_id');
        $type = $request->input('type');

        if (!empty($from) and !empty($to)) {
            $from = strtotime($from);
            $to = strtotime($to);

            $query->whereBetween('created_at', [$from, $to]);
        } else {
            if (!empty($from)) {
                $from = strtotime($from);
                $query->where('created_at', '>=', $from);
            }

            if (!empty($to)) {
                $to = strtotime($to);

                $query->where('created_at', '<', $to);
            }
        }

        if (isset($type) && $type !== 'all') {
            $query->where('type', $type);
        }

        if (!empty($student_id) and $student_id != 'all') {
            $query->where('buyer_id', $student_id);
        }

        if (!empty($webinar_id) and $webinar_id != 'all') {
            $query->where('webinar_id', $webinar_id);
        }

        return $query;
    }
}
