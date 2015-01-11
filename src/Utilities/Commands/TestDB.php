<?php namespace SRLabs\Utilities\Commands;

use Illuminate\Config\FileLoader;
use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;

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

	public function __construct(Filesystem $file, Repository $config)
	{
		parent::__construct();
		// DI Member assignment
		$this->file = $file;
		$this->config = $config;
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
		$this->confirmTestingDBConfig();

		// Gather connection name
		$this->connection = $this->argument('connection');

		// Make sure the connection is usable
		$this->prepareDBConnection();

		// Confirm DB file exists
		$this->prepareSQLiteFile();

		// Everything is in order - we can proceed.
		$this->call('migrate', array('--database' => $this->connection, '--env' => 'testing'));
		$this->call('db:seed', array('--database' => $this->connection, '--env' => 'testing'));
	}

	/**
	 * This whole endeavor is pointless if there is no testing environment configuration available.
	 */
	protected function confirmTestingDBConfig()
	{
		if (! $this->file->exists(app_path().'/config/testing/database.php'))
		{
			$this->error( 'No database config found for the testing environment.' );
			exit();
		}
	}

	/**
	 * Make sure the specified connection exists in the testing config file.  If it does, 
	 * add those details to the current environment's database connections.
	 */
	protected function prepareDBConnection()
	{
		// Logic flag
		$exists = false;
		
		// Read the testing database config file
		$config = require app_path().'/config/testing/database.php';

		// Search through the testing config options to find the 
		// specified connection details
		foreach($config['connections'] as $name => $connection)
		{
			if ($name == $this->connection && $connection['driver'] == 'sqlite')
			{
				// We found it. 
				$exists = true;
				
				// Save the path to the sqlite file
				$this->dbpath = $connection['database'];
				
				// Pull this connection into the current environment
				$this->config->set('database.connections.' . $this->connection, $connection);
				$this->config->set('database.default', 'sqlite');
			}
		}

		if ( ! $exists)
		{
			// We couldn't find it.  Abort!
			$this->error('SQLite DB connection "' . $this->connection . '" not found in testing config.' );
			exit();
		}
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
			array('connection', InputArgument::OPTIONAL, 'Testing DB Connection Name', 'setup'),
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
//			array('path', null, InputOption::VALUE_OPTIONAL, 'Path to target directory', public_path() . '/uploads'),
		);
	}
}