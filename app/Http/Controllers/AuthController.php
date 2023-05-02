<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Social;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function Register(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        if($validator->fails()){
            return response()->json([
                "validation_errors" => $validator->messages(),
            ],422);
        }else{

            $request['password'] = Hash::make($request['password']);
            $user = User::create(request()->all());

            return response()->json([
                'status' => 200,
                "username" => $user->name,
                "user" => $user,
                "message" => "Sign up Successfully",
            ]);
        }

    }


    public function Login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|max:191',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                "validation_errors" => $validator->messages(),
            ]);
        }else{

            $user = User::where('email', $request['email'])->first();

            if(!$user || !Hash::check($request['password'], $user->password)){
                return response()->json([
                    "message" => "Invalid Credentials"
                ],401);
            } else {

                $token = $user->createToken('token')->plainTextToken;

                return response()->json([
                    "status" => 200,
                    "user" => $user ,
                    "username" => $user->name,
                    "token" => $token,
                    "message" => "Sign in Successfully",
                ]);
            }
        }

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            "message" => "success logout"
        ],200);
    }



}
