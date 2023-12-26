<?php

namespace App\Http\Controllers;

use App\Http\Resources\BoardResource;
use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Models\Import;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function index(Request $request)
    {
        $boards = Board::orderBy('created_at', 'desc')->get();
        return inertia("BoardList", compact("boards"));
    }
    public function show(Request $request, Board $board)
    {
         // Case when no board is passed over
         if (!$board->exists) {
            $board = $request->user()->boards()->first();
        }

        // eager load columns with their cards
        $board?->loadMissing(['columns.cards','columns.cards.users','assignees','columns.cards.comments','columns.cards.comments.user','imports']);

        return inertia('Kanban', [
            'board' => $board ? BoardResource::make($board) : null,
        ]);
    }
    public function getProjectsList(Request $request)
    {
        try {
            $boards = Board::select('id','title')->orderBy('created_at', 'desc')->get();
            return ['status' => 'success', 'message' => 'success', 'data' => $boards];
        } catch (\Exception $e) {
            logger($e);
            return ['status' => 'error', 'message' => 'Error Occurred'];
        }
    }
    public function getDetailsByFilter(Request $request) 
    {
        $columns = Column::where('board_id', $request->board_id)->get();
        foreach ($columns as $column)
        {
            $cards = $column->cards()->with('users');
            
            if($request->has('searchtxt') && $request->searchtxt != '')
            {
                $cards = $cards->where('content', 'like', '%' . $request->searchtxt . '%');
            }

            if($request->has('severity') && $request->severity != '')
            {
                $cards = $cards->where('severity',$request->severity);
            }
            if($request->has('assignee') && $request->assignee != '')
            {
                $cards = $cards->whereHas('users', function ($q) use ($request, $column) {
                    $q->where('users.id',  $request->assignee );  
                });
            }
            if($request->has('assigneeUsers') && $request->assigneeUsers != '')
            {
                $cards = $cards->whereHas('users', function ($q) use ($request, $column) {
                    $q->whereIn('users.id',  $request->assigneeUsers );  
                });
            }
            if($request->has('imports') && $request->imports != '')
            {
                $cards = $cards->whereHas('import', function ($q) use ($request) {
                    $q->where('imports.name',  $request->imports);  
                });
            }
            if($request->has('domain') && $request->domain != '')
            {
                $cards = $cards->where('severity',$request->severity);
            }
            if($request->has('scan_date') && $request->scan_date != '')
            {
                $cards = $cards->whereDate('scan_date_time',$request->scan_date);
            }
            if($request->has('os_type') && $request->os_type != '')
            {
                $cards = $cards->where('os_type',$request->os_type);
            }
            if($request->has('os_version') && $request->os_version != '')
            {
                $cards = $cards->where('os_version',$request->os_version);
            }
            if($request->has('business_unit') && $request->business_unit != '')
            {
                $cards = $cards->where('business_unit',$request->business_unit);
            }
            if($request->has('ip_and_vuln_id') && $request->business_unit != '')
            {
                $cards = $cards->where('ip_and_vuln_id',$request->ip_and_vuln_id);
            }
            
            $cards = $cards->get();
            $column->cards = $cards;
        }
        return ['status' => 'success','data' => $columns];
    }

    public function store(Request $request)
    {
        $board = new Board;
        $board->title = $request->title;
        $board->user_id = Auth::id();
        $board->save();

        $todoColumn =  new Column;
        $todoColumn->title = 'To Do';
        $todoColumn->board_id = $board->id;
        $todoColumn->save();
        $todoColumn =  new Column;
        $todoColumn->title = 'Under Investigation';
        $todoColumn->board_id = $board->id;
        $todoColumn->save();
        $todoColumn =  new Column;
        $todoColumn->title = 'Delayed';
        $todoColumn->board_id = $board->id;
        $todoColumn->save();
        $todoColumn =  new Column;
        $todoColumn->title = 'Completed';
        $todoColumn->board_id = $board->id;
        $todoColumn->save();

        activity('create')
        ->performedOn($board) // Entry add in table. model name(subject_type) & id(subject_id)
        ->causedBy(Auth::user()) //causer_id = admin id, causer type = admin model
        ->log('A New Board is Created By ' . Auth::user()->name);
        
    }

    public function updateCardDetails(Request $request)
    {
        $card = Card::find($request->card_id);
        if($card)
        {
            $log_txt = '';
            if($request->type == 'start_date')
            {
                $start_date = \Carbon\Carbon::parse($request->date);
                $newDate = $start_date->format('Y-m-d H:i:s');
                $card->start_date = $newDate;
                $log_data = \Carbon\Carbon::parse($request->date)->format('M d, Y');
                $log_txt = Auth::user()->name.' updated the Start Date to '.$log_data;
            }
            if($request->type == 'end_date')
            {
                $start_date = \Carbon\Carbon::parse($request->date);
                $newDate = $start_date->format('Y-m-d H:i:s');
                $card->end_date = $newDate;
                $log_data = \Carbon\Carbon::parse($request->date)->format('M d, Y');
                $log_txt = Auth::user()->name.' updated the End Date to '.$log_data;
            }
            if($request->type == 'due_date')
            {
                $start_date = \Carbon\Carbon::parse($request->date);
                $newDate = $start_date->format('Y-m-d H:i:s');
                $card->due_date = $newDate;
                $log_data = \Carbon\Carbon::parse($request->date)->format('M d, Y');
                $log_txt = Auth::user()->name.' updated the Due Date to '.$log_data;
            }

            
            $card->save();
            activity('assignee')
                ->performedOn($card) // Entry add in table. model name(subject_type) & id(subject_id)
                ->causedBy(Auth::user()) //causer_id = admin id, causer type = admin model
                ->log($log_txt);
            return ['status' => 'success'];

        }
        return ['status' => 'error'];
    }

    public function updateBoardUsers(Request $request)
    {
        $board = Board::find($request->board_id);
        if($board)
        {
            $users = User::select('id')->whereIn('id', $request->assignees)->get();
            if(count($users) > 0)
            {
                $board->assignees()->sync($users,['assigned_by' => Auth::id()]);
                $board->assignees()->updateExistingPivot($users, ['assigned_by' => Auth::id()]);
                return ['status'=> 'success'];
            }
        }
        return ['status'=> 'error occurred'];
    }
    public function getColumns(Request $request)
    {
        try 
        {
            $board = Board::find($request->board_id);
            if($board)
            { 
                $columns = $board->columns()->with('cards','cards.users','cards.comments','cards.comments.user')->get();
                return ['status' => 'success','data'=> $columns];
            }
        } catch (\Exception $e) {
            logger($e);
            return ['status'=> 'error'];
        }
    }

    public function deleteBulkFile(Request $request)
    {
        try 
        {
            try 
            {
                $import_file = Import::find($request->fileId);
                if($import_file)
                {
                    $cards = Card::where('import_id',$import_file->id)->exists();
                    if(Card::where('import_id',$import_file->id)->exists())
                    {
                        Card::where('import_id',$import_file->id)->delete();
                          
                    }
                    Import::where('id',$import_file->id)->delete(); 
                    return ['status' => 'success'];
                }   
            } catch (\Exception $e) {
                logger($e);
                return ['status'=> 'error'];
            }
        } catch (\Exception $e) {
            logger($e);
        }
    }

    public function getFileDetails(Request $request)
    {
        try 
        {
            $file = Import::where('board_id',$request->board_id)->where('name',$request->fileName)->first();
            return ['status' => 'success','message'=>'success','data'=>$file];
        } catch (\Exception $e) {
            logger($e);
            return ['status'=> 'error'];
        }   
    }

    public function updateColumn(Request $request)
    {
        try 
        {
            $column = Column::find($request->column_id);
            if($column)
            {
                $card = Card::find($request->card_id);
                if($card)
                {
                    $card->column_id = $column->id;
                    $card->save();
                    return ['status' => 'success','message'=>'success'];
                }
                return ['status'=> 'error','message'=>'card not found'];
            }
            return ['status'=> 'error','message'=>'column not found'];
            
        } catch (\Exception $e) {
            logger($e);
            return ['status'=> 'error'];
        }   
    }
}
