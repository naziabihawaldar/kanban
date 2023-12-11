<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Models\Import;
use App\Models\User;
use Auth;
use Session;    
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Imports\CardsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class CardController extends Controller
{
    public function importBoardData(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'file' => 'required',
        ]);
       
        if ($request->hasFile('file')) 
        {
            $board_id = $request->board_id;
            $file = $request->file('file');
            if ($file === null) {
                logger("Invalid file");
            }
            if (isset($request->name) && $request->name != '') {
               
                $six_digit_random_number = mt_rand(100000, 999999);
                $file_name = $six_digit_random_number . 'import_logs';
                $exp = explode('.', $file_name);
                $create_file = $exp[0] . '.csv';
                if (!file_exists($file_name)) {
                    $headers = array(
                        "Content-type" => "text/csv",
                        "Content-Disposition" => "attachment; filename=" . $create_file,
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $myfile = fopen($create_file, "a") or die("Unable to open file!");
                    fputcsv($myfile, [
                        'Vulnerability Name',
                        'Vulnerability ID',
                        'Vulnerability Description',
                        'Vulnerability Details',
                        'Asset IP',
                        'IP & Vuln Id',
                        'Port',
                        'Protocol',
                        'OS Type/Version',
                        'Os Version',
                        'Business Unit',
                        'Class',
                        'CVE ID',
                        'CVSS Score',
                        'Severity',
                        'Solution',
                        'Impact Of Vulnerabilty',
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
                        'THS_VLAN4 Subnet',
                        'Comments',
                        'Status',
                        'Task assigned date',
                        'Assigned by Name',
                        'Assigned By Email',
                        'Start Date',
                        'End Date',
                        'Due Date',
                        'Current status',
                        'Inputs',
                        'upload status',
                        'upload error message'
                    ]);
                    $file_path = public_path() . '\\' . $create_file;
                    $importFile = new Import;
                    $importFile->name = $request->name;
                    $importFile->board_id = $board_id;
                    $importFile->file_path = $create_file;
                    $importFile->uploaded_by = Auth::id();
                    $importFile->save();

                    $data = Excel::import(new CardsImport($board_id, $importFile, $create_file), $file[0]);

                    $board = Board::find($board_id);

                    activity('create')
                        ->performedOn($board) // Entry add in table. model name(subject_type) & id(subject_id)
                        ->causedBy(Auth::user()) //causer_id = admin id, causer type = admin model
                        ->log('A file was imported by ' . Auth::user()->name);
                    // Process the uploaded file
                }
            }
            // return ['status' => 'success', 'message' => 'success'];
            // return back()->with(['name' => 'test']);
            return back()->with('status', 'verification-link-sent');
        }
    }

    public function assignCardToUser(Request $request)
    {
        $card = Card::find($request->card_id);
        if ($card) {
            $user = User::find($request->user_id);
            if ($user) {
                $card->users()->sync([$request->user_id]);
                $board = Board::find($card->board_id);
                if ($board) {
                    $board->assignees()->attach($user);
                }
                $mailData = [
                    'username' => $user->name,
                    'taskTitle' => $card->content,
                    'body' => 'This is for testing email using smtp.'
                ];

                activity('assignee')
                    ->performedOn($card) // Entry add in table. model name(subject_type) & id(subject_id)
                    ->causedBy(Auth::user()) //causer_id = admin id, causer type = admin model
                    ->log(Auth::user()->name . ' changed the Assignee to ' . $user->name);

                try {
                    dispatch(new \App\Jobs\SendMailNotification($mailData, 'customer_send_ticket_reopen', $user->email));
                } catch (\Exception $e) {
                    logger($e);
                }
            }
        }
        return back();
    }

    public function getCardDetails(Request $request)
    {
        try {
            $card = Card::find($request->card_id);
            if ($card) {
                $card->all_comments = $card->comments;
                $activities = Activity::where('subject_type', get_class($card))->get();
                $card->activities = $activities;
                return ['status' => 'success', 'message' => 'success', 'data' => $card];
            }
        } catch (\Exception $e) {
            logger($e);
            return ['status' => 'error', 'message' => 'Error Occurred'];
        }
    }

    public function getActivities(Request $request)
    {
        try 
        {
            $columns = Column::where('board_id',$request->board_id)->get();
            $activities = Activity::with('causer')->where('subject_id', $request->card_id)->orderByDesc('created_at')->get();
            $board = Board::find($request->board_id);
            $board_user = $board->user;
            return ['status' => 'success', 'message' => 'success', 'data' => $activities, 'columns' => $columns,'board_user' => $board_user];
        } catch (\Exception $e) {
            logger($e);
            return ['status' => 'error', 'message' => 'Error Occurred'];
        }
    }

    public function storeTask(Request $request)
    {
        $this->validate($request, [
            'board_id' => 'required',
            'vulnerabilityName' => 'required',
            'vulnerabilityID' => 'required',
            'description' => 'required',
            'vulnerabilityDetails' => 'required',
            'ip_and_vuln_id' => 'required',
            'port' => 'required',
            'protocol' => 'required',
            'os_type' => 'required',
            'os_version' => 'required',
            'business_unit' => 'required',
            'class' => 'required',
            'cve_id' => 'required',
            'cvss_score' => 'required',
            'severity' => 'required',
            'solution' => 'required',
            'impact_of_vulnerability' => 'required',
            'scan_date_time' => 'required|date',
            'background' => 'required',
            'service' => 'required',
            'remediation' => 'required',
            'references' => 'required',
            'exception' => 'required',
            'tags' => 'required',
            'asset_version' => 'required',
            'model' => 'required',
            'make' => 'required',
            'asset_type' => 'required',
            'host_name' => 'required',
            'PLK_VLAN10_POS_SICOM_Subnet' => 'required',
            'PLK_VLAN70_Kiosk_Subnet' => 'required',
            'PLK_VLAN254_Meraki_Management_Subnet' => 'required',
            'PLK_VLAN4_Subnet' => 'required',
            'BK_VLAN10_POS_SICOM_Subnet' => 'required',
            'BK_VLAN70_Kiosk_Subnet' => 'required',
            'BK_VLAN254_Meraki_Management_Subnet' => 'required',
            'BK_VLAN4_Subnet' => 'required',
            'THS_VLAN10_POS_SICOM_Subnet' => 'required',
            'THS_VLAN70_Kiosk_Subnet' => 'required',
            'THS_VLAN254_Meraki_Management_Subnet' => 'required',
            'THS_VLAN4_Subnet' => 'required',
        ]);  

        $start_date = $end_date = $due_date = null;
        if (isset($request->start_date) && $request->start_date != null) {
            $start_date = \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($request->start_date))->format('Y-m-d');
        }
        if (isset($request->end_date) && $request->end_date != null) {
            $end_date = \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($request->end_date))->format('Y-m-d');
        }
        if (isset($request->due_date) && $request->due_date != null) {
            $due_date = \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($request->due_date))->format('Y-m-d');
        }

        $board = Board::find($request->board_id);
        if($board)
        {
            $card = Card::where('vulnerability_id', $request->vulnerabilityID)->where('asset_ip', $request->asset_ip)->first();
            if(!$card)
            {
                $firstColumn = Column::where('title','To Do')->where('board_id',$request->board_id)->first();
                $card = new Card;
                if($firstColumn)
                {
                    $card->column_id = $firstColumn->id;
                }
            }
                $card->board_id = $board->id;
                $card->vulnerability_id = $request->vulnerabilityID;
                $card->asset_ip = $request->asset_ip;
                $card->content = $request->description;
                $card->vulnerability_title = $request->vulnerabilityName;
                $card->vulnerability_desc = $request->description;
                $card->vulnerability_details = $request->vulnerabilityDetails;
                $card->asset_ip = $request->asset_ip;
                $card->ip_and_vuln_id = $request->ip_vuln_id;
                $card->port = $request->port;
                $card->protocol = $request->protocol;
                $card->os_type = $request->os_typeversion;
                $card->os_version = $request->os_version;
                $card->business_unit = $request->business_unit;
                $card->class = $request->class;
                $card->cve_id = $request->cve_id;
                $card->cvss_score = $request->cvss_score;
                $card->severity = $request->severity;
                $card->solution = $request->solution;
                $card->impact_of_vulnerability = $request->impact_of_vulnerabilty;
                $card->scan_date_time = $request->scan_date_time; // Assuming you want the current date and time
                $card->background = $request->background;
                $card->service = $request->service;
                $card->remediation = $request->remediation;
                $card->references = $request->references;
                $card->exception = $request->exception;
                $card->tags = $request->tags;
                $card->asset_version = $request->asset_version;
                $card->model = $request->model;
                $card->make = $request->make;
                $card->host_name = $request->host_name;
                $card->asset_type = $request->asset_type;
                $card->PLK_VLAN10_POS_SICOM_Subnet = $request->plk_vlan10_pos_sicom_subnet;
                $card->PLK_VLAN70_Kiosk_Subnet = $request->plk_vlan70_kiosk;
                $card->PLK_VLAN254_Meraki_Management_Subnet = $request->plk_vlan254_meraki_management;
                $card->PLK_VLAN4_Subnet = $request->plk_vlan4_subnet;
                $card->BK_VLAN10_POS_SICOM_Subnet = $request->bk_vlan10_pos_sicom_subnet;
                $card->BK_VLAN70_Kiosk_Subnet = $request->bk_vlan70_kiosk;
                $card->BK_VLAN254_Meraki_Management_Subnet = $request->bk_vlan254_meraki_management;
                $card->BK_VLAN4_Subnet = $request->bk_vlan4_subnet;
                $card->THS_VLAN10_POS_SICOM_Subnet = $request->ths_vlan10_pos_sicom_subnet;
                $card->THS_VLAN70_Kiosk_Subnet = $request->ths_vlan70_kiosk;
                $card->THS_VLAN254_Meraki_Management_Subnet = $request->ths_vlan254_meraki_management;
                $card->THS_VLAN4_Subnet = $request->ths_vlan4_subnet;
                $card->start_date = $start_date; // Assuming you want the current date and time
                $card->end_date = $end_date;   // Assuming you want the current date and time
                $card->due_date = $due_date;   // Assuming you want the current date and time
                $card->created_by = Auth::id();
                $card->save();
               
                if(isset($request->assignee) && $request->assignee != '')
                {
                    $user = User::find($request->assignee);
                    if($user)
                    {
                        $card->users()->sync([$user->id]);
                    }
                }
                activity('create')
                    ->performedOn($card) // Entry add in table. model name(subject_type) & id(subject_id)
                    ->causedBy(Auth::user()) //causer_id = admin id, causer type = admin model
                    ->log(Auth::user()->name . ' created the Issue');
           
        }    
        return back();
    }
}
