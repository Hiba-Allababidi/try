<?php

namespace App\Http\Controllers;

use App\Models\Manager;
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
            'email' => 'required|string|unique:users|max:30',
            'password' => 'required|string|confirmed|min:8'
        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        $manager = Manager::create([
            'manager_name'=>$request->name,
            'manager_email'=>$request->email,
            'manager_password'=>bcrypt($request->password)
        ]);

        return $this->send_verification_code($manager);
    }


    public function send_verification_code($manager)
    {
        $manager_id = $manager->id;
        $code = mt_rand(100000, 999999);
        $details = [
            'title' => 'Hello',
            'message' => 'Your Verification code',
            'code' => $code
        ];

        try {
            Notification::send($manager, new EmailVerification($details));
        } catch (Swift_SwiftException $exception) {
            Manager::find($manager_id)->delete();
            return response()->json([
                'message' => 'email does not exist'
            ], 400);
        }

        User_verification::create([
            'user_id' => $manager_id,
            'code' => $code
        ]);

        return response()->json([
            'message' => 'success',
            'user' => $manager
        ], 201);
    }


    public function Resend_verification_code($manager)
    {
        $manager_id = $manager->id;
        $code = mt_rand(100000, 999999);
        $details = [
            'title' => 'Hello',
            'message' => 'Your Verification code',
            'code' => $code
        ];


        Notification::send($manager, new EmailVerification($details));
        User_verification::find($manager_id)->update(['code' => $code]);

        return response()->json([
            'message' => 'success',
        ], 201);
    }



    public function verify_user(Request $request)
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
        $id=$user_verification->user_id;
        $user_verification->delete();
        $user = Manager::find($id)->makeVisible(['password']);
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
