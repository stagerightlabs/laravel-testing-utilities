<?php namespace SRLabs\Utilities\Traits;

use SRLabs\Utilities\Exceptions\InvalidSQLiteConnectionException;

trait TestingDatabase {

	/**
	 * Implement the testing database management technique described by Chris Duell
	 * http://www.chrisduell.com/blog/development/speeding-up-unit-tests-in-php/
	 */
	public function prepareDatabase($source, $destination)
	{
		$source = config('database.connections.' . $source);
		$destination = config('database.connections.' . $destination);

		if (!is_array($source) || !is_array($destination))
		{
			throw new InvalidSQLiteConnectionException('You have specified an invalid sqlite connection.');
		}

		exec('cp ' . $source['database'] . ' ' . $destination['database']);
	}
}