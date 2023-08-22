<?php

namespace Maxi032\LaravelAdminPackage\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class TestNpm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lap:test-npm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test if npm is installed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // if exit code is 0 it means that there was no error and npm is installed
        echo (int)Process::run('npm -v')->exitCode();
    }
}
