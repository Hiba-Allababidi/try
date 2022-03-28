<?php

namespace App\Http\Controllers;

use App\Models\ResetCodePassword;
use App\Models\User;
use App\Notifications\EmailVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ResetPasswordController extends Controller
{

    public function forgot_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users'
        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        // ResetCodePassword::where('email', $request->email)->delete();

        $user = User::firstWhere('email', $request->email);

        $this->send_reset_password_code($user);

        return response()->json([
            'message' => 'success',
            'user' => $user
        ], 200);
    }

    public function send_reset_password_code($user)
    {
        $code = mt_rand(100000, 999999);
        $details = [
            'title' => 'Hello',
            'message' => 'Your password reset code',
            'code' => $code
        ];


        Notification::send($user, new EmailVerification($details));

        ResetCodePassword::create([
            'email' => $user->email,
            'code' => $code
        ]);
    }

    public function check_reset_code(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|int|exists:reset_code_passwords'
        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        $passwordReset = ResetCodePassword::firstWhere('code', $request->code);

        if ($passwordReset->isExpire())
            return response()->json([
                'message' => 'password reset code is expired'
            ], 401);

        return response()->json([
            'message' => 'success'
        ], 200);
    }

    public function reset_password(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'code' => 'required|string|exists:reset_code_passwords',
            'password' => 'required|string|confirmed|min:8'
        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        $passwordReset = ResetCodePassword::firstWhere('code', $request->code);
        $user = User::firstwhere('email', $passwordReset->email);
        $user->update((['password' => bcrypt($request->password)]));
        $passwordReset->delete();
        $user = $user->makeVisible('password');
        $token = JWTAuth::fromUser($user);
        return response()->json([
            'message' => 'success',
            'token' => $token
        ], 200);
    }
}
