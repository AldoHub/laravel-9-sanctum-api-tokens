<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use Illuminate\Http\Request;


//User Model
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;


class AuthController extends Controller
{

        
    public function register(Request $req){
    

        //using "confirmed" on the password needs the "password_confirmation" field on the form request/json  
        /*
         *  //---- json request sent
         * 
         * {
         *   "name": "test_user_2",
         *   "email": "tuser2@hotmail.com",
         *   "password": 123456,
         *   "password_confirmation": 123456,
         * }
        */

        //validate
        $fields = $req->validate([
            "name" => 'required',
            "email" => 'required|email|unique:users',
            "password" => 'required|confirmed'
        ]);

        //----- add the new user

        //hash the pass
        $fields['password'] = Hash::make($fields['password']); 
    
        //crete the user here
        $user = User::create($fields);
    
        //return response using HttpFoundation/Response
        return response($user, Response::HTTP_CREATED);
    }



    public function login(Request $req){
    
         /**
          *   //---- json request sent
          *    {
          *     "email": "jperez@hotmail.com",
          *     "password": 123456
          *    }
          *      
          */   


       
        $creds = $req->validate([
            'email' => ['required', 'email'],
            "password" => ['required']
        ]);
       

        if(Auth::attempt($creds)){
            
            //get the user
            $user = Auth::user();
            //gen the token
            $token = $user->createToken('token')->plainTextToken;
            $cookie = cookie('cookie_token', $token, 60*24);

            return response([
                "token" => $token, 
            ], Response::HTTP_OK)->withCookie($cookie);

            
        }else{
            return response([
                "message" => "Credentials are not valid, please try again"
            ], Response::HTTP_UNAUTHORIZED);
        }

       
        return response()->json([
            "message" => "login route works!!"
        ]);
    }


    public function userProfile(Request $req){

    /**
     *  Since this is an api, to avoid redirecting to the login page, just add the header
     *  "Accept: application/json" to the request sent
     *
     *  For the token use "Authorization: Bearer <generated_token>"
     *  
     */

        return response()->json([
            "message" => ' This is the user profile',
            "userData" => auth()->user()
        ], Response::HTTP_OK);

    }


    public function logout(){
        //forget/delete the cookie
        $cookie = Cookie::forget('cookie_token');
        
        //remove the token for the user
        auth()->user()->tokens()->delete();

        return response([
            "message" => 'Logged out successfully done',
        ], Response::HTTP_OK)->withCookie($cookie);

    }


    public function allUsers(){
        //do whatever here
    }
}
