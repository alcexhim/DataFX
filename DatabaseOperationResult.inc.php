<?php
	namespace DataFX;
	
	class DatabaseOperationResult
	{
		public $AffectedRowCount;
		
		public function __construct($affectedRowCount)
		{
			$this->InsertedRowCount = $insertedRowCount;
		}
	}
	class InsertResult extends DatabaseOperationResult
	{
		public $LastInsertID;
		
		public function __construct($affectedRowCount, $lastInsertId)
		{
			parent::__construct($affectedRowCount);
			$this->LastInsertID = $lastInsertId;
		}
	}
?>