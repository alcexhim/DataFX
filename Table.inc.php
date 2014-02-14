<?php
	namespace DataFX;
	
	use WebFX\System;
	class Table
	{
		public $Name;
		public $ColumnPrefix;
		public $Columns;
		
		public function __construct($name, $columnPrefix, $columns)
		{
			$this->Name = $name;
			$this->ColumnPrefix = $columnPrefix;
			$this->Columns = $columns;
		}
		
		public static function Get($name, $columnPrefix = null)
		{
			global $MySQL;
			
			$query = "SHOW COLUMNS FROM " . System::GetConfigurationValue("Database.TablePrefix") . $name;
			$result = $MySQL->query($query);
			$count = $result->num_rows;
			$columns = array();
			for ($i = 0; $i < $count; $i++)
			{
				$values = $result->fetch_assoc();
				
				$columnName = $values["Field"];
				if (substr($columnName, 0, strlen($columnPrefix)) == $columnPrefix)
				{
					$columnName = substr($columnName, strlen($columnPrefix));
				}
				$dataTypeNameAndSize = $values["Type"];
				$dataTypeName = substr($dataTypeNameAndSize, 0, strpos($dataTypeNameAndSize, "("));
				$dataTypeSize = substr($dataTypeNameAndSize, strpos($dataTypeNameAndSize, "("), strlen($dataTypeNameAndSize) - strpos($dataTypeNameAndSize, "(") - 2);
				$defaultValue = $values["Default"];
				$allowNull = ($values["Null"] == "YES");
				$primaryKey = ($values["Key"] == "PRI");
				$autoIncrement = ($values["Extra"] == "auto_increment");
				
				$columns[] = new Column($columnName, $dataTypeName, $dataTypeSize, $defaultValue, $allowNull, $primaryKey, $autoIncrement);
			}
			
			return new Table($name, $columnPrefix, $columns);
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
				$query .= ($this->ColumnPrefix . $column->Name) . " " . $column->DataType;
				if ($column->Size != null)
				{
					$query .= "(" . $column->Size . ")";
				}
				if ($column->DefaultValue != null)
				{
					$query .= " DEFAULT " . $column->DefaultValue;
				}
				if ($column->AllowNull == false)
				{
					$query .= " NOT NULL";
				}
				if ($column->PrimaryKey)
				{
					$query .= " PRIMARY KEY";
				}
				if ($column->AutoIncrement)
				{
					$query .= " AUTO_INCREMENT";
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
			return true;
		}
		public function Insert($records, $stopOnError = true)
		{
			DataFX::$Errors->Clear();
			global $MySQL;
			
			foreach ($records as $record)
			{
				$query = "INSERT INTO " . System::GetConfigurationValue("Database.TablePrefix") . $this->Name;
				$query .= " (";
				$count = count($record->Columns);
				for ($i = 0; $i < $count; $i++)
				{
					$column = $record->Columns[$i];
					$query .= ($this->ColumnPrefix . $column->Name);
					if ($i < $count - 1) $query .= ", ";
				}
				$query .= " ) VALUES ( ";
				for ($i = 0; $i < $count; $i++)
				{
					$column = $record->Columns[$i];
					if ($column->Value === ColumnValue::Now)
					{
						$query .= "NOW()";
					}
					else if ($column->Value === ColumnValue::Today)
					{
						$query .= "TODAY()";
					}
					else if ($column->Value === ColumnValue::Undefined)
					{
						$query .= "NULL";
					}
					else if (gettype($column->Value) == "string")
					{
						$query .= "'" . $MySQL->real_escape_string($column->Value) . "'";
					}
					else if (gettype($column->Value) == "object")
					{
						if (get_class($column->Value) == "DateTime")
						{
							$query .= "'" . date_format($column->Value, "Y-m-d H:i:s") . "'";
						}
						else
						{
							$query .= $column->Value;
						}
					}
					else
					{
						$query .= $column->Value;
					}
					if ($i < $count - 1) $query .= ", ";
				}
				$query .= " )";
				
				$result = $MySQL->query($query);
				if ($result === false)
				{
					DataFX::$Errors->Add(new DataFXError($MySQL->errno, $MySQL->error, $query));
					if ($stopOnError) return null;
				}
			}
			return new InsertResult($MySQL->affected_rows, $MySQL->insert_id);
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