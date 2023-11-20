<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Card;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use Spatie\Activitylog\Models\Activity;

class CardController extends Controller
{
    public function importBoardData(Request $request) 
    {
        if($request->hasFile('file')){
            $board_id = $request->board_id;
            $file = $request->file('file');
            if ($file === null) {
                logger("Invalid file");
            } else {
                $path = $file[0]->getRealPath();
                // $filePath = $file[0]->getPathname();
                \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\CardsImport($board_id), $path);
                $board = Board::find($board_id);

                activity('create')
                ->performedOn($board) // Entry add in table. model name(subject_type) & id(subject_id)
                ->causedBy(Auth::user()) //causer_id = admin id, causer type = admin model
                ->log('A file was imported by ' . Auth::user()->name);
                // Process the uploaded file
                return back();
            }
            
        }       
    }

    public function assignCardToUser(Request $request)
    {
        $card = Card::find($request->card_id);
        if($card)
        {
            $user = User::find($request->user_id);
            if($user)
            {
                $card->users()->sync([$request->user_id]);
                $board = Board::find($card->board_id);
                if($board)
                {
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
                ->log(Auth::user()->name.' changed the Assignee to '. $user->name);
    
                try{
                    dispatch(new \App\Jobs\SendMailNotification($mailData,'customer_send_ticket_reopen',$user->email));
                }catch(\Exception $e){
                    logger($e);
                }
            }
        }
        return back();  
    }

    public function getCardDetails(Request $request)
    {
        try 
        {
            $card = Card::find($request->card_id);
            if($card)
            {
                $card->all_comments = $card->comments;
                $activities = Activity::where('subject_type',get_class($card))->get();
                $card->activities = $activities;
                return ['status' => 'success','message' =>'success','data' => $card];
            }
        } catch (\Exception $e) {
            logger($e);
            return ['status'=> 'error','message'=> 'Error Occurred'];
        }
    }

    public function getActivities(Request $request)
    {
        try
        {
            $card = new Card; 
            $activities = Activity::with('causer')->where('subject_id',$request->card_id)->orderByDesc('created_at')->get();
            return ['status'=> 'success','message'=> 'success','data'=> $activities];
        }catch (\Exception $e) {
            logger($e);
            return ['status'=> 'error','message'=> 'Error Occurred'];
        }
    }
}
