<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class JWTAuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        //$token = Auth::login($user);

        return response()->json([
            'success' => 'Registration successful'
            // 'access_token' => $token,
            // 'token_type' => 'bearer',
            // 'expires_in' => Auth::factory()->getTTL() * 60
        ], 201);
    }

    /**
     * Login user and return a token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }

    public function refresh()
    {
        try {
            $newToken = Auth::refresh(true, true);
            return response()->json([
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => Auth::factory()->getTTL() * 60
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }

    /**
     * Logout user.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        Auth::logout(true);

        return response()->json(['message' => 'Successfully logged out']);
    }
}