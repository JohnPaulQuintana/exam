<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    //update delete
    public function profile(Request $request){
        // dd($request);
        try {
            $validateCredentials = Validator::make($request->all(),[
                'email'=>'required|email',
                'password'=>'required|min:6',
            ]);
    
            if($validateCredentials->fails()){
                return response()->json([
                    'status'=>false,
                    'message'=>'Validation failed!',
                    'errors'=>$validateCredentials->errors(),
                ], 401);
            }
    
            //passed
            User::find($request->id)->update([
                'email'=>$request->email,
                'password'=>$request->password,
            ]);
    
            return response()->json([
                'status'=>true,
                'message'=>"Profile Information updated successfully",
                'profile'=>User::find($request->id),
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'=>false,
                'message'=>$th->getMessage(),
            ], 401);
        }
    }
}
