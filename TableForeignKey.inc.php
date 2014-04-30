<?php
	namespace DataFX;
	
	\Enum::Create("DataFX\\TableForeignKeyReferenceOption", "Restrict", "Cascade", "SetNull", "NoAction");
	class TableForeignKey
	{
		public $ID;
		public $ColumnName;
		public $ForeignColumnReference;
		
		public $DeleteAction;
		public $UpdateAction;
		
		public function __construct($columnName, $foreignColumnReference, $deleteAction = null, $updateAction = null, $id = null)
		{
			$this->ID = $id;
			$this->ColumnName = $columnName;
			$this->ForeignColumnReference = $foreignColumnReference;
			
			if ($deleteAction == null) $deleteAction = TableForeignKeyReferenceOption::Restrict;
			$this->DeleteAction = $deleteAction;
			if ($updateAction == null) $updateAction = TableForeignKeyReferenceOption::Restrict;
			$this->UpdateAction = $updateAction;
		}
	}
	class TableForeignKeyColumn
	{
		public $Table;
		public $Column;
		
		public function __construct($table, $column)
		{
			$this->Table = $table;
			if (is_string($column))
			{
				$column = $table->GetColumnByName($column);
			}
			$this->Column = $column;
		}
	}
?>