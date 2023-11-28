<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Card;
use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        return inertia("Report");
    }


    public function getReportsData(Request $request)
    {
        try 
        {
            $filters = $request->filter;
            $reports = Card::with('users', 'board');
            if ($request->has('sortBy')) 
            {
                $sort_data = $request->sortBy[0];
                // logger($sort_data['order']);
                $sort_order = 'DESC';
                if ($sort_data['order'] == 'asc') {
                    $sort_order = 'ASC';
                }
                $reports = $reports->orderBy($sort_data['key'], $sort_order);
            }
            if (isset($filters['severity']) && $filters['severity'] != '') {
                $reports = $reports->where('severity', $filters['severity']);
            }
            if (isset($filters['assignee']) && $filters['assignee'] != '') {
                $reports = $reports->whereHas('users', function ($q) use ($filters) {
                    $q->where('users.id', $filters['assignee']);
                });
            }
            if (isset($filters['domain']) && $filters['domain'] != '') {
                $reports = $reports->where('severity', $filters['domain']);
            }
            if (isset($filters['start_date']) && $filters['start_date'] != '') {
                $reports = $reports->whereDate('start_date', $filters['start_date']);
            }
            if (isset($filters['end_date']) && $filters['end_date'] != '') {
                $reports = $reports->whereDate('end_date', $filters['end_date']);
            }
            if (isset($filters['due_date']) && $filters['due_date'] != '') {
                $reports = $reports->whereDate('due_date', $filters['due_date']);
            }
            if (isset($filters['scan_date']) && $filters['scan_date'] != '') {
                $reports = $reports->whereDate('scan_date_time', $filters['scan_date']);
            }
            if (isset($filters['os_type']) && $filters['os_type'] != '') {
                $reports = $reports->where('os_type',$filters['os_type']);
            }
            if (isset($filters['os_version']) && $filters['os_version'] != '') {
                $reports = $reports->where('os_version', $filters['os_version']);
            }
            if (isset($filters['business_unit']) && $filters['business_unit'] != '') {
                $reports = $reports->where('business_unit', $filters['business_unit']);
            }
            if (isset($filters['ip_and_vuln_id']) && $filters['ip_and_vuln_id'] != '') {
                $reports = $reports->where('ip_and_vuln_id', $filters['ip_and_vuln_id']);
            }
            $reports = $reports->latest()->paginate($request->rowsPerPage);
            return ['status' => 'success', 'message' => 'success', 'data' => $reports];
        } catch (\Exception $e) {
            logger($e);
            return ['status' => 'error', 'message' => 'Error Occurred'];
        }

    }

    public function export(Request $request)
    {
        logger($request);
        return (new ReportExport($request))->download('reports.csv');
    }
}
