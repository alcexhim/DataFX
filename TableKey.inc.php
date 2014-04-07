<?php
	namespace DataFX;
	
	use WebFX\System;
	class TableKey
	{
		public $Columns;
		
		public function __construct($columns)
		{
			if (is_array($columns))
			{
				$this->Columns = $columns;
			}
		}
	}
	class TableKeyColumn
	{
		public $Name;
		
		public function __construct($name)
		{
			$this->Name = $name;
		}
	}
?>