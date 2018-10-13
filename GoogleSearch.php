<?php
/* GoogleSearch.php (CLI Only)

| Author		: Muhammad Rakha Firjatullah
| Version		: 2.1 RELEASE
| 
| Email			: nonstop.hacking.free@gmail.com
| Github		: https://github.com/GoogleX133

BIG THANKS TO Teguh Aprianto (https://www.facebook.com/Teguhmicro)
*/

class GoogleSearch {
	
	public $results;
	/* kalau ganti pengaturan jangan sampai salah/tertukar tipe-nya (boolean atau string) */
	public $settings = array(
		"allPagesOutput" => true, // true | false (if false, will only show the first page results)
		"showTitle" => true, // true | false
		"showUrl" => true, // true | false
		"showUrlType" => "url", //formattedUrl | unescapedUrl | visibleUrl | url
		"showDesc" => true, // true | false
		"showThumbnailUrl" => false, // true | false
		"onScreenOutput" => false // true | false (if true, the result will show up on your screen)
	);
	
	/* utility */
	function ambilKata($param, $kata1, $kata2){
		if(strpos($param, $kata1) === FALSE) return FALSE;
		if(strpos($param, $kata2) === FALSE) return FALSE;
		$start = strpos($param, $kata1) + strlen($kata1);
		$end = strrpos($param, $kata2, $start);
		$return = substr($param, $start, $end - $start);
		return $return;
	}
	
	function getToken(){
		$groups = [];
		$base_url = "https://cse.google.com/cse.js?cx=partner-pub-2698861478625135:3033704849";
		preg_match_all("/cse_token\":.*?\"(.*?)\"/mi", file_get_contents($base_url), $matches, PREG_SET_ORDER, 0);
		
		if(empty($matches)){
			echo "Err...";exit();
		}
		$full = $matches;
				
		for($i = 0; $i<count($matches); $i++){
			if(!empty($matches[$i][1])) array_push($groups,$matches[$i][1]);
		}
				
		return array($full,$groups);
	}
	
	function search($query){
		$pages = [];
		$cseToken = $this->getToken();
		if(!$cseToken[0]){
			echo "\n[!] Error, can't get token";exit();
		}
		$base_url = "https://cse.google.com/cse/element/v1?num=10&hl=en&cx=partner-pub-2698861478625135:3033704849&safe=off&cse_tok=".$cseToken[1][0]."&start={page_no}&q={query}&callback=x";
		
		$temporary_url = str_replace("{query}",urlencode($query),$base_url);
		$clearCB = $this->ambilKata(file_get_contents(str_replace("{page_no}","0",$temporary_url)), "x(",');');
		$res = json_decode($clearCB);
		if($this->settings['allPagesOutput']){
			$this->results = [];
			for($i = 0; $i<count($res->cursor->pages); $i++){
				array_push($pages,$res->cursor->pages[$i]->start);
			}
			
			for($i = 0; $i<count($pages); $i++){
				$clearCB = $this->ambilKata(file_get_contents(str_replace("{page_no}",$pages[$i],$temporary_url)), "x(",');');
				$res = json_decode($clearCB);
				file_put_contents('GoogleSearch-'.$i.'.txt', $clearCB);
				for($iix = 0; $iix<count($res->results); $iix++){
					array_push($this->results,$res->results[$iix]);
				}
			}
		}else{$this->results = $res->results;}
	}
	
	function changeSettings($settings){
		foreach($settings as $key => $item){
			$item = preg_replace("/(false)/mi","0",$item);
			if(array_key_exists($key,$this->settings)){$this->settings[$key] = (preg_match('/(true)|(0)/', $item)) ? (bool)$item : $item;}
		}
	}
	
	function showResult($results){
		$content = "";
		$rx = rand(1000,100000);
		$type = $this->settings['showUrlType'];
		
		//var_dump($results);
		for($i = 0; $i<count($results); $i++){
			$content .= "\n\n========================================\n\n";
			if($this->settings['showTitle']) $content .= "Title: ".$results[$i]->titleNoFormatting;
			if($this->settings['showUrl']) $content .= "\nUrl: ".$results[$i]->$type;
			if($this->settings['showDesc']) $content .= "\nDescription: ".$results[$i]->contentNoFormatting;
			if($this->settings['showThumbnailUrl']) $content .= "\nThumbURL: ".$results[$i]->richSnippet->cseImage->src;
			$content .= "\n\n========================================\n\n";
		}
		
		if($this->settings['onScreenOutput']){echo $content;}else{
			file_put_contents('GoogleSearch-'.$rx.'.txt', $content);
			echo "\n [!] Your results are saved to GoogleSearch-".$rx.".txt";
		}
	}
	
	public function waitForUserInput($term,$type){
		echo $term;
		switch($type){
			case 'boolean':
				$res = trim(fgets(STDIN));
				if(stristr($res, "y") !== false || stristr($res, "true") !== false){
					return true;
				}elseif(stristr($res, "n") !== false || stristr($res, "false") !== false){
					return false;
				}else{
					echo "Err..."; exit();
				}
			break;
			case 'custom':
				return trim(fgets(STDIN));
			break;
			default:
				echo "Err..."; exit();
		}
	}
	
	/* menu */
	function settingsMenu(){
		$options = [];
		$i = 1;
		
		echo "\n\n========================================\n\nPlease select to change:\n\n";
		foreach($this->settings as $key => $item){
			array_push($options,$key);
			echo $i.".) ".$key." => ";
			if(gettype($item)=="boolean"){echo $item ? "true (bool)\n" : "false (bool)\n";}else{echo $item." (str)\n";}
			$i++;
		}
		echo "99.) Go Back\n";
		echo "\n***! BEWARE ! BEWARE ! BEWARE !***";
		$getui = $this->waitForUserInput("\n>>> ","custom");
		if($getui > count($options) || !$getui){$this->mainMenu();}
		$getuix = $this->waitForUserInput("\nInput new value: ","custom");
		$settings = array($options[$getui-1] => $getuix);
		$this->changeSettings($settings);
		$this->mainMenu();
	}
	
	function searchMenu(){
		echo "\n\n========================================\n\nI'm ready to search anything !,\nPlease input your query below";
		$getui = $this->waitForUserInput("\n\n>>> ","custom");
		echo "\n[!] Please Wait.....";
		$this->search($getui);
		$this->showResult($this->results);
		$this->waitForUserInput("\n\n>>> Press enter to go back to the main menu !","custom");
		$this->mainMenu();
	}
	
	public function mainMenu(){
		$getui = $this->waitForUserInput("\n\n========================================\n\nWelcome human!,\nPlease select menu below here:\n\n1.) Search Now\n2.) Change Settings\n\n>>> ","custom");
		if($getui == 1){
			$this->searchMenu();
		}elseif($getui == 2){
			$this->settingsMenu();
		}else{echo "?";exit();}
	}
}

$lib = new GoogleSearch();

cli_set_process_title("GoogleSearch V.2.1");
$lib->mainMenu();
?>
