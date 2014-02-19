<?php
	namespace DataFX;
	
	class DataObject
	{
		public static function Create($name, $prefix, $_)
		{
			// Fetch the arguments of the function
			$args = func_get_args();
			
			// Exclude the "name" and "prefix" arguments from the array of function arguments,
			// so only the property definitions names remain in the array
			array_shift($args);
			array_shift($args);
			$args = $args[0];
			
			// UPDATE 2013-08-23 14:52 by BECKERMJ - it now supports enums in namespaces!
			$classDeclaration = "";
			$realName = $name;
			$ns = null;
			$nses = explode("\\", $realName);
			$nsc = count($nses);
			if ($nsc > 1)
			{
				$realName = $nses[$nsc - 1];
				$ns = "";
				for ($i = 0; $i < $nsc - 1; $i++)
				{
					$ns .= $nses[$i];
					if ($i < $nsc - 2) $ns .= "\\";
				}
				$classDeclaration = "namespace " . $ns . "; \n";
			}
			
			// Generate the code of the class for this enumeration
			$classDeclaration .= "class " . $realName . " { \n";
			foreach ($args as $arg)
			{
				$classDeclaration .= "\tpublic \$" . $arg->Name . ";\n";
			}
			
			$classDeclaration .= "\n\tpublic static function GetByAssoc(\$values)\n\t{\n";
			$classDeclaration .= "\t\t\$item = new " . $name . "();\n\n";
			foreach($args as $arg)
			{
				$classDeclaration .= "\t\t\$item->" . $arg->Name . " = \$values[\"" . $prefix . $arg->Name . "\"];\n";
			}
			$classDeclaration .= "\n\t\treturn \$item;\n";
			$classDeclaration .= "\t}\n";
			/*
			public function ToJSON()
			{
				$json = "{";
				$json .= "\"ID\":" . $this->ID . ",";
				$json .= "\"Title\":\"" . \JH\Utilities::JavaScriptDecode($this->Title,"\"") . "\",";
				$json .= "\"URL\":\"" . \JH\Utilities::JavaScriptDecode($this->URL,"\"") . "\"";
				$json .= "}";
				return $json;
			}
			*/
			$classDeclaration .= "\n\tpublic function ToJSON()\n\t{\n";
			$classDeclaration .= "\t\t\$json = \"{\";\n\n";
			foreach($args as $arg)
			{
				$classDeclaration .= "\t\t\$json .= \"\\\"" . $arg->Name . "\\\":\";\n";
				$classDeclaration .= "\t\tif (gettype(\$this->" . $arg->Name . ") == \"string\")\n\t\t{";
				$classDeclaration .= "\n\t\t\t\$json .= \"\\\"\" . \$MySQL->real_escape_string(\$this->" . $arg->Name . ") . \"\\\"\";\n\t\t";
				$classDeclaration .= "}\n\t\telse\n\t\t{";
				$classDeclaration .= "\n\t\t\t\$json .= \$this->" . $arg->Name . ";\n\t\t";
				$classDeclaration .= "}\n\n ";

				// .= \" . \\JH\\Utilities::JavaScriptDecode(\$this->" . $arg . ",\"\\\") . "\"\\\",\"">" . $arg . " = \$values[\$this->GetTablePrefix() . \"" . $arg . "\"];\n";
			}
			$classDeclaration .= "\t\t\$json .= \"}\";\n\n";
			$classDeclaration .= "\n\t\treturn \$json;\n";
			$classDeclaration .= "\t}\n";
			
			
			
			$classDeclaration .= "\n\tpublic function Get(\$max = null)\n\t{\n";
			$classDeclaration .= "\t\tglobal \$MySQL;\n";
			$classDeclaration .= "\t\t\$query = \"SELECT * FROM \" . System::GetConfigurationValue(\"Database.TablePrefix\") . \"StartPages\";\n";
			$classDeclaration .= "\t\tif (is_numeric(\$max)) \$query .= \" LIMIT \" . \$max;\n";
			$classDeclaration .= "\t\t\$result = \$MySQL->query(\$query);\n";
			$classDeclaration .= "\t\t\$count = \$result->num_rows;\n";
			$classDeclaration .= "\t\t\$retval = array();\n";
			$classDeclaration .= "\t\tfor (\$i = 0; \$i < \$count; \$i++)\n";
			$classDeclaration .= "\t\t{\n";
			$classDeclaration .= "\t\t\t\$values = \$result->fetch_assoc();\n";
			$classDeclaration .= "\t\t\t\$retval[] = " . $name . "::GetByAssoc(\$values);\n";
			$classDeclaration .= "\t\t}\n";
			$classDeclaration .= "\t\treturn \$retval;\n";
			$classDeclaration .= "\t}\n";
			
			$classDeclaration .= "}";

			// Create the class for this enumeration
			eval($classDeclaration);
		}
		
	}
?>