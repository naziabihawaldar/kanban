<?php

namespace App\Imports;

use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Models\User;
use Auth;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Spatie\Permission\Models\Role;

class CardsImport implements ToModel, WithHeadingRow
{
    use Importable;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public $boardId,$imprt_file;

    public function __construct($board_id,$import_file) {
        $this->boardId = $board_id;
        $this->imprt_file = $import_file;
    }
    public function model(array $row)
    {
        
        $start_date = $end_date = $due_date = null;
        if(isset($row['start_date']) && $row['start_date'] != null)
        {
            $start_date =  \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['start_date']))->format('Y-m-d');
        }
        if(isset($row['end_date']) && $row['end_date'] != null)
        {
            $end_date =  \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['end_date']))->format('Y-m-d');
        }
        if(isset($row['due_date']) && $row['due_date'] != null)
        {
            $due_date =  \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['due_date']))->format('Y-m-d');
        }

        $board_id = $this->boardId;  
        $import_id = $this->imprt_file;  
        $board = Board::find($board_id);
        if($board)
        {   
            $column = Column::where('board_id',$board_id)->where('title','To Do')->first();
            if($column)
            {
                $newRecord = Card::where('vulnerability_id',$row['vulnerability_id'])->where('asset_ip',$row['asset_ip'])->first();
                if(!$newRecord)
                {
                    $newRecord = new Card;
                    $newRecord->column_id = $column->id;
                    $newRecord->board_id = $board_id;
                    $newRecord->vulnerability_id = $row['vulnerability_id'];
                }
                $newRecord->import_id = optional($import_id)->id;
                $newRecord->content = $row['vulnerability_description'];
                $newRecord->vulnerability_title = $row['vulnerability_name'];
                $newRecord->vulnerability_desc = $row['vulnerability_description'];
                $newRecord->vulnerability_details = $row['vulnerability_details'];
                $newRecord->asset_ip = $row['asset_ip'];
                $newRecord->ip_and_vuln_id = $row['ip_vuln_id'];
                $newRecord->port = $row['port'];
                $newRecord->protocol = $row['protocol'];
                $newRecord->os_type = $row['os_typeversion'];
                $newRecord->os_version = $row['os_version'];
                $newRecord->business_unit = $row['business_unit'];
                $newRecord->class = $row['class'];
                $newRecord->cve_id = $row['cve_id'];
                $newRecord->cvss_score = $row['cvss_score'];
                $newRecord->severity = $row['severity'];
                $newRecord->solution = $row['solution'];
                $newRecord->impact_of_vulnerability = $row['impact_of_vulnerabilty'];
                $newRecord->scan_date_time = $row['scan_date_time']; // Assuming you want the current date and time
                $newRecord->background = $row['background'];
                $newRecord->service = $row['service'];
                $newRecord->remediation = $row['remediation'];
                $newRecord->references = $row['references'];
                $newRecord->exception = $row['exception'];
                $newRecord->tags = $row['tags'];
                $newRecord->asset_version = $row['asset_version'];
                $newRecord->model = $row['model'];
                $newRecord->make = $row['make'];
                $newRecord->host_name = $row['host_name'];
                $newRecord->asset_type = $row['asset_type'];
                $newRecord->PLK_VLAN10_POS_SICOM_Subnet = $row['plk_vlan10_pos_sicom_subnet'];
                $newRecord->PLK_VLAN70_Kiosk_Subnet = $row['plk_vlan70_kiosk'];
                $newRecord->PLK_VLAN254_Meraki_Management_Subnet = $row['plk_vlan254_meraki_management'];
                $newRecord->PLK_VLAN4_Subnet = $row['plk_vlan4_subnet'];
                $newRecord->BK_VLAN10_POS_SICOM_Subnet = $row['bk_vlan10_pos_sicom_subnet'];
                $newRecord->BK_VLAN70_Kiosk_Subnet = $row['bk_vlan70_kiosk'];
                $newRecord->BK_VLAN254_Meraki_Management_Subnet = $row['bk_vlan254_meraki_management'];
                $newRecord->BK_VLAN4_Subnet = $row['bk_vlan4_subnet'];
                $newRecord->THS_VLAN10_POS_SICOM_Subnet = $row['ths_vlan10_pos_sicom_subnet'];
                $newRecord->THS_VLAN70_Kiosk_Subnet = $row['ths_vlan70_kiosk'];
                $newRecord->THS_VLAN254_Meraki_Management_Subnet = $row['ths_vlan254_meraki_management'];
                $newRecord->THS_VLAN4_Subnet = $row['ths_vlan4_subnet'];
                $newRecord->start_date = $start_date; // Assuming you want the current date and time
                $newRecord->end_date = $end_date;   // Assuming you want the current date and time
                $newRecord->due_date = $due_date;   // Assuming you want the current date and time
                $newRecord->created_by = Auth::id();
                $newRecord->save();
                $user = User::where('email',$row['assigned_by_email'])->first();
                if(!$user) 
                {
                    $user = new User;
                    $user->name = $row['assigned_by_name'];
                    $user->email = $row['assigned_by_email'];
                    $user->password = bcrypt('password');
                    $user->save();
                    $role = Role::find(3);
                    $user->assignRole($role);
                }
                $newRecord->users()->sync([$user->id]);

                activity('create')
                ->performedOn($newRecord) // Entry add in table. model name(subject_type) & id(subject_id)
                ->causedBy(Auth::user()) //causer_id = admin id, causer type = admin model
                ->log(Auth::user()->name.' created the Issue');
            }
        }
        
    }
}
