<?php

namespace Mubeen\LaravelUserCrud\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mubeen\LaravelUserCrud\Models\User;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        if ($this->isApiInterface()) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed for API interface'
            ], 405);
        }
        
        return view('laravel-user-crud::auth.register');
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];
        
        if ($this->isApiInterface()) {
            $validator = Validator::make($request->all(), $rules);
            
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
            
            // Generate token based on auth provider
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
                    $token = null;
            }
            
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ], 201);
        }
        
        $request->validate($rules);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect('/');
    }
    
    /**
     * Check if the interface type is API.
     */
    private function isApiInterface()
    {
        return config('laravel-user-crud.interface_type', 'web') === 'api';
    }
} 