<?php
	namespace DataFX;
	/**
	 * Represents a record on a Table.
	 * @author Michael Becker
	 * @see Table
	 * @see RecordColumn
	 */
	class Record
	{
		/**
		 * The column values for this record.
		 * @var RecordColumn[]
		 */
		public $Columns;
		
		/**
		 * Creates a Record object with the specified column values, but does not insert the record into a table. 
		 * @param RecordColumn[] $columns The column values to associate with this Record.
		 */
		public function __construct($columns)
		{
			$this->Columns = $columns;
		}
	}
?>