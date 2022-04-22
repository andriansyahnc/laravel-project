<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:users,name|max:20',
            'email' => 'required|email|unique:users,email|max:50',
            'password' => 'required|min:8|max:12',
            'confirm_password' => 'required|same:password',
          ]);
      
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "error" => $validator->errors(),
            ], 422);
        }
        $input = $request->only('name', 'email', 'password', 'role_name');
        return response()->json([
            "status" => true,
            "data" => $input,
            "role" => $role_name,
        ], 200);
    }
}
