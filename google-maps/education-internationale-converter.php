<?php 


/* Excel Columns

0: Zone Geographique ( continent )
1: ISO_A2
2: Nom français
3: population
4: Sécurité
5: Francophonie
6: Fédération
7: Nom de l'asso
8: Abbreviation
9: OMMS / AMGE / 2
10: Date de creation
11: Adherents
12: Logo
13: Branches d'ages
14: Site web
15: Facebook
16: Twitter
17: Projets communs
18:	Projets compas
19:	Projets pioK
20: Evenements
21: Groupes SGDF présents
22: Volontaires
23: Erascout
24: Bases scouts
25: articles
26: Témoignages
27: Contacts SI

*/

class Foundry {
	function makeArray($values){
		if( preg_match("/;/", $values) ){
			return array_map('trim', explode(";", $values));
		} else {
			return (empty($values))? null : array_map('trim', explode(PHP_EOL, $values));
		}
	}
}

class GeoJSONSchema {
	var $type;
	var $features;

	function __construct(){
		$this->type 		= "FeatureCollection";
		$this->features 	= array();
	}

}

class GeoJSONFeature {
	var $type;
	var $geometry;
	var $properties;

	function __construct($data){
		$this->type 		= "Feature";
		$this->geometry 	= array(	"type" => "Point",
										"coordinates" => array(0,0,0));
		$this->properties = $data;
	}
}

class Country extends Foundry {
	var $area;
	var $iso;
	var $countryName;
	var $population;
	var $francophonie;
	var $securite;
	var $federation;
	var $associations;
	var $projects;
	var $compaProjects;
	var $pioKProjects;
	var $events;
	var $groups;
	var $volunteers;
	var $erascout;
	var $bases;
	var $articles;
	var $testimonies;
	var $contact;

	function __construct($currentArea, $row){
		$this->area 		= 	trim($currentArea) ;
		$this->iso 			= 	trim($row[1]);
		$this->countryName 	= 	trim($row[2]);
		$this->population 	= 	trim($row[3]);
		$this->securite 	= 	trim($row[4]);
		$this->francophonie	= 	$this->isFrancophone($row[5]);
		$this->federation	= 	trim($row[6]);
		$this->groups 		=	$this->makeArray($row[21]);
		$this->volunteers 	= 	$this->makeArray($row[22]);
		$this->associations = 	array();
		$this->projects 	=	$this->createNameLink($row[17]);
		$this->compaProjects=	trim($row[18]);
		$this->pioKProjects	=	trim($row[19]);
		$this->events 		=	$this->createNameLink($row[20]);
		$this->erascout 	=	trim($row[23]);
		$this->bases 		=	$this->createNameLink($row[24]);
		$this->articles 	=	$this->createNameLink($row[25]);
		$this->testimonies 	= 	$this->createNameLink($row[26]);
		$this->contact 		= 	$this->createPerson($row[27]);
	}

	function isFrancophone($value){
		switch(true){
			case preg_match("/(observateur)/i", $value):
				return "obs";
			break;
			case preg_match("/(associ)/i", $value):
				return "associate";
			break;
			case preg_match("/(membre)/i", $value):
				return "member";
			break;
			default:
				return false ;
			break;
		}
	}

	function createNameLink($values){
		$valuesArray = array();
		foreach(array_map('trim', explode(";", $values)) as $v){
			if(empty($v)){
				return null ;
			}
			array_push($valuesArray, new NameLink($v));
		}
		return $valuesArray;
	}

	function createPerson($person){
		return (empty($person))? null : new Person($person);
	}
}

class Association extends Foundry {
	var $name;
	var $abbr;
	var $affiliation;
	var $creation;
	var $members;
	var $logo;
	var $branches;
	var $website;
	var $facebook;
	var $twitter;
	
	function __construct($row){
		$this->name 		=	(empty($row[7]))? trim($row[6]) : trim($row[7]);
		$this->abbr 		=	trim($row[8]);
		$this->affiliation 	= 	$this->affiliate($row[9]);
		$this->creation 	=	trim($row[10]);
		$this->members 		=	trim($row[11]);
		$this->logo 		=	$this->findLogo($row[12]);
		$this->branches 	=	$this->makeArray($row[13]);
		$this->website 		=	trim($row[14]);
		$this->facebook 	=	trim($row[15]);
		$this->twitter 		=	trim($row[16]);
		
	}

