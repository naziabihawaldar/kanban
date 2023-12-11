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
        try 
        {
            $request->validate([
                'email'     => 'required|exists:users|max:255',
                'password'  => 'required|min:6|max:255',
            ]);

            if(Auth::attempt(['email' => $request->email, 'password' => $request->password]))
            {
                $user = User::where('email',$request->email)->first();
                $token = $user->createToken("*")->plainTextToken;  
                $user->access_token = $token;
                $user->token_type = 'Bearer';
                $role_name = $user->roles->pluck('name')[0];
                $user->user_role = $role_name;
                return ['status'=>'success','message'=>'success','data' => $user];
            }
            return response()->json([
                'status' => 'error',
                'message' => "Invalid Credentials"
            ], 200);

        } catch (\Exception $e) {
            logger($e);
            return ['status' => "error",'message'=>'Error Occurred. Please try again later'];
        }
    }

    public function getProjects(Request $request)
    {
        try 
        {
            $boards = Board::orderBy('created_at', 'desc')->paginate($request->rowsPerPage);
            return ['status'=>'success','message'=>'success','data' => $boards];
        } catch (\Exception $e) {
            logger($e);
        }
    }

    public function getTasks(Request $request)
    {
        try 
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
        } catch (\Exception $e) {
            logger($e);
        }
        
    }
}
