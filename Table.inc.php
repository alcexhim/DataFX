<?php
	namespace DataFX;
	
	use WebFX\System;
	
	/**
	 * Represents a table on the database.
	 * @author Michael Becker
	 * @see Column
	 * @see Record
	 * @see TableForeignKey
	 */
	class Table
	{
		/**
		 * The name of the table.
		 * @var string
		 */
		public $Name;
		/**
		 * The prefix used before the name of each column in the table.
		 * @var string
		 */
		public $ColumnPrefix;
		/**
		 * The columns on the table.
		 * @var Column[]
		 */
		public $Columns;
		/**
		 * The records in the table.
		 * @var Record[]
		 */
		public $Records;
		
		/**
		 * The key that is the primary key of the table.
		 * @var TableKey
		 */
		public $PrimaryKey;
		/**
		 * The key(s) that are the unique keys of the table.
		 * @var TableForeignKey[]
		 */
		public $UniqueKeys;
		/**
		 * Any additional key(s) on the table that are not primary or unique keys.
		 * @var TableForeignKey[]
		 */
		public $ForeignKeys;
		
		/**
		 * Creates a Table object with the given parameters (but does not create the table on the database).
		 * @param string $name The name of the table.
		 * @param string $columnPrefix The prefix used before the name of each column in the table.
		 * @param Column[] $columns The column(s) of the table.
		 * @param Record[] $records The record(s) to insert into the table.
		 */
		public function __construct($name, $columnPrefix, $columns, $records = null)
		{
			$this->Name = $name;
			$this->ColumnPrefix = $columnPrefix;
			$this->Columns = $columns;
			
			if ($records == null) $records = array();
			$this->Records = $records;
			
			$this->PrimaryKey = null;
			$this->UniqueKeys = array();
			$this->ForeignKeys = array();
		}
		
		/**
		 * Gets the table with the specified name from the database.
		 * @param string $name The name of the table to search for.
		 * @param string $columnPrefix The column prefix for the columns in the table. Columns that begin with this prefix will be populated with the prefix stripped.
		 * @return Table The table with the specified name.
		 */
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
		
		/**
		 * Creates the table on the database.
		 * @return boolean True if the table was created successfully; false if an error occurred.
		 */
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
				if ($column->AllowNull == false)
				{
					$query .= " NOT NULL";
				}
				if ($column->DefaultValue != null)
				{
					$query .= " DEFAULT ";
					if ($column->DefaultValue === ColumnValue::Undefined)
					{
						$query .= "NULL";
					}
					else if ($column->DefaultValue === ColumnValue::CurrentTimestamp)
					{
						$query .= "CURRENT_TIMESTAMP";
					}
					else if (is_string($column->DefaultValue))
					{
						$query .= "\"" . $column->DefaultValue . "\"";
					}
					else
					{
						$query .= $column->DefaultValue;
					}
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
			
			$count = count($this->ForeignKeys);
			if ($count > 0)
			{
				$query .= ", ";
				for ($i = 0; $i < $count; $i++)
				{
					$fk = $this->ForeignKeys[$i];
					$query .= "FOREIGN KEY ";
					if ($fk->ID != null)
					{
						$query .= $fk->ID . " ";
					}
					$query .= "(" . $this->ColumnPrefix . $fk->ColumnName . ")";
					$query .= " REFERENCES " . System::GetConfigurationValue("Database.TablePrefix") . $fk->ForeignColumnReference->Table->Name . " (" . $fk->ForeignColumnReference->Table->ColumnPrefix . $fk->ForeignColumnReference->Column->Name . ")";
					
					$query .= " ON DELETE ";
					switch ($fk->DeleteAction)
					{
						case TableForeignKeyReferenceOption::Restrict:
						{
							$query .= "RESTRICT";
							break;
						}
						case TableForeignKeyReferenceOption::Cascade:
						{
							$query .= "CASCADE";
							break;
						}
						case TableForeignKeyReferenceOption::SetNull:
						{
							$query .= "SET NULL";
							break;
						}
						case TableForeignKeyReferenceOption::NoAction:
						{
							$query .= "NO ACTION";
							break;
						}
					}
					
					$query .= " ON UPDATE ";
					switch ($fk->DeleteAction)
					{
						case TableForeignKeyReferenceOption::Restrict:
						{
							$query .= "RESTRICT";
							break;
						}
						case TableForeignKeyReferenceOption::Cascade:
						{
							$query .= "CASCADE";
							break;
						}
						case TableForeignKeyReferenceOption::SetNull:
						{
							$query .= "SET NULL";
							break;
						}
						case TableForeignKeyReferenceOption::NoAction:
						{
							$query .= "NO ACTION";
							break;
						}
					}
					
					if ($i < $count - 1) $query .= ", ";
				}
			}
			
			$query .= ")";
			
			$result = $MySQL->query($query);
			if ($result === false)
			{
				trigger_error("DataFX error: " . $MySQL->errno . ": " . $MySQL->error);
				trigger_error("DataFX query: " . $query);
				DataFX::$Errors->Clear();
				DataFX::$Errors->Add(new DataFXError($MySQL->errno, $MySQL->error, $query));
				return false;
			}
			
			if ($this->PrimaryKey != null)
			{	
				$key = $this->PrimaryKey;
				$query = "ALTER TABLE `" . System::$Configuration["Database.TablePrefix"] . $this->Name . "` ADD PRIMARY KEY (";
				$count = count($key->Columns);
				for ($i = 0; $i < $count; $i++)
				{
					$col = $key->Columns[$i];
					$query .= "`" . $this->ColumnPrefix . $col->Name . "`";
					if ($i < $count - 1)
					{
						$query .= ", ";
					}
				}
				$query .= ");";

				$result = $MySQL->query($query);
				if ($result === false)
				{
					DataFX::$Errors->Clear();
					DataFX::$Errors->Add(new DataFXError($MySQL->errno, $MySQL->error, $query));
					return false;
				}
			}
			foreach ($this->UniqueKeys as $key)
			{
				$query = "ALTER TABLE `" . System::$Configuration["Database.TablePrefix"] . $this->Name . "` ADD UNIQUE (";
				$count = count($key->Columns);
				for ($i = 0; $i < $count; $i++)
				{
					$col = $key->Columns[$i];
					$query .= "`" . $this->ColumnPrefix . $col->Name . "`";
					if ($i < $count - 1)
					{
						$query .= ", ";
					}
				}
				$query .= ")";
				
				$result = $MySQL->query($query);
				if ($result === false)
				{
					DataFX::$Errors->Clear();
					DataFX::$Errors->Add(new DataFXError($MySQL->errno, $MySQL->error, $query));
					return false;
				}
			}
				
			$result = $this->Insert($this->Records);
			if ($result == null) return false;
			
			return true;
		}
		
		/**
		 * 
		 * @param Record[] $records The record(s) to insert into the table.
		 * @param boolean $stopOnError True if processing of the records should stop if an error occurs; false to continue.
		 * @return NULL|InsertResult
		 */
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
					if ($column->Value === null || $column->Value === ColumnValue::Undefined)
					{
						$query .= "NULL";
					}
					else if ($column->Value === ColumnValue::Now)
					{
						$query .= "NOW()";
					}
					else if ($column->Value === ColumnValue::CurrentTimestamp)
					{
						$query .= "CURRENT_TIMESTAMP";
					}
					else if ($column->Value === ColumnValue::Today)
					{
						$query .= "TODAY()";
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
		
		/**
		 * Deletes this table from the database.
		 * @return boolean True if the table was deleted successfully; false otherwise.
		 */
		public function Delete()
		{
			global $MySQL;
			$query = "DROP TABLE " . System::$Configuration["Database.TablePrefix"] . $this->Name;
			$result = $MySQL->query($query);
			if ($result === false)
			{
				DataFX::$Errors->Clear();
				DataFX::$Errors->Add(new DataFXError($MySQL->errno, $MySQL->error, $query));
				return false;
			}
		}
		
		/**
		 * Determines if this table exists on the database.
		 * @return boolean True if this table exists; false otherwise.
		 */
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
		
		/**
		 * Retrieves the Column with the given name on this Table.
		 * @param string $name The name of the column to search for.
		 * @return Column|NULL The column with the given name, or NULL if no columns with the given name were found.
		 */
		public function GetColumnByName($name)
		{
			foreach ($this->Columns as $column)
			{
				if ($column->Name == $name) return $column;
			}
			return null;
		}
	}
?>