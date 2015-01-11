<?php namespace SRLabs\Utilities\Traits;


trait TestingDatabase {

	/**
	 * Implement the testing database management technique described by Chris Duell
	 * http://www.chrisduell.com/blog/development/speeding-up-unit-tests-in-php/
	 */
	public function prepareDatabase($source, $destination)
	{
		$source = \Config::get('database.connections.' . $source);
		$destination = \Config::get('database.connections.' . $destination);

		exec('rm ' . $destination['database']);
		exec('cp ' . $source['database'] . ' ' . $destination['database']);
	}
}