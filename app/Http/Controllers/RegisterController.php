<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\User_verification;
use App\Notifications\EmailVerification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Swift_DependencyException;
use Tymon\JWTAuth\Facades\JWTAuth;

class RegisterController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,30',
            'email' => 'required|string|email|unique:users|max:30',
            'password' => 'required|string|confirmed|min:8'
        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        $this->send_verification_code($user);

        return response()->json([
            'message' => 'success',
            'user' => $user
        ], 200);
    }


    public function send_verification_code($user)
    {
        $user_id=$user->id;
        $code = mt_rand(100000, 999999);
        $details = [
            'title' => 'Hello',
            'body' => [
                'Verification code' => $code
            ]
        ];

        try{
            Notification::send($user, new EmailVerification($details));
        }
        catch (Swift_DependencyException $exception){
            return response()->json($exception->getMessage());
        }



        User_verification::create([
            'user_id' => $user_id,
            'code' => $code
        ]);

        return response()->json([
           'message'=>'success'
        ]);
    }


    public function verify_user($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|int'
        ]);
        if ($validator->fails())
            return response()->json($validator->errors());

        $entered_code = $request->code;
        $code = DB::table('user_verifications')->where('user_id', $id)->pluck('code');
        if ($entered_code == $code[0]) {
            DB::table('users')->where('id', $id)
                ->update(['is_activated' => 1, 'email_verified_at' => Carbon::now()]);
            $user = User::find($id)->makeVisible(['password']);
            $token = JWTAuth::fromUser($user);
            return response()->json([
                'message' => 'success',
                'token' => $token
            ]);
        } else
            return response()->json([
                'message' => 'failure'
            ]);
    }
}
