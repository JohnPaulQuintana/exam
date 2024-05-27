<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //crete account
    public function register(Request $request)
    {
        // dd($request);
        // check if the request is post or get method
        try {
            if ($request->isMethod('post')) {

                // dd($request);
                // validated the input request
                $validateRequest = Validator::make(
                    $request->all(),
                    [
                        'name' => 'required',
                        'email' => 'required|string|email',
                        'password' => 'required|confirmed|min:6',
                    ]
                );

                //if validation fails
                if ($validateRequest->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => "Validation Failed!",
                        'errors' => $validateRequest->errors(),
                    ], 401);
                }

                // success validation
                $user = \App\Models\User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => $request->password,
                ]);

                return response()->json([
                    "status" => true,
                    "message" => "User created successfully!",
                    "token" => $user->createToken("API TOKEN")->plainTextToken,
                ], 200);
            } else {
                dd("this is the get request, return html or view");
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 401);
        }
    }

    // login account
    public function login(Request $request)
    {
        try {
            
            if($request->isMethod("post")){
                // validate the user creadentials
                $validateRequest = Validator::make($request->all(),
                    [
                        'email'=>'required|email',
                        'password'=>'required|min:6'
                    ]
                );
                // error aquired return false with response message and errors
                if($validateRequest->fails()){
                    return response()->json([
                        'status'=>false,
                        'message'=>"Validation failed!",
                        'errors'=>$validateRequest->errors(),
                    ], 401);
                }

                //if not authenticated or the attempt email and password is not present
                if(!Auth::attempt($request->only(['email','password']))){
                    return response()->json([
                        'status'=>false,
                        'message'=>"The email and password does not match with the records!",
                    ], 401);
                }

                // success attempt query the database getting the first records matched with this email
                $user = User::where('email', $request->email)->first();
                
                //now we can return the status 200 with user credentials
                return response()->json([
                    'status'=>true,
                    'message'=>"User successfully authenticated!",
                    "token" => $user->createToken("API TOKEN")->plainTextToken,
                ], 200);

            }else{
                dd('return the login sendtion to render');
            }

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 401);
        }
    }
}
