<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vulnerability_id' => $this->vulnerability_id,
            'vulnerability_title' => $this->vulnerability_title,
            'vulnerability_desc' => $this->vulnerability_desc,
            'vulnerability_details' => $this->vulnerability_details,
            'asset_ip' => $this->asset_ip,
            'ip_and_vuln_id' => $this->ip_and_vuln_id,
            'port' => $this->port,
            'protocol' => $this->protocol,
            'os_type' => $this->os_type,
            'os_version' => $this->os_version,
            'business_unit' => $this->business_unit,
            'class' => $this->class,
            'cve_id' => $this->cve_id,
            'cvss_score' => $this->cvss_score,
            'severity' => $this->severity,
            'solution' => $this->solution,
            'impact_of_vulnerability' => $this->impact_of_vulnerability,
            'scan_date_time' => $this->scan_date_time,
            'background' => $this->background,
            'service' => $this->service,
            'remediation' => $this->remediation,
            'references' => $this->references,
            'exception' => $this->exception,
            'tags' => $this->tags,
            'asset_version' => $this->asset_version,
            'model' => $this->model,
            'make' => $this->make,
            'asset_type' => $this->asset_type,
            'host_name' => $this->host_name,
            'PLK_VLAN10_POS_SICOM_Subnet' => $this->PLK_VLAN10_POS_SICOM_Subnet,
            'PLK_VLAN70_Kiosk_Subnet' => $this->PLK_VLAN70_Kiosk_Subnet,
            'PLK_VLAN254_Meraki_Management_Subnet' => $this->PLK_VLAN254_Meraki_Management_Subnet,
            'PLK_VLAN4_Subnet' => $this->PLK_VLAN4_Subnet,
            'BK_VLAN10_POS_SICOM_Subnet' => $this->BK_VLAN10_POS_SICOM_Subnet,
            'BK_VLAN70_Kiosk_Subnet' => $this->BK_VLAN70_Kiosk_Subnet,
            'BK_VLAN254_Meraki_Management_Subnet' => $this->BK_VLAN254_Meraki_Management_Subnet,
            'BK_VLAN4_Subnet' => $this->BK_VLAN4_Subnet,
            'THS_VLAN10_POS_SICOM_Subnet' => $this->THS_VLAN10_POS_SICOM_Subnet,
            'THS_VLAN70_Kiosk_Subnet' => $this->THS_VLAN70_Kiosk_Subnet,
            'THS_VLAN254_Meraki_Management_Subnet' => $this->THS_VLAN254_Meraki_Management_Subnet,
            'THS_VLAN4_Subnet' => $this->THS_VLAN4_Subnet,
            'content' => $this->content,
            'position' => $this->position,
            'column' => $this->column_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'due_date' => $this->due_date,
            'users' => $this->whenLoaded('users'),
            'comments' => $this->whenLoaded('comments'),
        ];
    }
}
