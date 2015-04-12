<?php namespace SRLabs\Utilities\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class TestDB extends Command {

    protected $name = 'utility:testdb';

    protected $description = 'Flush and migrate the testing database.';

    /**
     * @var
     */
    private $file;

    /**
     * @var
     */
    private $connection;

    /**
     * @var
     */
    private $dbpath;

    public function __construct(Filesystem $file)
    {
        parent::__construct();
        // DI Member assignment
        $this->file = $file;
    }

    /*
     * Don't allow this command to be run in a production environment
     */
    use ConfirmableTrait;

    /**
     * This command prepares a sqlite testing database, to allow for the
     * technique described by Chris Duell here:
     * http://www.chrisduell.com/blog/development/speeding-up-unit-tests-in-php/
     */
    public function fire()
    {
        // Don't allow this command to run in a production environment
        if ( ! $this->confirmToProceed()) return;

        // First check that we are using sqlite as the testing database
        $this->prepareDatabaseConnection();

        // Confirm DB file exists
        $this->prepareSQLiteFile();

        // Gather arguments    
        $name = $this->argument('connection');
        $connection = config('database.connections.' . $name, []);

        // Gather options
        $seeder = $this->option('class');

        // Clear existing database, if necessary
        if (is_readable($connection['database']))
        {
            unlink($connection['database']);
        }

        // Everything is in order - we can proceed.
        $this->call('migrate', array('--database' => $name));
        
        // If a seeder class was specified, pass that to the seed command
        $this->call('db:seed', array('--database' => $name, '--class' => $seeder));

        // Send a completion message to the user
        $this->info($connection['database'] . " has been refreshed.");
    }

    /**
     * This whole endeavor is pointless if there is no testing environment configuration available.
     */
    protected function prepareDatabaseConnection()
    {
        $this->connection = config('database.connections.' . $this->argument('connection'), []);

        if (empty($this->connection) || !array_key_exists('database', $this->connection))
        {
            $this->error('SQLite DB connection "' . $this->argument('connection') . '" not found in config.' );
            exit();
        }

        if ($this->connection['driver'] != 'sqlite')
        {
            $this->error( "This technique is not intended to be used on a non-sqlite database." );
            exit();
        }

        // Save the path to the sqlite file
        $this->dbpath = $this->connection['database'];
    }

    /**
     * We want to start with a clean slate, i.e. an empty sqlite file.
     */
    protected function prepareSQLiteFile()
    {
        // First remove the old database file
        $this->file->delete($this->dbpath);

        // Now create an empty target database file
        touch($this->dbpath);

        // Double check that the file exists before moving on
        if (! $this->file->exists($this->dbpath))
        {
            $this->error( 'SQlite file not found.' );
            exit();
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('connection', InputArgument::OPTIONAL, 'Testing DB Connection Name', 'staging'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('class', null, InputOption::VALUE_OPTIONAL, 'The class to be used for seeding.', 'DatabaseSeeder'),
        );
    }
}