	function affiliate($organisations){
		$organisationsArray = array();
		if(preg_match("/(OMMS)/", $organisations)){
			array_push($organisationsArray, "OMMS");
		}
		if(preg_match("/(AMGE)/", $organisations)){
			array_push($organisationsArray, "AMGE");
		}
		return $organisationsArray;
	}

	function findLogo($filename){
		$logofile = glob("./../cdn/images/Logos_WOSMWAGGS/*/".$filename.".*");
		return $logofile[0];
	}
}

class NameLink {
	var $name;
	var $link;

	function __construct($value){
		$value = array_map('trim', explode(PHP_EOL, $value));
		$this->name = $value[0];
		$this->link = $value[1];
	}
}

class Person {
	var $name;
	var $email;
	var $phone;

	function __construct($value){
		$value = array_map('trim', explode(PHP_EOL, $value));
		$this->name 	= 	$value[0];
		$this->email 	= 	$value[1];
		$this->phone 	= 	$value[2];
	}
}


// php_sapi_name() returns 'cli' if the script is runned from a shell or terminal or crontab
if(	php_sapi_name() != "cli" &&
	$_SERVER['REMOTE_ADDR'] != '127.0.0.1' &&
	$_SERVER['REMOTE_ADDR'] != '192.168.50.1' &&
   	$_SERVER['REMOTE_ADDR'] != '84.55.161.8' &&   	//Sortie Internet SGDF
   	$_SERVER['REMOTE_ADDR'] != '146.185.40.1'     	//Oxalide
){
    header('HTTP/1.0 403 Forbidden');
    echo 'IP : '.$_SERVER['REMOTE_ADDR'];
    die('<h1>Vous n\'avez pas le droit d\'acc&eacute;der à cette ressource&nbsp;!</h1>');
}else{

	try {
		$excelFile = glob('./../cdn/data/*.xlsx');
		require_once("xllib/php-excel-reader/excel_reader2.php");
		require_once("xllib/SpreadsheetReader.php");

		$reader = new SpreadsheetReader($excelFile[0]);
		if( !$reader ) {
			throw new Exception("Couldn't find excel file.");
		}

		$rowNumber = 0 ;

		$json = new GeoJSONSchema();

		$current_country = null ;
		$current_area = null ;

		foreach($reader as $row){
			if($rowNumber > 1){
				
				if( !empty($row[0]) ){ // New region
					$current_area = $row[0];
				}

				if( !empty($row[1]) ){	// New country
					if( !empty($current_country) ){
						$feature = new GeoJSONFeature($current_country);
						array_push($json->features, $feature);
					}
					$current_country = new Country($current_area, $row) ;					
				}

				if( !empty($row[6]) || !empty($row[7]) ){ // New association
					$asso = new Association($row);
					array_push($current_country->associations, $asso);
					if(empty($row[7])){
						$current_country->federation = null ;
					}
				}
			}
			$rowNumber ++ ;
		}


		$filepaths = array('./../cdn/data/', '/space/www/www.sgdf.fr/data/htdocs/templates/sgdf_design_chefs_cadres_2013/includes/');
		$filename = "sgdf-international.json";
		foreach( $filepaths as $fp ){
			if( file_exists($fp) ){
				if(file_put_contents($fp.$filename, json_encode($json))){
					if(!chmod($fp.$filename, 0777)){
						throw new Exception("Couldn't change rights on file : " . $fp.$filename);
					}
				} else {
					throw new Exception("Couldn't save content to file : " . $fp.$filename);
				}
			} else {
				throw new Exception("Filepath : " . $fp . " doesn't exists.");
			}
		}

	} catch (Exception $e){
		mail (	'informatique@sgdf.fr', 
				"Google Maps Education Internationale - Exception", 
				"Exception in file ". $e->getFile() ." line ". $e->getLine(). " : \n". $e->getMessage() );
		die("Exception in file ". $e->getFile() ." line ". $e->getLine(). " : \n". $e->getMessage() );
	}

	mail (	'informatique@sgdf.fr', 
			"Google Maps Education Internationale - Everything is okay", 
			"The data update went okay" );
	echo "Everything is okay" ;
}


?>