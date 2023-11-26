<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use App\Models\Card;

class ReportExport implements FromCollection, WithHeadings , WithMapping , WithStrictNullComparison
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */

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
            'Asset IP',
            'IP & Vuln Id',
            'Port',
            'Protocol',
            'OS Type/Version',
            'OS Version',
            'Business Unit',
            'Class',
            'CVE ID',
            'CVSS Score',
            'Severity',
            'Solution',
            'Impact Of Vulnerability',
            'Scan Date Time',
            'Background',
            'Service',
            'Remediation',
            'References',
            'Exception',
            'Tags',
            'Asset Version',
            'Model',
            'Make',
            'Asset Type',
            'Host Name',
            'PLK_VLAN10 (POS-SICOM) Subnet',
            'PLK_VLAN70 (Kiosk)',
            'PLK_VLAN254 (Meraki-Management)',
            'PLK_VLAN4 Subnet',
            'BK_VLAN10 (POS-SICOM) Subnet',
            'BK_VLAN70 (Kiosk)',
            'BK_VLAN254 (Meraki-Management)',
            'BK_VLAN4 Subnet',
            'THS_VLAN10 (POS-SICOM) Subnet',
            'THS_VLAN70 (Kiosk)',
            'THS_VLAN254 (Meraki-Management)',
            'THS_VLAN4 Subnet'
        ];
        
    }
    public function __construct($request)
    {
    	//$this->request = $request;
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
            $card->board->title,
            optional($assigned_to)->name,
            $start_date,
            $end_date,
            $due_date,
            $card->asset_ip,
            $card->ip_and_vuln_id,
            $card->port,
            $card->protocol,
            $card->os_type,
            $card->os_version,
            $card->business_unit,
            $card->class,
            $card->cve_id,
            $card->cvss_score,
            $card->severity,
            $card->solution,
            $card->impact_of_vulnerability,
            $card->scan_date_time,
            $card->background,
            $card->service,
            $card->remediation,
            $card->references,
            $card->exception,
            $card->tags,
            $card->asset_version,
            $card->model,
            $card->make,
            $card->asset_type,
            $card->host_name,
            $card->PLK_VLAN10_POS_SICOM_Subnet,
            $card->PLK_VLAN70_Kiosk_Subnet,
            $card->PLK_VLAN254_Meraki_Management_Subnet,
            $card->PLK_VLAN4_Subnet,
            $card->BK_VLAN10_POS_SICOM_Subnet,
            $card->BK_VLAN70_Kiosk_Subnet,
            $card->BK_VLAN254_Meraki_Management_Subnet,
            $card->BK_VLAN4_Subnet,
            $card->THS_VLAN10_POS_SICOM_Subnet,
            $card->THS_VLAN70_Kiosk_Subnet,
            $card->THS_VLAN254_Meraki_Management_Subnet,
            $card->THS_VLAN4_Subnet
         ];
    }

    public function collection()
    {
        try {
            $reports = Card::with('users','board')->latest()->get();
            return $reports;
        } catch (\Exception $e) {
            logger($e);
        }
    }
}
