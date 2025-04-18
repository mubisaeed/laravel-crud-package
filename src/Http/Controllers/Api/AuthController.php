<?php

namespace Mubeen\LaravelUserCrud\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mubeen\LaravelUserCrud\Models\User;

class AuthController extends Controller
{
    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');
        $authProvider = config('laravel-user-crud.auth_provider', 'sanctum');
        
        switch ($authProvider) {
            case 'sanctum':
                return $this->loginWithSanctum($credentials, $request);
                
            case 'passport':
                return $this->loginWithPassport($credentials);
                
            case 'jwt':
                return $this->loginWithJWT($credentials);
                
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid authentication provider'
                ], 500);
        }
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $authProvider = config('laravel-user-crud.auth_provider', 'sanctum');
        
        switch ($authProvider) {
            case 'sanctum':
                $token = $user->createToken(config('laravel-user-crud.token_name', 'API Token'))->plainTextToken;
                break;
                
            case 'passport':
                $token = $user->createToken(config('laravel-user-crud.token_name', 'API Token'))->accessToken;
                break;
                
            case 'jwt':
                $token = auth('api')->login($user);
                break;
                
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid authentication provider'
                ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }

    /**
     * Get authenticated user profile.
     */
    public function profile()
    {
        $user = Auth::user();
        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $userData = [];
        
        if ($request->has('name')) {
            $userData['name'] = $request->name;
        }
        
        if ($request->has('email')) {
            $userData['email'] = $request->email;
        }
        
        if ($request->has('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout(Request $request)
    {
        $authProvider = config('laravel-user-crud.auth_provider', 'sanctum');
        
        switch ($authProvider) {
            case 'sanctum':
                $request->user()->currentAccessToken()->delete();
                break;
                
            case 'passport':
                $request->user()->token()->revoke();
                break;
                
            case 'jwt':
                auth('api')->logout();
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Login with Laravel Sanctum.
     */
    private function loginWithSanctum($credentials, $request)
    {
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = $request->user();
        $token = $user->createToken(config('laravel-user-crud.token_name', 'API Token'))->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * Login with Laravel Passport.
     */
    private function loginWithPassport($credentials)
    {
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken(config('laravel-user-crud.token_name', 'API Token'))->accessToken;

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * Login with JWT.
     */
    private function loginWithJWT($credentials)
    {
        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => auth('api')->user(),
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
} 