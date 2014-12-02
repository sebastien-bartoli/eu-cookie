<?php 

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

	$now = getdate();
	$season = ($now['mon'] >= (int)9)? (int)$now['year'] + 1 : (int)$now['year'] ;
	
	try{

		$dsn = 'dblib:host=46.105.115.173;dbname=sgdf' ;

		$pdo_db = new PDO(	$dsn, 
							'visiteur_sgdf', 
							'fh!e654er');
	} catch (PDOException $e){
		
		mail (	'informatique@sgdf.fr', 
				"Google Maps groups data Updater - Exception", 
				"PDOException in file ". $e->getFile() ." line ". $e->getLine(). " : \n". $e->getMessage() . "\n PDO couldn't connect with DSN " . $dsn );
		die("PDOException in file ". $e->getFile() ." line ". $e->getLine(). " : \n". $e->getMessage());
	}	
	
	try{

		$pdo_query = $pdo_db->prepare("	SELECT DISTINCT
											STR.CODE_STRUCTURE AS 'code_structure' ,
                                            STR.NOM AS 'nom_structure',
                                            STR.CODE_POSTAL AS 'code_postal_structure', 
                                            STR.MUNICIPALITE AS 'municipalite_structure',
                                            COALESCE(STR.TELEPHONE, '') AS 'telephone_structure',
                                            COALESCE(STR.COURRIEL, '') AS 'courriel_structure',
                                            COALESCE(STR.WEB, '') AS 'web_structure', 
                                            DA.NOM AS 'departement_administratif',
                                            STR.LATITUDE AS 'lat',
                                            STR.LONGITUDE AS 'lng',
                                            STR_CR.CODE_STRUCTURE AS 'code_structure_cr',
                                            STR_CR.NOM AS 'nom_cr',
                                            COALESCE(STR_CR.TELEPHONE, '') AS 'telephone_cr',
                                            COALESCE(STR_CR.COURRIEL, '') AS 'courriel_cr'
                                       	FROM STRUCTURE STR 
                                        INNER JOIN TYPE_STRUCTURE TSTR 
                                        	ON STR.ID_TYPE_STRUCTURE = TSTR.ID_TYPE_STRUCTURE 
                                        	AND TSTR.LIBELLE = 'Groupe'  
                                        INNER JOIN DEPARTEMENT_ADMINISTRATIF DA
                                        	ON DA.ID_DEPARTEMENT_ADMINISTRATIF = STR.ID_DEPARTEMENT_ADMINISTRATIF
                                        INNER JOIN STRUCTURE STR_PARENT
                                        	ON STR_PARENT.ID_STRUCTURE = STR.ID_PARENT
                                        INNER JOIN STRUCTURE_DELEGUEE SD
                                        	ON SD.ID_STRUCTURE_DELEGUEE = STR_PARENT.ID_STRUCTURE
                                        INNER JOIN STRUCTURE STR_CR
                                        	ON STR_CR.ID_STRUCTURE = SD.ID_STRUCTURE_RESPONSABLE
                                        	AND STR_CR.NOM LIKE 'CENTRE DE RESSOURCES%'
                                        WHERE    
                                        	STR.ID_SAISON = :season
                                        	AND STR.STATUT = '0'
                                        ORDER BY STR_CR.CODE_STRUCTURE ");
		
		$pdo_query->bindParam(':season', $season);
		$pdo_query->execute();

		$json_schema = array(	"type" 		=> "FeatureCollection", 
								"features" 	=> array()
						);
		$json_feature_model = array(	"type" 			=> 	"Feature", 
										"geometry"		=>	array(
																"type"			=>	"Point",
																"coordinates"	=>	array(0,0,0)
															),
										"properties"	=>	array(
																"nom" 			=> 	null,
																"telephone"		=>	null,
																"courriel"		=>	null,
																"web"			=> 	null,
																"latitude"		=>	null,
																"longitude"		=>	null,
																"nom_cr"		=> 	null,
																"telephone_cr"	=>	null,
																"courriel_cr"	=>	null
															)
								);

		while($groupe = $pdo_query->fetch(PDO::FETCH_OBJ)){

			$groupe_model = $json_feature_model ;
			foreach($groupe as $element){
				$element = (empty($element) || $element === " " )? null : trim($element) ;
			}
			$url = $groupe->web_structure ;

			if( !preg_match('/^((https?):\/\/).+\..{2,20}(\/.+)?\/?$/', $url) && $url !== " " && $url !== null || strlen($url) == 50 ){
				$url = (preg_match('/^\s?((https?):\/\/)/', $url))? trim($url) : "http://" . trim($url);				
			}
			
			$groupe_model['geometry']['coordinates'][1] = $groupe_model['properties']['latitude'] = (float)$groupe->lat ;
			$groupe_model['geometry']['coordinates'][0] = $groupe_model['properties']['longitude'] = (float)$groupe->lng ;
			$groupe_model['properties']['nom'] = (string)$groupe->nom_structure ;
			$groupe_model['properties']['telephone'] = (string)$groupe->telephone_structure ;
			$groupe_model['properties']['courriel'] = (string)$groupe->courriel_structure ;
			$groupe_model['properties']['web'] = (string)$url ;
			$groupe_model['properties']['nom_cr'] = (string)$groupe->nom_cr ;
			$groupe_model['properties']['telephone_cr'] = (string)$groupe->telephone_cr ;
			$groupe_model['properties']['courriel_cr'] = (string)$groupe->courriel_cr ;

			array_push($json_schema['features'], $groupe_model);
		}


		$filepaths = array('/space/www/ws.sgdf.fr/data/htdocs/cdn/data/', '/space/www/www.sgdf.fr/data/htdocs/templates/sgdf_design_2013/includes/');
		$filename = "groupes_sgdf.json";
		foreach( $filepaths as $fp ){
			if( file_exists($fp) ){
				if(file_put_contents($fp.$filename, json_encode($json_schema))){
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

	} catch(PDOException $e){
		mail (	'informatique@sgdf.fr', 
				"Google Maps groups data Updater - Exception", 
				"PDOException in file ". $e->getFile() ." line ". $e->getLine(). " : \n". $e->getMessage() );
		die("PDOException in file ". $e->getFile() ." line ". $e->getLine(). " : \n". $e->getMessage() );
	} catch (Exception $e){
		mail (	'informatique@sgdf.fr', 
				"Google Maps groups data Updater - Exception", 
				"Exception in file ". $e->getFile() ." line ". $e->getLine(). " : \n". $e->getMessage() );
		die("Exception in file ". $e->getFile() ." line ". $e->getLine(). " : \n". $e->getMessage() );
	}

	mail (	'informatique@sgdf.fr', 
			"Google Maps groups data Updater - Everything is okay", 
			"The data update went okay" );
	echo "Everything is okay" ;

}
?>