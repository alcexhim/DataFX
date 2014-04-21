<?php
	namespace DataFX;
	
	class DatabaseOperationResult
	{
		public $AffectedRowCount;
		
		public function __construct($affectedRowCount)
		{
			$this->AffectedRowCount = $affectedRowCount;
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