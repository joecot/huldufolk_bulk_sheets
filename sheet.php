<?php
require './vendor/autoload.php';
use mikehaertl\pdftk\Pdf;
$auth_config_path = '/home/joe/Downloads/client_id.json';
$client = getGoogleClient($auth_config_path);
$googleSheetsService = new Google_Service_Sheets($client);
$spreadsheetId = '1p1D-lajKVf0JileDWoDgwcY-HDM50zmBtwtBX5_RUFw';
$pdfdir = 'PDFs_PAX';
$formpdf = 'Huldufolkacrformpax2.pdf';

$characters = getCharacters();
$crews = getCrews();
$factions = getFactions();

foreach($characters as $character){
		print_r($character);
		if(empty($character['Code']) || empty($character['CrewCode']) || empty($character['Quality1'])) continue;
		print_r($character);
        generateCharacter($character);
}


function getCharacters(){
	global $googleSheetsService,$spreadsheetId;
	
	$range = 'Sheets!A1:AD72';
	$response = $googleSheetsService->spreadsheets_values->get($spreadsheetId, $range);
	$sheets_sheet = $response->getValues();

	if (empty($sheets_sheet)) {
		die('Failed getting characters sheet');
	}

	$characters = Array();
	$headers = array_shift($sheets_sheet);
	$header_count = count($headers);
	
	$row = 1;
	foreach($sheets_sheet as $line){
		$row++;
		$line = array_pad($line,$header_count,'');
		$character = array_combine($headers,$line);
		if(empty($character['Quality1'])) continue;
		$character['sheet_row'] = $row;
		$characters[$character['Code']] = $character;
	}
	//now character descriptions
	$range = 'Characters!A1:P72';
	$response = $googleSheetsService->spreadsheets_values->get($spreadsheetId, $range);
	$sheets_sheet = $response->getValues();
	$headers = array_shift($sheets_sheet);
	$header_count = count($headers);
	$powerCodeColNum = array_search('PowersLinkCode',$headers);
	$powerCodeCol = false;
	if($powerCodeColNum){
		$powerCodeCol = chr(65+$powerCodeColNum);
	}
	$row = 1;
	foreach($sheets_sheet as $line){
		$row++;
		$line = array_pad($line,$header_count,'');
		$character = array_combine($headers,$line);
		if(empty($character['Code']) || empty($characters[$character['Code']])) continue;
		if(empty($character['CrewCode'])){
			unset($characters[$character['Code']]);
			continue;
		}
		$character['PowerCodeColumn'] = false;
		$character['character_row'] = $row;
		if($powerCodeCol) $character['PowerCodeColumn'] = $powerCodeCol.$character['character_row'];
		$characters[$character['Code']] = array_merge($characters[$character['Code']],$character);
		
	}
	return $characters;
}

function getCrews(){
	global $googleSheetsService,$spreadsheetId;
	$crews = Array();
	$range = 'Crews!A1:H19';
	$response = $googleSheetsService->spreadsheets_values->get($spreadsheetId, $range);
	$crews_sheet = $response->getValues();

	if (empty($crews_sheet)) {
		die('Failed getting crews sheet');
	}
	$headers = array_shift($crews_sheet);
	$header_count = count($headers);
	foreach($crews_sheet as $line){
		$line = array_pad($line,$header_count,'');
		$crew = array_combine($headers,$line);
		if(empty($crew['CrewCode'])) continue;
		$crews[$crew['CrewCode']] = $crew;
	}
	return $crews;
}

function getFactions(){
	global $googleSheetsService,$spreadsheetId;
	$factions = Array();
	$range = 'Factions!A1:F8';
	$response = $googleSheetsService->spreadsheets_values->get($spreadsheetId, $range);
	$factions_sheet = $response->getValues();

	if (empty($factions_sheet)) {
		die('Failed getting factions sheet');
	}
	$headers = array_shift($factions_sheet);
	$header_count = count($headers);
	foreach($factions_sheet as $line){
		$line = array_pad($line,$header_count,'');
		$faction = array_combine($headers,$line);
		if(empty($faction['Faction'])) continue;
		$factions[$faction['Faction']] = $faction;
	}
	return $factions;
}

