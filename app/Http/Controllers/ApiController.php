<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\User;

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
}
