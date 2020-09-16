<?php
namespace LibPHPAss;

class AssFile {
	private $type = null;

	static private $headParamsAccepted = array(
		"Title",
		"Original Script",
		"Original Translation",
		"Original Editing",
		"Original Timing",
		"Synch Point",
		"Script Updated By",
		"Update Details",
		"ScriptType",
		"Collisions",
		"PlayResX",
		"PlayResY",
		"PlayDepth",
		"Timer",
		"WrapStyle"
	);
	static private $stylesParamsAccepted = array(
		"name",
		"fontname",
		"fontsize",
		"primarycolour",
		"secondarycolour",
		"tertiarycolour",
		"outlinecolor",
		"backcolour",
		"bold",
		"italic",
		"underline",
		"strikeout",
		"scalex",
		"scaley",
		"spacing",
		"angle",
		"borderstyle",
		"outline",
		"shadow",
		"alignment",
		"marginl",
		"marginr",
		"marginv",
		"alphalevel",
		"encoding",
		//Various acceptable but not in spec field name
		"outlinecolour"
	);
	static private $eventParamsAccepted = array(
		"Marked",
		"Layer",
		"Start",
		"End",
		"Style",
		"Name",
		"Actor",
		"MarginL",
		"MarginR",
		"MarginV",
		"Effect",
		"Text"
	);

	private $head = array();
	private $styleFieldsOrder = null;
	private $styles = array();
	private $eventFieldsOrder = null;
	private $events = array();

	/**
	 * @param $content string Content of the ass file.
	 */
	private function __construct($content) {
		//File may contains a BOM so we need to detect it and remove it if necessary
		$bom = pack("CCC", 0xef, 0xbb, 0xbf);
		if (0 == strncmp($content, $bom, 3)) {
			$content = substr($content, 3);
		}

		//Remove any windows line returns
		$content = str_replace("\r\n", "\n", $content);
		//Explode line by line as an ass file can be parsed line by line
		$lines = explode("\n", $content);

		//Now need to parse header
		
		if (strtolower($lines[0]) != "[script info]") {
			//@TODO define an object for this exception
			throw new \Exception("Invalid File Type");
		}

		$currentLine = $endOfHeader = 1;
		//End of Header detection
		while ($endOfHeader < sizeof($lines) && (strlen($lines[$endOfHeader]) == 0 || $lines[$endOfHeader][0] != "[") ) {
			$endOfHeader++;
		}
		if(!stristr(strtolower($lines[$endOfHeader]),'styles')){$endOfHeader++;
			while ($endOfHeader < sizeof($lines) && (strlen($lines[$endOfHeader]) == 0 || $lines[$endOfHeader][0] != "[") ) {
				$endOfHeader++;
			}
		}
		//We create a separate array containing only the header
		$header = array_slice($lines, $currentLine, $endOfHeader - $currentLine);
		//We parse the header
		$this->parseHeader($header);

		//Styles (and ass or ssa determination)
		//Using Style definition starting line, we can determine if the file is in ssa or ass format
		
		if (strtolower(trim($lines[$endOfHeader])) == "[v4 styles]") {
			$this->type = "ssa";
		} else if (strtolower(trim($lines[$endOfHeader])) == "[v4 styles+]" || strtolower(trim($lines[$endOfHeader])) == "[v4+ styles]") {
			$this->type = "ass";
		} else {
			throw new \Exception("Invalid File Type");
		}

		$currentLine = $endOfStyle = $endOfHeader+1;
		//Determine end of Style block
		while ($endOfStyle < sizeof($lines) && (strlen($lines[$endOfStyle]) == 0 || $lines[$endOfStyle][0] != "[")) {
			$endOfStyle++;
		}
		//Fetch only Styles
		$styles = array_slice($lines, $currentLine, $endOfStyle - $currentLine);
		//Parse these styles
		$this->parseStyles($styles);
		
		$endOfEvents=$endOfStyle;
		//Events
		while(strtolower(trim($lines[$endOfEvents])) != "[events]") {
			$endOfEvents++;
			//throw new \Exception("Invalid File Type");
		}
		$endOfEvents++;
		$currentLine = $endOfEvents;

		//Determine the end of event block
		while ($endOfEvents < sizeof($lines) && strlen($lines[$endOfEvents]) >= 0 && @$lines[$endOfEvents][0] != "[") {
			$endOfEvents++;
		}
		//Fetch only Events
		$events = array_slice($lines, $currentLine, $endOfEvents - $currentLine);
		//Parse these events
		$this->parseEvents($events);

		//Next is optionnal : Fonts and Graphics
		// @TODO : implement it
	}

