<?php

namespace Agp\Report\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:report {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria uma classe de Report.';
    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $model = $this->argument('model');
        if ($model == '')
            return $this->error('Nome da model entity é inválido.');

        $columns = '';
        $class = '\App\Model\Entity\\' . $model;
        if (class_exists($class)) {
            $object = new $class();
            if (method_exists($object, 'getFillable')) {
                foreach ($object->getFillable() as $item) {
                    $columns .= '        $this->addColumn("' . $item . '")->setTitle("' . strtoupper($item) . '");' . chr(10);
                }
            }
        }

        $contents =
            '<?php


namespace App\Reports;


use Agp\Report\ReportTotalizador;
use App\Model\Entity\\' . $model . ';

class ' . $model . 'Report extends \Agp\Report\Report
{
    public function __construct()
    {
        parent::__construct();

        //SQL inicial
        $this->queryBuilder = function () {
            return ' . $model . '::query();
        };
' . $columns . '
    }
}';
        if ($this->confirm('Do you wish to create ' . $model . 'Report file?')) {
            $file = "${model}Report.php";
            $path = app_path();

            $file = $path . "/Reports/$file";
            $composerDir = $path . "/Reports";

            if ($this->files->isDirectory($composerDir)) {
                if ($this->files->isFile($file))
                    return $this->error($model . 'Report Already exists!');

                if (!$this->files->put($file, $contents))
                    return $this->error('Something went wrong!');
                $this->info("{$model}Report generated!");
            } else {
                $this->files->makeDirectory($composerDir, 0775, true, true);

                if (!$this->files->put($file, $contents))
                    return $this->error('Something went wrong!');
                $this->info("{$model}Report generated!");
            }
        }
    }
}
