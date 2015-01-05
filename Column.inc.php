<?php
	namespace DataFX;
	/**
	 * Represents a column on a Table.
	 * @author Michael Becker
	 * @see Table
	 */
	class Column
	{
		/**
		 * The name of the column.
		 * @var string
		 */
		public $Name;
		/**
		 * The data type of the column.
		 * @var string
		 */
		public $DataType;
		/**
		 * The maximum size of the data in the column.
		 * @var int
		 */
		public $Size;
		/**
		 * The default value of the column.
		 * @var string
		 */
		public $DefaultValue;
		/**
		 * True if the column should allow null values; false otherwise.
		 * @var boolean
		 */
		public $AllowNull;
		/**
		 * True if the column is a primary key; false otherwise.
		 * @var boolean
		 */
		public $PrimaryKey;
		/**
		 * True if the column should automatically increment its value when a row is added; false otherwise.
		 * @var boolean
		 */
		public $AutoIncrement;
		
		/**
		 * Creates a Column with the specified values.
		 * @param string $name The name of the column.
		 * @param string $dataType The data type of the column.
		 * @param string $size The maximum size of the data in the column. Default null.
		 * @param string $defaultValue The default value of the column. Default null.
		 * @param boolean $allowNull True if the column should allow null values; false otherwise. Default false.
		 * @param boolean $primaryKey True if the column is a primary key; false otherwise. Default false.
		 * @param boolean $autoIncrement True if the column should automatically increment its value when a row is added; false otherwise. Default false.
		 */
		public function __construct($name, $dataType, $size = null, $defaultValue = null, $allowNull = false, $primaryKey = false, $autoIncrement = false)
		{
			$this->Name = $name;
			$this->DataType = $dataType;
			$this->Size = $size;
			$this->DefaultValue = $defaultValue;
			$this->AllowNull = $allowNull;
			$this->PrimaryKey = $primaryKey;
			$this->AutoIncrement = $autoIncrement;
		}
	}
?>