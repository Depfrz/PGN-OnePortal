<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class AnalyticsRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:run {module : Slug of the module to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Python Data Engine for specific module';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $module = $this->argument('module');
        $pythonPath = base_path('python_engine/main.py');
        
        $this->info("Starting Data Engine for module: {$module}");
        
        // Asumsi 'python' ada di PATH environment variable.
        // Di production, mungkin perlu path absolut ke executable python.
        $result = Process::run("python \"{$pythonPath}\" --module={$module}");

        if ($result->successful()) {
            $output = $result->output();
            $this->info("Process Finished Successfully.");
            $this->line("Output: " . $output);
            
            // Log output for debugging
            Log::info("Analytics Engine Success [{$module}]: " . $output);
            
            // TODO: Parse JSON output and save to database
            return Command::SUCCESS;
        } else {
            $this->error("Process Failed!");
            $this->error("Error Output: " . $result->errorOutput());
            
            Log::error("Analytics Engine Failed [{$module}]: " . $result->errorOutput());
            
            return Command::FAILURE;
        }
    }
}
