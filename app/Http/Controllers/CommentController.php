<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Card;
use App\Models\Comment;
use Auth;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function storeComment(Request $request)
    {
        try 
        {
            

            $card = Card::find($request->card_id);
            if( $card )
            {
                $comment = new Comment;
                $comment->user_id = Auth::id();
                $comment->card_id = $request->card_id;
                $comment->comment = $request->comment;
                $comment->save();

                $comments = $card->comments()->with("user")->get();                
                return ['status' => 'success','message'=> 'success','data'=>$comments];
            }
            // return back();
            
        } catch (\Exception $e) {
            logger($e);
            return ['status' => 'error','message'=> 'error Occurred'];
        }
        
    }
}