	/**
	 * @param $string
	 * @return AssFile
	 */
	static function loadFromString($string) {
		return new AssFile($string);
	}

	/**
	 * @param $file
	 * @return AssFile
	 * @throws \Exception
	 */
	static function loadFromFile($file) {
		if (!file_exists($file)) {
			//@TODO Declare Exception objects
			throw new \Exception("File not found");
		}

		return self::loadFromString(file_get_contents($file));
	}

	/**
	 * @param $param String Name of the parameter to send
	 * @param $value String Value of the parameter
	 * @return bool
	 */
	private function setHeaderInformation($param, $value) {
		if (array_search($param, self::$headParamsAccepted) === false) {
			return false;
		}

		//Let's manage parameters that have only a set of predefined values
		if ($param == "ScriptType" && (strtolower($value) != "v4.00" && strtolower($value) != "v4.00+")) {
			return false;
		} else if ($param == "Collisions" && ($value != "Normal" && $value != "Reverse")) {
			return false;
		} else if (($param == "PlayResX" || $param == "PlayResY" || $param == "PlayDepth" || $param == "Timer") && (!is_numeric($value))) {
			return false;
		} else if ($param == "WrapStyle" && (!is_numeric($value) || $value < 0 || $value > 3)) {
			return false;
		}

		$this->head[$param] = $value;
		return true;
	}

	/**
	 * @param $header Array Strings that forms the header of the file.
	 */
	private function parseHeader($header) {
		foreach ($header as $line) {
			$data = explode(":", $line, 2);
			if (sizeof($data) == 2) {
				$this->setHeaderInformation(trim($data[0]), trim($data[1]));
			}
		}

	}

	/**
	 * @param null $info Particular field to fetch. If null every fields are returned.
	 * @return array|null
	 */
	public function getHeaderInfo($info = null) {
		if ($info == null) {
			return $this->head;
		} else if (isset($this->head[$info])) {
			return $this->head[$info];
		} else {
			return null;
		}
	}

	private function parseStyles($styles) {
		$formatDefined = false;
		foreach ($styles as $style) {
			$value = explode(":", $style);
			//If the line is not recognized we just skip it.
			if (
				sizeof($value) != 2 ||
				($value[0] != "Format" && $value[0] != "Style")
			) {
				continue;
			}

			if (!$formatDefined) {
				//If there is a style before the Format line, the file is not valid
				if ($value[0] != "Format") {
					throw new \Exception("Style definition found, Format definition expected");
				}

				//So now we know for sure that we have a Format Line
				$this->definedStyleFormatOrder(explode(",", trim($value[1])));
				$formatDefined = true;
			} else {
				//There should only by Style or unkown line type now.
				if ($value[0] != "Style") {
					throw new \Exception("Unexpected format definition found.");
				}

				//We can Now parse the styles
				$this->addStyleDefinition(explode(",", trim($value[1])));
			}
		}

	}

	/**
	 * @param $fields Array fields to define a style
	 * @throws \Exception
	 */
	private function definedStyleFormatOrder($fields) {
		//We need a reference to remove unwanted spaces coming from the file
		foreach ($fields as &$field) {
			//Remove any unwanted spaces
			$field = strtolower(trim($field));
			if (array_search($field, self::$stylesParamsAccepted) === false) {
				throw new \Exception("Unknown Format Parameter : ".$field);
			}
		}

		//No Exceptions thrown, that means that each fields is correct !
		// But there may be some fields that are twice
		if (sizeof(array_unique($fields)) != sizeof($fields)) {
			throw new \Exception("A format parameter is defined twice.");
		}

		$this->styleFieldsOrder = $fields;
	}

