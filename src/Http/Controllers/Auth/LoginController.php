<?php

namespace Mubeen\LaravelUserCrud\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        if ($this->isApiInterface()) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed for API interface'
            ], 405);
        }
        
        return view('laravel-user-crud::auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        if ($this->isApiInterface()) {
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
            if (!Auth::attempt($credentials, $request->boolean('remember'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }
            
            $user = $request->user();
            
            // Get token based on auth provider
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
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ]);
        }
        
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        if ($this->isApiInterface()) {
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
        
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

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