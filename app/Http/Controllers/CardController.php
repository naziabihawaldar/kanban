<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Card;
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
        try {
            $card = new Card;
            $activities = Activity::with('causer')->where('subject_id', $request->card_id)->orderByDesc('created_at')->get();
            return ['status' => 'success', 'message' => 'success', 'data' => $activities];
        } catch (\Exception $e) {
            logger($e);
            return ['status' => 'error', 'message' => 'Error Occurred'];
        }
    }
}