function generateCharacter($character){
	global $characters,$crews,$pdfdir,$formpdf;
	//$qualities = Array('Alacrity','Benevolence','Conviction','Cunning','Defiance','Devotion','Endurance','Precision','Regality','Resilience','Tenacity','Valor','Vigor','Wariness','Wisdom','Zeal');
	//$affinities = Array('Computers','Diplomacy','Driving','Stealth','SwordFighting');
	$spheres = Array('Creation','Destruction','Conquest','Dominion','Service');
	$paths = Array('LesserCreation','Substance','Life','LesserDestruction','Change','Strife','LesserConquest','Empire','Infiltration','LesserDominion','Autocracy','Demesne','LesserService','Alleviation','Augmentation');
	$types = Array('Hidden Born', 'Lost Twin', 'Stolen');
	$pdf = new Pdf(Array('A' => $formpdf));
	$formFields = Array();
	$formFields['Code'] = $character['CrewCode'].'-'.$character['Code'];
	if(!empty($character['PlayerName'])) $formFields['Player_Name'] = $character['PlayerName'];
	if(!empty($character['CharacterName'])) $formFields['Character_Name'] = $character['CharacterName'];
	if(!empty($character['Faction'])) $formFields['Faction'] = $character['Faction'];
	if(!empty($character['Type']) && in_array($character['Type'],$types)) $formFields['Type'] = $character['Type'];
	
	$customQualities = 0;
	for($i = 1; $i<=3; $i++){
		if(!empty($character['Quality'.$i])){
            $customQualities++;
            $formFields['Qualities_Custom'.$customQualities] = 'Yes';
            $formFields['Qualities_Text_Custom'.$customQualities] = $character['Quality'.$i];
		}
	}
	$customAffinities = 0;
	for($i = 1; $i <=5; $i++){
		if($i<=2) $field = 'FocusedAffinity';
		else $field = 'Affinity';
		if(empty($character[$field.$i])) continue;  
        $customAffinities++;
        $formFields['Affinities_Custom'.$customAffinities] = $character[$field.$i];
        if($i<=2) $formFields['Affinities_Focus_Custom'.$customAffinities] = 'Yes';
	}
	if(!empty($character['Belief_Rank'])&& ctype_digit((string)$character['Belief_Rank']) && $character['Belief_Rank'] < 6){
		for($belieflevel = 1; $belieflevel <= $character['Belief_Rank']; $belieflevel++){
			for($j = 1; $j <= 3; $j++){
				$formFields['Belief_'.$belieflevel.'_'.$j] = 'Yes';
			}
		}
	}
	if(!empty($character['SphereFocus']) && in_array($character['SphereFocus'],$spheres)){
		$formFields['Sphere_Focus_'.$character['SphereFocus']] = 'Yes';
	}
	foreach($paths as $path){
		if(!empty($character[$path]) && ctype_digit((string)$character[$path]) && $character[$path] < 6){
			for($j = 1; $j <= $character[$path]; $j++){
				$formFields['Path_'.$path.'_'.$j] = 'Yes';
			}
		}
	}

	$link = getPowersLink($character,$paths);
	$character['PowersLinkCode'];
	if($link){
		$formFields['Powers_Link'] = $link;
		if(empty($character['PowersLinkCode']) || $character['PowersLinkCode'] != $link){
			$character['PowersLinkCode'] = $link;
			updatePowerCode($character);
		}
	}
	print_r($formFields);
	//exit;
	if(!file_exists($pdfdir.'/'.$character['CrewCode'].'-'.$character['Code'].'-1-sheet.pdf')){
		$pdf->fillForm($formFields)
		->flatten();
		//$pdf->addFile('sheetback.pdf','B');
		//$pdf->cat(1,null,'A')
		//->cat(1,null,'B')
			//->needAppearances()
		//$pdf2 = new pdf()
		$pdf->saveAs($pdfdir.'/'.$character['CrewCode'].'-'.$character['Code'].'-1-sheet.pdf');
	}
	generateBackground($character);
}

function generateBackground($character){
	global $characters,$crews,$factions,$pdfdir;
	$pdf = getcwd().'/'.$pdfdir.'/'.$character['CrewCode'].'-'.$character['Code'].'-2-background.pdf';
	if(!file_exists($pdf)){
		ob_start();
		include('./background.php');
		$background = ob_get_clean();
		file_put_contents('./background.html',$background);
		$url = 'file://'.getcwd().'/background.html';
		generatePdf($pdf, $url);
		if(file_exists($pdf)) echo "background PDF created!\n";
		else die("Error generating background PDF\n");
	}
}

function updatePowerCode($character){
	global $googleSheetsService,$spreadsheetId;
	if(!$character['PowerCodeColumn'] || empty($character['PowersLinkCode'])) return;
	echo "can update PowerCodeColumn ".$character['PowerCodeColumn']."\n";
	$updateRange = 'Characters!'.$character['PowerCodeColumn'];
	$updateBody = new \Google_Service_Sheets_ValueRange([
		'range' => $updateRange,
		'majorDimension' => 'ROWS',
		'values' => ['values' => $character['PowersLinkCode']],
	]);
	$googleSheetsService->spreadsheets_values->update(
		$spreadsheetId,
		$updateRange,
		$updateBody,
		['valueInputOption' => 'USER_ENTERED']
	);
	sleep(1);
	return;
}

