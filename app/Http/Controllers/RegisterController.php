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
use Swift_SwiftException;
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

        return $this->send_verification_code($user);
    }


    public function send_verification_code($user)
    {
        $user_id = $user->id;
        $code = mt_rand(100000, 999999);
        $details = [
            'title' => 'Hello',
            'message' => 'Your Verification code',
            'code' => $code
        ];

        try {
            Notification::send($user, new EmailVerification($details));
        } catch (Swift_SwiftException $exception) {
            $user->delete();
            return response()->json([
                'message' => 'email does not exist'
            ], 400);
        }

        User_verification::create([
            'user_id' => $user_id,
            'code' => $code
        ]);

        return response()->json([
            'message' => 'success',
            'user' => $user
        ], 201);
    }


    public function verify_user($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|int|exists:user_verifications'
        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 400);
        $user_verification = User_verification::firstWhere('code', $request->code);
        if ($user_verification->isExpire())
            return response()->json([
                'message' => 'this verification code has expired'
            ], 401);
        $user_verification->delete();
        $user = User::find($id)->makeVisible(['password']);
        DB::table('users')->update(['is_activated' => 1, 'email_verified_at' => Carbon::now()]);
        //$user->update(['is_activated' => 1, 'email_verified_at' => Carbon::now()]);
        $user->makeVisible('password');
        $token = JWTAuth::fromUser($user);
        return response()->json([
            'message' => 'success',
            'token' => $token
        ], 200);
    }
}
