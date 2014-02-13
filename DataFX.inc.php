<?php
	namespace DataFX;
	use WebFX\System;
	
	class DataFX
	{
		public static $Errors;
		
		public static function Initialize()
		{
			global $MySQL;
			if (!(isset(System::$Configuration["Database.ServerName"]) && isset(System::$Configuration["Database.UserName"]) && isset(System::$Configuration["Database.Password"]) && isset(System::$Configuration["Database.DatabaseName"])))
			{
				// DataFX error!
				return false;
			}
			
			$MySQL = new \mysqli(System::$Configuration["Database.ServerName"], System::$Configuration["Database.UserName"], System::$Configuration["Database.Password"], System::$Configuration["Database.DatabaseName"]);
			$MySQL->set_charset("utf8");
			
			if ($MySQL->connect_error)
			{
				DataFX::$Errors->Clear();
				DataFX::$Errors->Add(new DataFXError($MySQL->connect_errno, $MySQL->connect_error));
				return false;
			}
			
			require_once("Column.inc.php");
			require_once("Table.inc.php");
			return true;
		}
	}
	DataFX::$Errors = new DataFXErrorCollection();
	
	class DataFXError
	{
		public $Code;
		public $Message;
		
		public function __construct($code, $message)
		{
			$this->Code = $code;
			$this->Message = $message;
		}
	}
	class DataFXErrorCollection
	{
		public function __construct()
		{
			$this->Clear();
		}
		
		public $Items;
		public function Add($item)
		{
			$this->Items[] = $item;
		}
		public function Clear()
		{
			$this->Items = array();
		}
	}
	
	DataFX::Initialize();
?>
