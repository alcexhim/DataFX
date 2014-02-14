<?php
	namespace DataFX;
	
	use WebFX\System;
	class Table
	{
		public $Name;
		public $Columns;
		
		public function __construct($name, $columns)
		{
			$this->Name = $name;
			$this->Columns = $columns;
		}
		
		public function Create()
		{
			global $MySQL;
			$query = "CREATE TABLE " . System::$Configuration["Database.TablePrefix"] . $this->Name;
			
			$query .= "(";
			$count = count($this->Columns);
			for ($i = 0; $i < $count; $i++)
			{
				$column = $this->Columns[$i];
				$query .= $column->Name . " " . $column->DataType;
				if ($column->Size != null)
				{
					$query .= "(" . $column->Size . ")";
				}
				if ($column->DefaultValue != null)
				{
					$query .= " DEFAULT " . $column->DefaultValue;
				}
				if ($i < $count - 1) $query .= ", ";
			}
			$query .= ")";
			echo($query);
			
			$result = $MySQL->query($query);
			if ($result === false)
			{
				DataFX::$Errors->Clear();
				DataFX::$Errors->Add(new DataFXError($MySQL->errno, $MySQL->error));
				return false;
			}
		}
		public function Delete()
		{
			global $MySQL;
			$query = "DROP TABLE " . System::$Configuration["Database.TablePrefix"] . $this->Name;
			$result = $MySQL->query($query);
			if ($result === false)
			{
				DataFX::$Errors->Clear();
				DataFX::$Errors->Add(new DataFXError($MySQL->errno, $MySQL->error));
				return false;
			}
		}
		public function Exists()
		{
			global $MySQL;
			$query = "SHOW TABLES LIKE '" . System::$Configuration["Database.TablePrefix"] . $this->Name . "'";
			$result = $MySQL->query($query);
			if ($result !== false)
			{
				return ($result->num_rows > 0);
			}
			return false;
		}
	}
?>