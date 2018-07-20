<?php
/* GoogleSearch.php (CLI Only)

| Author		: Muhammad Rakha Firjatullah
| Version		: 1.1 RELEASE
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
	function search($query){
		$searchSite;
		$pages = [];
		$base_url = "https://www.googleapis.com/customsearch/v1element?key=AIzaSyCVAXiUzRYsML1Pv6RwSG1gunmMikTzQqY&num=10&hl=en&start={page_no}&cx=partner-pub-2698861478625135:3033704849&cse_tok=AF14hlgzuKg572zAVU4KcBDcWNoTAuMJsA:1532103127571&q={query}";
		
		$temporary_url = str_replace("{query}",urlencode($query),$base_url);
		$res = json_decode(file_get_contents(str_replace("{page_no}","0",$temporary_url)));
		if($this->settings['allPagesOutput']){
			$this->results = [];
			for($i = 0; $i<count($res->cursor->pages); $i++){
				array_push($pages,$res->cursor->pages[$i]->start);
			}
			
			for($i = 0; $i<count($pages); $i++){
				$res = json_decode(file_get_contents(str_replace("{page_no}",$pages[$i],$temporary_url)));
				for($iix = 0; $iix<count($res->results); $iix++){
					array_push($this->results,$res->results[$iix]);
				}
			}
		}else{$this->results = $res->results;}
	}
	
	function changeSettings($settings){
		foreach($settings as $key => $item){
			if(array_key_exists($key,$this->settings)){$this->settings[$key] = $item;}
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
		$this->waitForUserInput("\n\n>>> Press any key to go back to the main menu !","custom");
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

cli_set_process_title("GoogleSearch V.1.1");
$lib->mainMenu();
?>