	/**
	 * @param $style Array List of values for the style definition
	 * @throws \Exception
	 */
	private function addStyleDefinition($style) {
		//We first need to confirm there is a field order defined
		if ($this->styleFieldsOrder == null) {
			throw new \Exception("Style cannot be parsed without field order defined");
		}

		//Let's confirm that there is enough params
		if (sizeof($style) != sizeof($this->styleFieldsOrder)) {
			throw new \Exception("Bad Style definition : the number of defined fields doesn't match with format definition");
		}

		$styleDefinition = array();

		//Let's now verify each value (this is boring to code)
		// We will also save style definition as long as it is correct
		foreach ($this->styleFieldsOrder as $k => $field) {
			//Let's trim the value before doing anything. We don't want to fail verification because of trailling spaces
			$style[$k] = trim($style[$k]);
			//Let's switch \o/
			switch ($field) {
				//Numbers
				case "fontsize":
				case "scalex":
				case "scaley":
				case "spacing":
				case "angle":
				case "marginl":
				case "marginr":
				case "marginv":
				case "encoding":
					if (!is_numeric($style[$k])) {
						throw new \Exception("Bad Style definition : Number expected, other found");
					}
					break;
				//Colours
				case "primarycolour":
				case "secondarycolour":
				case "outlinecolor":
				case "tertiarycolour":
				case "backcolour":
					if ($this->type == "ssa" && !is_numeric($style[$k])) {
						throw new \Exception("Bad Style definition : In SSA, colours should be numbers in decimal form.");
					}
					if ($this->type == "ass" && preg_match("#^&H[0-9A-F]{8}$#i", $style[$k]) == 0) {
						throw new \Exception("Bad Style definition : Colours couldn't be identified");
					}
					break;
				//Booleans
				case "bold":
				case "italic":
				case "underline":
				case "strikeout":
					if ($style[$k] != -1 && $style[$k] != 0) {
						throw new \Exception("Bad Style definition : Boolean should only contains -1 or 0");
					}
					break;
				//Particular fields
				case "borderstyle":
					if ($style[$k] != 1 && $style[$k] != 3) {
						throw new \Exception("Bad Style definition : BorderStyle should only contains 1 or 3");
					}
					break;
				case "shadow":
					if ($style[$k] < 0 || $style[$k] > 4) {
						throw new \Exception("Bad Style definition : Shadow can only contains a value between 0 and 4 included");
					}
					break;
				case "alignment":
					if ($style[$k] < 1 || $style[$k] > 9) {
						throw new \Exception("Bad Style definition : Alignment can only contains a value between 1 and 9 included");
					}
					break;
			}

			//Everything is fine with this field, Let's add it to the definition
			$styleDefinition[$field] = $style[$k];
		}

		//Calculate the fontsize in %
		$height=@$this->head["PlayResX"]?@$this->head["PlayResX"]:480;
		$styleDefinition["fontsizepercent"] = $styleDefinition["fontsize"]/$height;

		//If we are here, this means that every field in the definition is correct
		$this->styles[$styleDefinition["name"]] = $styleDefinition;

	}

	/**
	 * @param $events Array List of events line to parse
	 */
	private function parseEvents($events) {
		//First exploitable line should contains field order until this line is found, we should not consider any line
		$formatDefined = false;

		foreach ($events as $event) {
			if(!@$event)continue;
			@list($type, $values) = @explode(":",$event,2);

			if(!$formatDefined){
				//If it is actually the format line, we should parse it
				if(strtolower($type) == "format"){
					$formatFieldsOrder = explode(",",trim($values));
					//We should verify that all the fields are existing fields
					foreach ($formatFieldsOrder as &$field) {
						//Remove any unwanted spaces
						$field = trim($field);
						if (array_search($field, self::$eventParamsAccepted) === false) {
							throw new \Exception("Unknown Format Parameter : ".$field);
						}
					}
					$this->eventFieldsOrder = $formatFieldsOrder;
					$formatDefined = true;
				} else {
					continue;
				}
			} else {
				$type = strtolower($type);
				switch($type){
					case "dialogue":
					case "comment":
					case "picture":
					case "sound":
					case "movie":
					case "command":
						$this->parseEvent($event);
						break;
					default:
						//Unknown Line, we don't parse it.
						continue;
				}
			}
		}
	}

	/**
	 * @param $event String the event line to parse
	 */
	private function parseEvent($event) {
		list($type,$values) = explode(":",$event,2);
		$paramCount = sizeof($this->eventFieldsOrder);

		$type = strtolower($type);
		$values = explode(",",$values,$paramCount);

		// Check if there is the correct number of fields
		if(sizeof($values) != $paramCount){
			return;
		}

		//Formatting the event
		$event = array(
			"type" => $type
		);
		for($i = 0; $i < $paramCount; $i++) {
			$event[$this->eventFieldsOrder[$i]] = $values[$i];
		}

		if($event["type"] == "dialogue" && isset($event["Style"])){
			$event["StyleData"] = $this->styles[$event["Style"]];
		}

		//Adding the event to the event queue
		$this->events[] = $event;
	}

	/**
	 * @return array
	 */
	public function getEvents() {
		return $this->events;
	}
}
?>