<?php
	namespace DataFX;
	/**
	 * Associates a column name with a value for a record on a table.
	 * @author Michael Becker
	 * @see Record
	 * @see Column
	 */
	class RecordColumn
	{
		/**
		 * The name of the column for which to associate a value.
		 * @var string
		 */
		public $Name;
		/**
		 * The value to associate.
		 * @var unknown
		 */
		public $Value;
		
		/**
		 * Creates a RecordColumn with the specified values.
		 * @param string $name The name of the column for which to associate a value.
		 * @param unknown $value The value to associate.
		 */
		public function __construct($name, $value)
		{
			$this->Name = $name;
			$this->Value = $value;
		}
	}
?>