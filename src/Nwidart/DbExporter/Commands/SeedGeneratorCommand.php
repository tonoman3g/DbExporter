<?php namespace Nwidart\DbExporter\Commands;


use Config;
use Nwidart\DbExporter\DbExporter;
use Nwidart\DbExporter\DbExportHandler;
use Str;
use Symfony\Component\Console\Input\InputOption;

class SeedGeneratorCommand extends GeneratorCommand
{
    protected $name = 'dbe:seeds';

    protected $description = 'Export your database table data to a seed class.';

    /**
     * @var \Nwidart\DbExporter\DbExportHandler
     */
    protected $handler;

    public function __construct(DbExportHandler $handler)
    {
        parent::__construct();

        $this->handler = $handler;
    }

    public function fire()
    {
        $this->comment("Preparing the seeder class for database {$this->getDatabaseName()}");

        // Grab the options
        $ignore = $this->option('ignore');
        $table = $this->option('table');
        $targetFilename = $this->option('name');

        if (!empty($targetFilename)) {
            $this->handler->targetFileName($targetFilename);
        }

        if (!empty($ignore)) {
            $ignoredTables = explode(',', str_replace(' ', '', $ignore));
            $this->handler->ignore($ignoredTables);
            foreach (DbExporter::$ignore as $ignoredTable) {
                $this->comment("Ignoring the {$ignoredTable} table");
            }
        }

        if (!empty($table)) {
            $processedTables = explode(',', str_replace(' ', '', $table));
            $this->handler->table($processedTables);
            foreach (DbExporter::$tables as $table) {
                $this->comment("Proccessing the {$table} table");
            }
        }
        $this->handler->seed();

        // Symfony style block messages
        $formatter = $this->getHelperSet()->get('formatter');
        $filename = $this->getFilename();

        $errorMessages = array('Success!', "Database seed class generated in: {$filename}");

        $formattedBlock = $formatter->formatBlock($errorMessages, 'info', true);
        $this->line($formattedBlock);
    }

    private function getFilename()
    {
        if (empty(DbExporter::$targetFilename)) {
            $defaultFilename = ucfirst(Str::camel($this->getDatabaseName()));
        } else {
            $defaultFilename = ucfirst(DbExporter::$targetFilename);
        }

        $filename = $defaultFilename . "TableSeeder";

        return config('db-exporter.export_path.seeds') . "{$filename}.php";
    }

    protected function getOptions()
    {
        return array(
            array('ignore', 'ign', InputOption::VALUE_REQUIRED, 'Ignore tables to export, separated by a comma', null),
            array('table', 'tbl', InputOption::VALUE_REQUIRED, 'Table names to export, seperated by a comma', null),
            array('name', null, InputOption::VALUE_REQUIRED, 'Generated file name', null)
        );
    }
}