function getPowersLink($character,$paths){
	global $pdfdir;
	$i = 0;
	$vars = Array();
	foreach($paths as $path){
		$i++;
		if(!empty($character[$path]) && ctype_digit((string)$character[$path]) && $character[$path] < 6){
			$vars['path'][$i] = $character[$path];
		}else{
			$vars['path'][$i] = 0;
		}
	}
	print_r($vars);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,"https://thehuldufolk.com/powers/");
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($vars));
	//curl_setopt($ch, CURLOPT_VERBOSE, true);
	$server_output = curl_exec($ch);
	curl_close($ch);
	$headers = false;
	$link = false;
	if($server_output){
		$headers = getCurlHeaders($server_output);
	}
	if($headers && !empty($headers['Location']) && substr($headers['Location'],0,8) == '/powers/'){
		list(,,$link) = explode('/',$headers['Location']);
		$pdf = getcwd().'/'.$pdfdir.'/'.$character['CrewCode'].'-'.$character['Code'].'-3-powers-'.$link.'.pdf';
		if(!file_exists($pdf)){
			$url = "https://thehuldufolk.com".$headers['Location'];
			generatePdf($pdf, $url);
			if(file_exists($pdf)) echo "powers PDF created!\n";
			else die("Error generating powers PDF\n");
		}
	}
	return $link;
}

function getCurlHeaders($output){
	$headers = [];
	$data = explode("\n",$output);
	$headers['status'] = $data[0];
	array_shift($data);

	foreach($data as $part){
		$middle=explode(":",$part);
		if(!empty($middle) && count($middle)>1) $headers[trim($middle[0])] = trim($middle[1]);
	}
	return $headers;
}
function getGoogleClient($auth_config_path){
	$client = new Google_Client();
	$client->setApplicationName('Google Sheets API PHP Quickstart');
	$client->setScopes(Google_Service_Sheets::SPREADSHEETS);
	$client->setAuthConfig($auth_config_path);
	$client->setAccessType('offline');
	$client->setPrompt('select_account consent');
	// Load previously authorized token from a file, if it exists.
	// The file token.json stores the user's access and refresh tokens, and is
	// created automatically when the authorization flow completes for the first
	// time.
	$tokenPath = 'token.json';
	if (file_exists($tokenPath)) {
		$accessToken = json_decode(file_get_contents($tokenPath), true);
		$client->setAccessToken($accessToken);
	}

	// If there is no previous token or it's expired.
	if ($client->isAccessTokenExpired()) {
		// Refresh the token if possible, else fetch a new one.
		if ($client->getRefreshToken()) {
			$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
		} else {
			// Request authorization from the user.
			$authUrl = $client->createAuthUrl();
			printf("Open the following link in your browser:\n%s\n", $authUrl);
			print 'Enter verification code: ';
			$authCode = trim(fgets(STDIN));

			// Exchange authorization code for an access token.
			$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
			$client->setAccessToken($accessToken);

			// Check to see if there was an error.
			if (array_key_exists('error', $accessToken)) {
				throw new Exception(join(', ', $accessToken));
			}
		}
		// Save the token to a file.
		if (!file_exists(dirname($tokenPath))) {
			mkdir(dirname($tokenPath), 0700, true);
		}
		file_put_contents($tokenPath, json_encode($client->getAccessToken()));
	}
	return $client;

}

function generatePdf($file, $location){
	$exec = "node pdf.js '$file' '$location'";
	echo $exec."\n";
	exec($exec);
}

function getCharacterName($code, $fullTitle=FALSE){
	global $characters,$crews;
	//if(!isset($characters[$code])) die('Invalid character code: '.$code);
	if(!isset($characters[$code])) return "XXMISSINGCHARXX";
	$character = $characters[$code];
	$name = preg_replace('/[ ]+/',' ',$character['CharacterName']);
	$title = $name;
	if($fullTitle){
		$title .= " ($character[Faction]";
		if(isset($crews[$character['CrewCode']])) $title.=", <strong>".$crews[$character['CrewCode']]['Name']."</strong>";
		$title .=")";
	}
	return $title;
}

function getCrewName($code, $fullTitle=FALSE){
	global $characters,$crews;
	//if(!isset($characters[$code])) die('Invalid character code: '.$code);
	if(!isset($crews[$code])) return "XXMISSINGCREWXX";
	$crew = $crews[$code];
	$title = "$crew[Name]";
	if($fullTitle){
		$title .= " (Led by ".getCharacterName($crew['Leader']).")";
	}
	return $title;
}

function parseText($text){
	static $preg = false;
	if(!$preg) $preg = '/'.preg_quote('[').'([^]]+)]/';
	$text = preg_replace_callback($preg,'parseCode',$text);
	$text = nl2br($text);
	return $text;
}
function parseCode($match){
	$code = $match[1];
	$full = false;
	$bold = false;
	if(substr($code,-1) == '*'){
		$bold = true;
		$code = substr($code,0,-1); //pull * off the end;
	}
	if(substr($code,-1) == '+'){
		$full = true;
		$code = substr($code,0,-1); //pull + off the end;
	}
	if(ctype_digit(substr($code,-1))){ //this is a character
		$text = getCharacterName($code, $full);
	}else{//this is a crew
		$text = getCrewName($code,$full);
	}
	if($bold) $text = "<strong>".$text."</strong>";

	return $text;
}