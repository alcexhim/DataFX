<?php
	namespace DataFX;
	class Record
	{
		public $Columns;
		
		public function __construct($columns)
		{
			$this->Columns = $columns;
		}
	}
?>