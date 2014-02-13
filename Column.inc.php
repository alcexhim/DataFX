<?php
	namespace DataFX;
	class Column
	{
		public $Name;
		public $DataType;
		public $Size;
		public $DefaultValue;
		public $AllowNull;
		public $PrimaryKey;
		public $AutoIncrement;
		
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