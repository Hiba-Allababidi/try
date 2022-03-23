<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\User_verification;
use App\Notifications\EmailVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __Construct()
    {
        $this->middleware('auth:api')->except('register', 'verify_user');
    }


    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,50',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed|min:8'
        ]);
        if ($validator->fails())
            return response()->json($validator->errors()->toJson(), 400);

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        if ($this->send_verification_code($user)) {
            return response()->json([
                'message' => 'success',
                'user' => $user
            ], 200);
        } else
            return response()->json([
                'message' => 'failure'
            ]);
    }


    public function send_verification_code(User $user)
    {
        $user_id = $user->id;
        $code = Str::random(6);
        User_verification::create([
            'user_id' => $user_id,
            'code' => $code
        ]);

        $details = [
            'title' => 'hello',
            'body' => $code
        ];

        Notification::send($user, new EmailVerification($details));

        return true;
    }


    public function verify_user($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|min:6|max:6'
        ]);

        $entered_code = $request->code;
        $code = DB::table('user_verifications')->where('user_id', $id)->pluck('code');
        if ($entered_code == $code[0]) {
            DB::table('users')->where('id', $id)->update(['is_activated' => 1]);
            $user = User::find($id)->makeVisible(['password']);
            $token = JWTAuth::fromUser($user);
            return response()->json([
                'message' => 'success',
                'token' => $token
            ]);
        }
        else
            return response()->json([
               'message'=>'failure'
            ]);
    }
}
