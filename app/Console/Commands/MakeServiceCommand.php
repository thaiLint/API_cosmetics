<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name : The name of the service class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class in app/Services';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $directory = app_path('Services');
        $path = $directory . '/' . $name . '.php';

        if (File::exists($path)) {
            $this->error("❌ Service already exists: {$path}");
            return Command::FAILURE;
        }

        $template = <<<PHP
<?php

namespace App\Services;

class {$name}
{
    public function __construct()
    {
        //
    }
}
PHP;

        File::put($path, $template);

        $this->info("✅ Service created successfully: app/Services/{$name}.php");
        return Command::SUCCESS;
    }
}
