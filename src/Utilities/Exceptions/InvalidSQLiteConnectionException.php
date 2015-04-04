<?php namespace SRLabs\Utilities\Exceptions;

class InvalidSQLiteConnectionException extends Exception
{
	public function toString() {
		return __CLASS__ . ": [{$this->code}] : {$this->message}\n";
	}
}