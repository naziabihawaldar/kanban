<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use App\Models\Card;

class ConsolidatedReportExport implements FromCollection, WithHeadings , WithMapping , WithStrictNullComparison
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $request; 

    public function headings():array
    {
        return [
            'Vulnerability Name',
            'Vulnerability ID',
            'Vulnerability Description',
            'Board',
            'Assigned To',
            'Start Date',
            'End Date',
            'Due Date',
            'Task Status'
        ];
    }
    public function __construct($request)
    {
    	$this->request = $request;
    }
    public function map($card): array
    {
        $assigned_to = $card->users()->first();
        $start_date = \Carbon\Carbon::parse($card->start_date)->format('M d, Y');
        $end_date = \Carbon\Carbon::parse($card->end_date)->format('M d, Y');
        $due_date = \Carbon\Carbon::parse($card->due_date)->format('M d, Y');
        return [
            $card->vulnerability_title,
            $card->vulnerability_id,
            $card->vulnerability_desc,
            optional($card->board)->title,
            optional($assigned_to)->name,
            $start_date,
            $end_date,
            $due_date,
            $card->task_status,
         ];
    }

    public function collection()
    {
        try {
            $request = $this->request;
            logger($request);
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
            if (isset($filters['task_status']) && $filters['task_status'] != '') {
                $reports = $reports->where('task_status', $filters['task_status']);
            }
            if (isset($filters['project']) && $filters['project'] != '') {
                $reports = $reports->where('board_id', $filters['project']);
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
            $reports = $reports->latest()->get();
            return $reports;
        } catch (\Exception $e) {
            logger($e);
        }
    }
}
