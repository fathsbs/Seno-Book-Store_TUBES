<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Laravel\Fortify\Rules\Password;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //api register
    public function register(Request$request)
    {
        try {
            $request->validate([
                'name'=>['required','string','max:255'],
                'phone_number'=>['nullable','string','max:255'],
                'username'=>['required','string','max:255','unique:users'],
                'email'=>['required','string','email','max:255','unique:users'],
                'password'=>['required','string',new Password],
            ]);
            
            //register user
            User::create([
                'name'=>$request->name,
                'username'=>$request->username,
                'email'=>$request->email,
                'phone_number'=>$request->phone_number,
                'password'=> Hash::make($request->password),
            ]);

            //baca data sebelumnya
            $user = User::where('email',$request->email)->first();

            //buat token result
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            //tampil
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' =>'Bearer',
                'user' =>$user
            ],'User Registered');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ],'Authentication Failed',500);
        }
    }

    //api login
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email'=>'email|required',
                'password'=>'required'
            ]);

            $credentials =  request(['email','password']);
            //jika password dan email tidak benar
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message'=>'Unauthorized'
                ],'Authentication Failed',500);
            }

            //email benar
            $user = User::where('email',$request->email)->first();

            //cek jika password belum sesuai
            if (!Hash::check($request->password, $user->password,[])) {
                throw new \Exception('Invalid Credentials');
            }

            //jika password sudah sesuai
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            //menghasilkan token akses
            return ResponseFormatter::success([
                'access_token'=>$tokenResult,
                'token_type'=>'Bearer',
                'user'=>$user
            ],'Authenticated');

        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message'=>'Something went wrong',
                'error'=>$error
            ],'Authentication Failed',500);
        }
    }

    //api user
    public function fetch(Request $request){
        return ResponseFormatter::success($request->user(),'Data Profil User Berhasil Diambil');
    }

    //api edit profil
    public function updateProfile(Request $request)
    {
        $data = $request->all();

        $user = Auth::user();
        $user-> update($data);

        return ResponseFormatter::success($user, 'Profile Updated');
    }

    //api logout
    public function logout(Request $request)
    {        
        $token = $request -> user() -> currentAccessToken() -> delete();
        
        return ResponseFormatter::success($token,'Token Revoked');
    }
}
