<?php

namespace App\Http\Controllers;

use App\Models\Keeper;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string',
            'password' => 'required|string|min:8'
        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        if (filter_var($request->text, FILTER_VALIDATE_EMAIL)){
            if (Keeper::where('email', '=', $request->text)->exists()){
                $keeper = Keeper::firstWhere('email', $request->text);
                return $this->KeeperLogin($keeper,$request);
            }
            if (Manager::where('email', '=', $request->text)->exists)
            {
                $manager = Manager::firstWhere('email', $request->text);
                return $this->ManagerLogin($manager,$request);
            }
        }

        if (Keeper::where('name', '=', $request->text)->exists()){
            $user = Keeper::firstWhere('name', $request->text);
            return $this->KeeperLogin($user,$request);
        }
        if (Manager::where('name', '=', $request->text)->exists()){
            $manager = Manager::firstWhere('name', $request->text);
            return $this->ManagerLogin($manager,$request);
        }

    }
    public function KeeperLogin(Keeper $keeper, Request $request)
    {
            Config::set('jwt.user', 'App\Models\User');
            Config::set('auth.providers.users.model', \App\Models\User::class);
            if ($keeper->is_activated) {
            if (Hash::check($request->password, $keeper->password)){
                $token = JWTAuth::fromUser($keeper);
                return response()->json([
                    'message' => 'success',
                    'token' => $token,
                    'is_manager'=>false
                ], 200);
            }
            return response()->json([
                'message' => 'password is not correct !',
            ], 401);
        }
        $keeper->delete();
        return response()->json([
            'message' => 'you need to register first !'
        ], 401);
    }

public function ManagerLogin(Manager $manager, Request $request)
    {
        Config::set('jwt.user', 'App\Models\Manager');
    Config::set('auth.providers.users.model', \App\Models\Manager::class);
            if ($manager->is_activated)
            if (Hash::check($request->password, $manager->password)){
                $token = JWTAuth::fromUser($manager);
                return response()->json([
                    'message' => 'success',
                    'token' => $token,
                    'is_manager'=>true
                ], 200);
            }
            return response()->json([
                'message' => 'password is not correct !',
            ], 401);
        $manager->delete();
        return response()->json([
            'message' => 'you need to register first !'
        ], 401);
    }
}
