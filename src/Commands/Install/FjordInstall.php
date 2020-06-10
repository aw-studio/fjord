<?php

namespace Fjord\Commands\Install;

use Fjord\User\Models\FjordUser;
use Fjord\Commands\Traits\RolesAndPermissions;
use Fjord\Crud\Config\CrudConfig;
use FjordApp\Controllers\User\ProfileSettingsController;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class FjordInstall extends Command
{
    use RolesAndPermissions,
        InstallVendorConfigs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fjord:install 
                            {--migrations= : Whether to publish migrations or not }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This wizard will take you through the installation process';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Filesystem $filesystem)
    {
        // http://patorjk.com/software/taag/#p=display&h=1&v=0&f=Slant&t=Fjord%20Install
        $this->info("    ______ _                   __   ____              __          __ __");
        $this->info("   / ____/(_)____   _____ ____/ /  /  _/____   _____ / /_ ____ _ / // /");
        $this->info("  / /_   / // __ \ / ___// __  /   / / / __ \ / ___// __// __ `// // / ");
        $this->info(" / __/  / // /_/ // /   / /_/ /  _/ / / / / /(__  )/ /_ / /_/ // // /  ");
        $this->info("/_/  __/ / \____//_/    \__,_/  /___//_/ /_//____/ \__/ \__,_//_//_/   ");
        $this->info("    /___/                                                              ");

        $this->info("\n----- start -----\n");
        $this->vendorConfigs();
        $this->call('fjord:guard');

        $this->handleFjordPublishable();

        $this->createDefaultRoles();
        $this->createDefaultPermissions();

        $this->publishFjord();
        $this->makeModelDirs();

        $this->defaultUser();

        $this->info("\n----- finished -----\n");

        $this->info('installation complete - run php artisan fjord:admin to create an admin user');
    }

    public function defaultUser()
    {
        if (config('app.env') == 'production') {
            return;
        }
        $user = FjordUser::firstOrCreate([
            'username' => 'admin',
            'email' => 'admin@admin.com',
        ], [
            'first_name' => 'admin',
            'last_name' => '',
            'password' => bcrypt('secret')
        ]);

        $user->assignRole('admin');

        $this->info('created default admin (email: admin@admin.com, password: secret)');
    }

    /**
     * Make directory if not exists.
     *
     * @param string $path
     * @return void
     */
    private function makeDirectory(string $path)
    {
        if (File::exists($path)) {
            return;
        }

        File::makeDirectory($path);
    }

    public function migrations()
    {
        $migrations = $this->option('migrations');
        return $migrations !== 'false' && $migrations !== false;
    }

    /**
     * Publish Fjord config and assets
     *
     * @return void
     */
    private function handleFjordPublishable()
    {
        $this->info('publishing fjord config & migrations');
        if ($this->migrations()) {
            $this->callSilent('vendor:publish', [
                '--provider' => "Fjord\FjordServiceProvider",
                '--tag' => "migrations"
            ]);
        }

        $this->callSilent('vendor:publish', [
            '--provider' => "Fjord\FjordServiceProvider",
            '--tag' => "config"
        ]);

        // migrate tables
        if (\App::environment(['local', 'staging'])) {
            $this->callSilent('migrate');
        } else {
            $this->call('migrate');
        }
    }

    /**
     * Publish Fjord resources.
     *
     * @return void
     */
    public function publishFjord()
    {
        if (File::exists(base_path('fjord/app/Kernel.php'))) {
            return;
        }
        $this->info('publishing fjord');
        // clear the config cache, otherwise, fjord_resource_path() will return
        // the resource path itself, which is present for shure
        $this->callSilent('config:clear');

        File::copyDirectory(fjord_path('publish/fjord'), base_path('fjord'));

        $composer = json_decode(File::get(base_path('composer.json')), true);
        $composer['autoload']['psr-4']['FjordApp\\'] = 'fjord/app/';
        File::put(base_path('composer.json'), json_encode($composer, JSON_PRETTY_PRINT));
        shell_exec('composer dumpautoload');
    }

    /**
     * Create Model 
     *
     * @return void
     */
    private function makeModelDirs()
    {
        $this->makeDirectory(app_path('Models'));
        $this->makeDirectory(app_path('Models/Translations'));
    }
}
