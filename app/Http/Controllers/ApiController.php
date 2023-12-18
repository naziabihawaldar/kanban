<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\Board;
use App\Models\Card;
use App\Models\Column;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|exists:users|max:255',
                'password' => 'required|min:6|max:255',
            ]);

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = User::where('email', $request->email)->first();
                $token = $user->createToken("*")->plainTextToken;
                $user->access_token = $token;
                $user->token_type = 'Bearer';
                $role_name = $user->roles->pluck('name')[0];
                $user->user_role = $role_name;
                return ['status' => 'success', 'message' => 'success', 'data' => $user];
            }
            return response()->json([
                'status' => 'error',
                'message' => "Invalid Credentials"
            ], 200);

        } catch (\Exception $e) {
            logger($e);
            return ['status' => "error", 'message' => 'Error Occurred. Please try again later'];
        }
    }

    public function getProjects(Request $request)
    {
        try {
            $boards = Board::orderBy('created_at', 'desc')->paginate($request->rowsPerPage);
            return ['status' => 'success', 'message' => 'success', 'data' => $boards];
        } catch (\Exception $e) {
            logger($e);
            return ['status' => 'error', 'message' => 'Error Occurred'];
        }
    }

    public function getTasks(Request $request)
    {
        try {
            $columns = Column::where('board_id', $request->board_id)->get();
            foreach ($columns as $column) {
                $cards = $column->cards()->with('users');

                if ($request->has('searchtxt') && $request->searchtxt != '') {
                    $cards = $cards->where('content', 'like', '%' . $request->searchtxt . '%');
                }

                if ($request->has('severity') && $request->severity != '') {
                    $cards = $cards->where('severity', $request->severity);
                }
                if ($request->has('assignee') && $request->assignee != '') {
                    $cards = $cards->whereHas('users', function ($q) use ($request, $column) {
                        $q->where('users.id', $request->assignee);
                    });
                }
                if ($request->has('imports') && $request->imports != '') {
                    $cards = $cards->whereHas('import', function ($q) use ($request) {
                        $q->where('imports.name', $request->imports);
                    });
                }
                if ($request->has('domain') && $request->domain != '') {
                    $cards = $cards->where('severity', $request->severity);
                }
                if ($request->has('scan_date') && $request->scan_date != '') {
                    $cards = $cards->whereDate('scan_date_time', $request->scan_date);
                }
                if ($request->has('os_type') && $request->os_type != '') {
                    $cards = $cards->where('os_type', $request->os_type);
                }
                if ($request->has('os_version') && $request->os_version != '') {
                    $cards = $cards->where('os_version', $request->os_version);
                }
                if ($request->has('business_unit') && $request->business_unit != '') {
                    $cards = $cards->where('business_unit', $request->business_unit);
                }
                if ($request->has('ip_and_vuln_id') && $request->business_unit != '') {
                    $cards = $cards->where('ip_and_vuln_id', $request->ip_and_vuln_id);
                }

                $cards = $cards->get();
                $column->cards = $cards;
            }
            return ['status' => 'success', 'data' => $columns];
        } catch (\Exception $e) {
            logger($e);
        }

    }
    public function storeTask(Request $request)
    {
        try {
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
                $start_date = \Carbon\Carbon::createFromFormat('Y-m-d', $request->start_date)->toDateString();
            }
            if (isset($request->end_date) && $request->end_date != null) {
                $end_date = \Carbon\Carbon::createFromFormat('Y-m-d', $request->end_date)->toDateString();
            }
            if (isset($request->due_date) && $request->due_date != null) {
                $due_date = \Carbon\Carbon::createFromFormat('Y-m-d', $request->due_date)->toDateString();
            }

            $board = Board::find($request->board_id);
            if ($board) {
                $card = Card::where('vulnerability_id', $request->vulnerabilityID)->where('asset_ip', $request->asset_ip)->first();
                if (!$card) {
                    $firstColumn = Column::where('title', 'To Do')->where('board_id', $request->board_id)->first();
                    $card = new Card;
                    if ($firstColumn) {
                        $card->column_id = $firstColumn->id;
                    }
                }
                $card->board_id = $board->id;
                $card->vulnerability_id = $request->vulnerabilityID;
                $card->content = $request->description;
                $card->vulnerability_title = $request->vulnerabilityName;
                $card->vulnerability_desc = $request->description;
                $card->vulnerability_details = $request->vulnerabilityDetails;
                $card->asset_ip = $request->asset_ip;
                $card->ip_and_vuln_id = $request->ip_and_vuln_id;
                $card->port = $request->port;
                $card->protocol = $request->protocol;
                $card->os_type = $request->os_type;
                $card->os_version = $request->os_version;
                $card->business_unit = $request->business_unit;
                $card->class = $request->class;
                $card->cve_id = $request->cve_id;
                $card->cvss_score = $request->cvss_score;
                $card->severity = $request->severity;
                $card->solution = $request->solution;
                $card->impact_of_vulnerability = $request->impact_of_vulnerability;
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
                $card->PLK_VLAN10_POS_SICOM_Subnet = $request->PLK_VLAN10_POS_SICOM_Subnet;
                $card->PLK_VLAN70_Kiosk_Subnet = $request->PLK_VLAN70_Kiosk_Subnet;
                $card->PLK_VLAN254_Meraki_Management_Subnet = $request->PLK_VLAN254_Meraki_Management_Subnet;
                $card->PLK_VLAN4_Subnet = $request->PLK_VLAN4_Subnet;
                $card->BK_VLAN10_POS_SICOM_Subnet = $request->BK_VLAN10_POS_SICOM_Subnet;
                $card->BK_VLAN70_Kiosk_Subnet = $request->BK_VLAN70_Kiosk_Subnet;
                $card->BK_VLAN254_Meraki_Management_Subnet = $request->BK_VLAN254_Meraki_Management_Subnet;
                $card->BK_VLAN4_Subnet = $request->BK_VLAN4_Subnet;
                $card->THS_VLAN10_POS_SICOM_Subnet = $request->THS_VLAN10_POS_SICOM_Subnet;
                $card->THS_VLAN70_Kiosk_Subnet = $request->THS_VLAN70_Kiosk_Subnet;
                $card->THS_VLAN254_Meraki_Management_Subnet = $request->THS_VLAN254_Meraki_Management_Subnet;
                $card->THS_VLAN4_Subnet = $request->THS_VLAN4_Subnet;
                $card->start_date = $start_date; // Assuming you want the current date and time
                $card->end_date = $end_date;   // Assuming you want the current date and time
                $card->due_date = $due_date;   // Assuming you want the current date and time
                $card->created_by = Auth::id();
                $card->save();

                if (isset($request->assignee) && $request->assignee != '') {
                    $user = User::find($request->assignee);
                    if ($user) {
                        $card->users()->sync([$user->id]);
                    }
                }
                activity('create')
                    ->performedOn($card) // Entry add in table. model name(subject_type) & id(subject_id)
                    ->causedBy(Auth::user()) //causer_id = admin id, causer type = admin model
                    ->log(Auth::user()->name . ' created the Task');

            }
            return ['status' => 'success', 'message' => 'success'];
        } catch (\Exception $e) {
            logger($e);
            return ['status' => 'error', 'message' => 'Error Occurred'];
        }

        return back();
    }

    public function updateTask(Request $request)
    {
        $this->validate($request, [
            'board_id' => 'required',
            'card_id' => 'required',
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
        try {
            $start_date = $end_date = $due_date = null;
            if (isset($request->start_date) && $request->start_date != null) {
                $start_date = \Carbon\Carbon::createFromFormat('Y-m-d', $request->start_date)->toDateString();
            }
            if (isset($request->end_date) && $request->end_date != null) {
                $end_date = \Carbon\Carbon::createFromFormat('Y-m-d', $request->end_date)->toDateString();
            }
            if (isset($request->due_date) && $request->due_date != null) {
                $due_date = \Carbon\Carbon::createFromFormat('Y-m-d', $request->due_date)->toDateString();
            }

            $board = Board::find($request->board_id);
            if ($board) {
                $card = Card::find($request->card_id);
                if (!$card) {
                    return ['status' => 'error', 'message' => 'Task Not Found'];
                }
                if ($card) {
                    $card->board_id = $board->id;
                    $card->vulnerability_id = $request->vulnerabilityID;
                    $card->content = $request->description;
                    $card->vulnerability_title = $request->vulnerabilityName;
                    $card->vulnerability_desc = $request->description;
                    $card->vulnerability_details = $request->vulnerabilityDetails;
                    $card->asset_ip = $request->asset_ip;
                    $card->ip_and_vuln_id = $request->ip_and_vuln_id;
                    $card->port = $request->port;
                    $card->protocol = $request->protocol;
                    $card->os_type = $request->os_type;
                    $card->os_version = $request->os_version;
                    $card->business_unit = $request->business_unit;
                    $card->class = $request->class;
                    $card->cve_id = $request->cve_id;
                    $card->cvss_score = $request->cvss_score;
                    $card->severity = $request->severity;
                    $card->solution = $request->solution;
                    $card->impact_of_vulnerability = $request->impact_of_vulnerability;
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
                    $card->PLK_VLAN10_POS_SICOM_Subnet = $request->PLK_VLAN10_POS_SICOM_Subnet;
                    $card->PLK_VLAN70_Kiosk_Subnet = $request->PLK_VLAN70_Kiosk_Subnet;
                    $card->PLK_VLAN254_Meraki_Management_Subnet = $request->PLK_VLAN254_Meraki_Management_Subnet;
                    $card->PLK_VLAN4_Subnet = $request->PLK_VLAN4_Subnet;
                    $card->BK_VLAN10_POS_SICOM_Subnet = $request->BK_VLAN10_POS_SICOM_Subnet;
                    $card->BK_VLAN70_Kiosk_Subnet = $request->BK_VLAN70_Kiosk_Subnet;
                    $card->BK_VLAN254_Meraki_Management_Subnet = $request->BK_VLAN254_Meraki_Management_Subnet;
                    $card->BK_VLAN4_Subnet = $request->BK_VLAN4_Subnet;
                    $card->THS_VLAN10_POS_SICOM_Subnet = $request->THS_VLAN10_POS_SICOM_Subnet;
                    $card->THS_VLAN70_Kiosk_Subnet = $request->THS_VLAN70_Kiosk_Subnet;
                    $card->THS_VLAN254_Meraki_Management_Subnet = $request->THS_VLAN254_Meraki_Management_Subnet;
                    $card->THS_VLAN4_Subnet = $request->THS_VLAN4_Subnet;
                    $card->start_date = $start_date; // Assuming you want the current date and time
                    $card->end_date = $end_date;   // Assuming you want the current date and time
                    $card->due_date = $due_date;   // Assuming you want the current date and time
                    $card->created_by = Auth::id();
                    $card->save();
                }


                if (isset($request->assignee) && $request->assignee != '') {
                    $user = User::find($request->assignee);
                    if ($user) {
                        $card->users()->sync([$user->id]);
                    }
                }
                activity('update')
                    ->performedOn($card) // Entry add in table. model name(subject_type) & id(subject_id)
                    ->causedBy(Auth::user()) //causer_id = admin id, causer type = admin model
                    ->log(Auth::user()->name . ' updated the Task');

                return ['status' => 'success', 'message' => 'Task updated successfully'];

            }
            return ['status' => 'error', 'message' => 'Project Not Found'];

        } catch (\Exception $e) {
            logger($e);
            return ['status' => 'error', 'message' => 'Error Occurred'];
        }
    }

    public function storeProject(Request $request)
    {
        try 
        {
            if (isset($request->title) && $request->title != '') 
            {
                $board = new Board;
                $board->title = $request->title;
                $board->user_id = Auth::id();
                $board->save();

                $todoColumn = new Column;
                $todoColumn->title = 'To Do';
                $todoColumn->board_id = $board->id;
                $todoColumn->save();
                $todoColumn = new Column;
                $todoColumn->title = 'Under Investigation';
                $todoColumn->board_id = $board->id;
                $todoColumn->save();
                $todoColumn = new Column;
                $todoColumn->title = 'Delayed';
                $todoColumn->board_id = $board->id;
                $todoColumn->save();
                $todoColumn = new Column;
                $todoColumn->title = 'Completed';
                $todoColumn->board_id = $board->id;
                $todoColumn->save();

                activity('create')
                    ->performedOn($board) // Entry add in table. model name(subject_type) & id(subject_id)
                    ->causedBy(Auth::user()) //causer_id = admin id, causer type = admin model
                    ->log('A New Board is Created By ' . Auth::user()->name);
                return ['status' => 'success', 'message' => 'success', 'data' => $board];
            }
            return ['status' => 'error', 'message' => 'Title Not Found'];
        } catch (\Exception $e) {
            logger($e);
            return ['status' => 'error', 'message' => 'Error Occurred'];
        }
    }
    public function updateColumn(Request $request)
    {
        try 
        {
            $column = Column::find($request->column_id);
            if ($column) {
                $card = Card::find($request->task_id);
                if ($card) {
                    $card->column_id = $column->id;
                    $card->save();
                    return ['status' => 'success', 'message' => 'success'];
                }
                return ['status' => 'error', 'message' => 'Task not found'];
            }
            return ['status' => 'error', 'message' => 'Column not found'];

        } catch (\Exception $e) {
            logger($e);
            return ['status' => 'error'];
        }
    }

}
