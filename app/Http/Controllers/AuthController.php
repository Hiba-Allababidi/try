<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __Construct()
    {
        $this->middleware('auth:api')->except('login');
    }


    public function login(Request $request){
        $validator=Validator::make($request->all(),[
           'email'=>'required|string|email',
           'password'=>'required|string|min:8'
        ]);
        if($validator->fails())
            return response()->json($validator->errors());
        $user=User::where('email',$request->email)->first();
        if(isset($user))
        {//tcnwazdiujnyefry
            if($user->is_activated){
                if($user->password == $request->password)
                {
                    $token=JWTAuth::fromUser($user);
                    return response()->json([
                        'message'=>'success',
                        'token'=>$token
                    ]);
                }
                return response()->json([
                    'message'=>'failure ! password is not correct',
                ]);
            }
            return response()->json([
                'message'=>'failure ! you need to verify your account first',
            ]);
        }
        return response()->json([
            'message'=>'failure ! User does not exist',
        ]);
    }


    public function user_profile(){
        $user=JWTAuth::user();
        return response()->json([
           'user'=>$user
        ]);
    }
}
