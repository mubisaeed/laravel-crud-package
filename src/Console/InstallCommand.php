<?php

namespace Mubeen\LaravelUserCrud\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'laravel-user-crud:install';
    protected $description = 'Install and configure the Laravel User CRUD package';

    public function handle()
    {
        $this->info('Installing Laravel User CRUD package...');

        // Ask for interface type
        $interfaceType = $this->choice(
            'What type of interface do you want to implement?',
            ['web', 'api'],
            0
        );
        
        // If API, ask for authentication provider
        $authProvider = null;
        if ($interfaceType === 'api') {
            $authProvider = $this->choice(
                'Which authentication provider would you like to use?',
                ['sanctum', 'passport', 'jwt'],
                0
            );
            
            // Check if the selected provider is installed
            $this->installDependencyIfNeeded($authProvider);
        }
        
        // Create config file
        $this->createConfig($interfaceType, $authProvider);
        
        // Publish views and migrations
        $this->info('Publishing resources...');
        $this->call('vendor:publish', [
            '--provider' => 'Mubeen\LaravelUserCrud\LaravelUserCrudServiceProvider',
            '--tag' => 'laravel-user-crud'
        ]);
        
        if ($interfaceType === 'api' && $authProvider === 'passport' && class_exists('\\Laravel\\Passport\\Passport')) {
            $this->info('Installing Laravel Passport keys...');
            $this->call('passport:install');
        }
        
        $this->info('Laravel User CRUD package has been installed successfully!');
        
        // Output next steps
        $this->outputNextSteps($interfaceType, $authProvider);
    }
    
    private function installDependencyIfNeeded($authProvider)
    {
        switch ($authProvider) {
            case 'sanctum':
                if (!class_exists(\Laravel\Sanctum\Sanctum::class)) {
                    if ($this->confirm('Laravel Sanctum is not installed. Would you like to install it now?', true)) {
                        $this->info('Installing Laravel Sanctum...');
                        if ($this->runCommand('composer require laravel/sanctum')) {
                            $this->call('vendor:publish', [
                                '--provider' => 'Laravel\Sanctum\SanctumServiceProvider'
                            ]);
                            $this->call('migrate');
                            $this->info('Laravel Sanctum installed successfully.');
                        } else {
                            $this->error('Failed to install Laravel Sanctum.');
                            $this->info('Please run these commands manually:');
                            $this->info('1. composer require laravel/sanctum');
                            $this->info('2. php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"');
                            $this->info('3. php artisan migrate');
                            
                            if (!$this->confirm('Do you want to continue with the setup?', false)) {
                                $this->info('Installation aborted.');
                                exit(1);
                            }
                        }
                    }
                }
                break;
                
            case 'passport':
                if (!class_exists(\Laravel\Passport\Passport::class)) {
                    if ($this->confirm('Laravel Passport is not installed. Would you like to install it now?', true)) {
                        $this->info('Installing Laravel Passport...');
                        if ($this->runCommand('composer require laravel/passport')) {
                            $this->info('Laravel Passport installed successfully.');
                        } else {
                            $this->error('Failed to install Laravel Passport.');
                            $this->info('Please run this command manually: composer require laravel/passport');
                            
                            if (!$this->confirm('Do you want to continue with the setup?', false)) {
                                $this->info('Installation aborted.');
                                exit(1);
                            }
                        }
                    }
                }
                break;
                
            case 'jwt':
                if (!class_exists(\Tymon\JWTAuth\JWTAuth::class)) {
                    if ($this->confirm('JWT Auth is not installed. Would you like to install it now?', true)) {
                        $this->info('Installing JWT Auth...');
                        if ($this->runCommand('composer require tymon/jwt-auth')) {
                            $this->call('vendor:publish', [
                                '--provider' => 'Tymon\JWTAuth\Providers\LaravelServiceProvider'
                            ]);
                            $this->call('jwt:secret');
                            $this->info('JWT Auth installed successfully.');
                        } else {
                            $this->error('Failed to install JWT Auth.');
                            $this->info('Please run these commands manually:');
                            $this->info('1. composer require tymon/jwt-auth');
                            $this->info('2. php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"');
                            $this->info('3. php artisan jwt:secret');
                            
                            if (!$this->confirm('Do you want to continue with the setup?', false)) {
                                $this->info('Installation aborted.');
                                exit(1);
                            }
                        }
                    }
                }
                break;
        }
    }
    
    /**
     * Run a shell command.
     * 
     * @param string $command
     * @return bool
     */
    private function runCommand($command)
    {
        $this->info("Running: $command");
        
        $output = null;
        $returnCode = null;
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        foreach ($output as $line) {
            $this->line($line);
        }
        
        return $returnCode === 0;
    }
    
    private function createConfig($interfaceType, $authProvider)
    {
        $configPath = config_path('laravel-user-crud.php');
        
        $config = [
            'interface_type' => $interfaceType,
            'auth_provider' => $authProvider,
            'route_prefix' => $interfaceType === 'web' ? 'admin' : 'api',
            'middleware' => $interfaceType === 'web' 
                ? ['web', 'auth'] 
                : ['api', $authProvider === 'sanctum' ? 'auth:sanctum' : ($authProvider === 'passport' ? 'auth:api' : 'auth:api')],
            'admin_middleware' => $interfaceType === 'web' 
                ? ['web', 'auth'] 
                : ['api', $authProvider === 'sanctum' ? 'auth:sanctum' : ($authProvider === 'passport' ? 'auth:api' : 'auth:api')],
            'layout' => 'layouts.app',
            'additional_user_fields' => [],
            'api_pagination_limit' => 15,
            'token_name' => 'User Management',
            'token_expiration_days' => 30,
        ];
        
        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        $content = str_replace("array (", "[", $content);
        $content = str_replace(")", "]", $content);
        $content = str_replace("=> \n  ", "=> ", $content);
        
        File::put($configPath, $content);
        
        $this->info("Configuration file created at: $configPath");
    }
    
    private function outputNextSteps($interfaceType, $authProvider)
    {
        $this->info('');
        $this->info('Next steps:');
        
        if ($interfaceType === 'web') {
            $this->info('1. Run migrations: php artisan migrate');
            $this->info('2. Access user management at: /admin/users');
        } else {
            $this->info('1. Run migrations: php artisan migrate');
            
            switch ($authProvider) {
                case 'sanctum':
                    $this->info('2. Make sure Sanctum is configured in your app/Http/Kernel.php');
                    $this->info('   Add \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class to your api middleware group');
                    break;
                    
                case 'passport':
                    $this->info('2. Add Laravel\Passport\HasApiTokens trait to your User model');
                    $this->info('3. Call Passport::routes() in your AuthServiceProvider');
                    break;
                    
                case 'jwt':
                    $this->info('2. Add Tymon\JWTAuth\Contracts\JWTSubject interface to your User model');
                    $this->info('3. Implement getJWTIdentifier() and getJWTCustomClaims() methods');
                    break;
            }
            
            $this->info('4. API endpoints available at: /api/users');
        }
    }
} 