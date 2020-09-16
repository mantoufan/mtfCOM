<?php
/**
 * Created by PhpStorm.
 * User: Naouak
 * Date: 15/03/2015
 * Time: 21:26
 */

namespace LibPHPAss;


class WebVTT {
	private $metadatas = array();
	private $events = array();

	/**
	 * WebVTT constructor.
	 * @param array $events
	 */
	public function __construct(array $events) {
		$this->events = $events;
	}

    private static function formatLine($line)
    {
        $line = preg_replace_callback("/\\{\\\\([^\\}]*)\\}/", function($matches){
            $tags = array(
                "i1" => "i",
                "i0" => "/i",
                "i" => "/i"
            );

            if(!isset($tags[$matches[1]])){
                return "";
            }

            return "<".$tags[$matches[1]].">";
        }, $line);
        return $line;
    }

    private static function formatText($text)
    {
        $text = str_replace("\\N","\n", $text);

        $lines = explode("\n", $text);

        foreach ($lines as $key => $line) {
            $lines[$key] = self::formatLine($line);
        }

        $text = implode("\n", $lines);
        return $text;
    }


    private static function formatPercent($percent) {
		return round($percent*100,2)."%";
	}

	/**
	 * Stringify the object in WebVTT
	 */
	public function toString(){
		//File Header
		$result = "WEBVTT\n";

		//Metadatas
		foreach($this->metadatas as $key => $value){
			$result.=$key.":".$value."\n";
		}
		//End of Metadata
		$result.="\n";

		foreach($this->events as $event){
			$cue = $this->formatEvent($event);
			if(strlen(trim($cue))){
				$result.=$cue."\n";
			}
		}

		return $result;
	}

	private function formatEvent($event) {
		$cue = "";
		//Cues
		if($event["type"]=="dialogue"){
			//Timings
			$cue.=self::fromASSTimeToWebVTTTime($event["Start"])." --> ".self::fromASSTimeToWebVTTTime($event["End"]);

			//Cue settings
			$cue_settings = array();

			$alignement = $event["StyleData"]["alignment"];
			//Horizontal position
			$cue_setting="line:";
			if($alignement%3==0){
				$cue_setting.="end";
			} else if($alignement%3==1){
				$cue_setting.="start";
			} else {
				$cue_setting.="middle";
			}
			$cue_settings[] = $cue_setting;

			//Vertical position
			$cue_setting="position:";
			if($alignement<=3){
				$cue_setting.="start";
			} else if($alignement<=6){
				$cue_setting.="middle";
			} else {
				$cue_setting.="end";
			}
			$cue_settings[] = $cue_setting;

			//FontSize
			$cue_setting="size:".self::formatPercent($event["StyleData"]["fontsizepercent"]);
			$cue_settings[] = $cue_setting;

			//Alignment of the text
			//Property name : "align"
			//Values : start, middle, end, left, right

			//Region
			//Needs to study the spec for this one so not yet.

			$cue.=" ".implode(" ",$cue_settings);
			$cue.="\n";

			//Payload (the text of the subtitle)
			//@todo Text formatting
			$cue.=self::formatText($event["Text"]);
			$cue.="\n";
		}

		return $cue;
	}

	public static function fromASSTimeToWebVTTTime($time){
		return "0".$time."0";
	}
}
?>