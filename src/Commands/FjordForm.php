<?php

namespace Fjord\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Fjord\Support\StubBuilder;
use Illuminate\Support\Facades\File;

class FjordForm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fjord:form {--collection=} {--form=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create controller and config file for a new form.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("    _______                __   ______                   ");
        $this->info("   / ____(_)___  _________/ /  / ____/___  _________ ___ ");
        $this->line("<info>  / /_  / / __ \/ ___/ __  /  / /_  / __ \/ ___/ __ `__ \\");
        $this->info(" / __/ / / /_/ / /  / /_/ /  / __/ / /_/ / /  / / / / / /");
        $this->info("/_/ __/ /\____/_/   \__,_/  /_/    \____/_/  /_/ /_/ /_/ ");
        $this->info("   /___/                                                 ");

        $collection = $this->option('collection');
        if (!$collection) {
            $collection = $this->ask('enter the collection name (snake_case, plural)');
        }
        $formName = $this->option('form');
        if (!$formName) {
            $formName = $this->ask('enter the form name (snake_case)');
        }

        $collection = Str::snake($collection);
        $formName = Str::snake($formName);

        $controllerNamespace = ucfirst(Str::camel($collection));
        $controllerName = ucfirst(Str::camel($formName));

        $controllerDir = base_path("fjord/app/Controllers/Form/{$controllerNamespace}");
        $controller = new StubBuilder(fjord_path('stubs/FormController.stub'));
        $controller->withClassname("{$controllerName}Controller");
        $controller->withNamespace($controllerNamespace);
        $controller->withPermission("{$collection}");
        $controller->withConfigClass($controllerName . "Config");

        $configDir = base_path("fjord/app/Config/Form/{$controllerNamespace}");
        $config = new StubBuilder(fjord_path('stubs/FormConfig.stub'));

        // Routing
        $config->withCRouteName(Str::slug($collection, '-'));
        $config->withFormRouteName(Str::slug($formName, '-'));
        $config->withCollection($controllerNamespace);
        $config->withFormName("'" . lcfirst($formName) . "'");
        $config->withController("{$controllerName}Controller");
        $config->withConfigClassName($controllerName . "Config");



        $this->createDirIfNotExists($configDir);
        $this->createDirIfNotExists($controllerDir);

        $controller->create("{$controllerDir}/{$controllerName}Controller.php");
        $config->create("{$configDir}/{$controllerName}Config.php");
    }

    /**
     * Create directory if not exists.
     *
     * @param string $dir
     * @return void
     */
    public function createDirIfNotExists(string $dir)
    {
        if (!File::exists($dir)) {
            File::makeDirectory($dir);
        }
    }
}
