<?php

namespace App\Console\Commands;

use RuntimeException;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class SmartyClearCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'smarty:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all generated smarty cache files';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new config clear command instance.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $path = storage_path('tmpl_c');

        if (! $path) {
            throw new RuntimeException('Smarty cache path not found.');
        }

        foreach ($this->files->glob("{$path}/*") as $blade) {
            $this->files->delete($blade);
        }

        $this->info('Generated smarty cache cleared!');
    }
}
