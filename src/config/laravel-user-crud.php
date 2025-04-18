<?php

return [
    // Interface type: 'web' or 'api'
    'interface_type' => 'web',
    
    // Auth provider for API: 'sanctum', 'passport', or 'jwt'
    'auth_provider' => null,
    
    // Routes prefix for all the package routes
    'route_prefix' => 'admin',
    
    // Middleware to apply to the routes
    'middleware' => ['web', 'auth'],
    
    // Admin routes middleware 
    'admin_middleware' => ['web', 'auth'],
    
    // View layout to extend (for web interface)
    'layout' => 'layouts.app',
    
    // Additional user fields
    'additional_user_fields' => [],
    
    // API response settings
    'api_pagination_limit' => 15,
    
    // Token settings (for Sanctum/Passport)
    'token_name' => 'User Management',
    'token_expiration_days' => 30,
]; 