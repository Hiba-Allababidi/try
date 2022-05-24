<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __Construct()
    {
        $this->middleware('jwt.verify')->except('login');
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string',
            'password' => 'required|string|min:8'
        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 400);
        if (filter_var($request->text, FILTER_VALIDATE_EMAIL))
            $user = User::firstWhere('email', $request->text);
        else
            $user=User::firstWhere('name',$request->text);
        if(isset($user)){
            if ($user->is_activated) {
                if (Hash::check($request->password, $user->password)){
                //if($request->password == $user->password){
                    $token = JWTAuth::fromUser($user);
                    return response()->json([
                        'message' => 'success',
                        'token' => $token
                    ], 200);
                }
                return response()->json([
                    'message' => 'password is not correct !',
                ], 401);
            }
        }
        $user->delete();
        return response()->json([
            'message' => 'you need to register first !'
        ], 401);
    }

    // public function logout()
    // {
    //     Auth::logout();
    //     return response()->json([
    //         'message' => 'success'
    //     ], 200);
    // }

    public function logout( Request $request ) {

        $token = $request->header( 'Authorization' );

        try {
            JWTAuth::parseToken()->invalidate( $token );

            return response()->json( [
                'error'   => false,
                'message' => trans( 'auth.logged_out' )
            ] );
        } catch ( TokenExpiredException $exception ) {
            return response()->json( [
                'error'   => true,
                'message' => trans( 'auth.token.expired' )

            ], 401 );
        } catch ( TokenInvalidException $exception ) {
            return response()->json( [
                'error'   => true,
                'message' => trans( 'auth.token.invalid' )
            ], 401 );

        } catch ( JWTException $exception ) {
            return response()->json( [
                'error'   => true,
                'message' => trans( 'auth.token.missing' )
            ], 500 );
        }
    }


    public function user_profile()
    {
        $user = JWTAuth::user();
        if (isset($user))
            return response()->json([
                'user' => $user
            ], 200);
        return response()->json([
            'message' => 'user not found'
        ], 404);
    }
}
