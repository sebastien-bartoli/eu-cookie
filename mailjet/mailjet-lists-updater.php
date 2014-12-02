<?php 



/*

20-06-2014

Script PHP mise à jour automatique des lists MailJet depuis les données de l'intranet SGDF



Sebastien Bartoli

*/



// php_sapi_name() returns 'cli' if the script is runned from a shell or terminal or crontab

if(	php_sapi_name() != "cli" &&

	$_SERVER['REMOTE_ADDR'] != '127.0.0.1' &&

   	$_SERVER['REMOTE_ADDR'] != '84.55.161.8' &&   	//Sortie Internet SGDF

   	$_SERVER['REMOTE_ADDR'] != '146.185.40.1'     	//Oxalide

){

    header('HTTP/1.0 403 Forbidden');

    echo 'IP : '.$_SERVER['REMOTE_ADDR'];

    die('<h1>Vous n\'avez pas le droit d\'acc&eacute;der à cette ressource&nbsp;!</h1>');

}else{



	// Import and initialize Mailjet API (apikey, secret)

	require_once('lib/php-mailjet.class-mailjet-0.1.php');

	$mjapi = (object) new Mailjet(	'277ea49f4e17b1f5179f489df81bc711', 

									'b24eb480245535777ef535d2919d504d');



	// Array of the lists we will work on

	// Query contains the function code from the SGDF Intranet SQL

	// Separate queries code by a comma ','

	$working_lists = array(	(object) array(	'id'	=>	600659,  	// SAGAT - AP

											'query'	=>	'600'),

							(object) array(	'id'	=>	600664,  	// SAGAT - AUMONIERS

											'query'	=>	'302,502'),

							(object) array(	'id'	=>	600661,  	// SAGAT - CHEFS

											'query'	=>	'293,260,232G,235G,270,290,210,210M,211P,212P,213,213M,214P,215P,240,241,242,230,230M,233,233M,250,251,220,220M,223,223M'),

							(object) array(	'id'	=>	600638,  	// SAGAT - DT 

											'query'	=>	'500'),

							(object) array(	'id'	=>	600649,  	// SAGAT - DTA

											'query'	=>	'501'),

							(object) array(	'id'	=>	600651,  	// SAGAT - RG 

											'query'	=>	'300'),

							(object) array(	'id'	=>	600654,  	// SAGAT - RGA

											'query'	=>	'301'),

							(object) array(	'id'	=>	600650,  	// SAGAT - RPAF

											'query'	=>	'505'),

							(object) array(	'id'	=>	600665,  	// SAGAT - RPP

											'query'	=>	'503'),

							(object) array(	'id'	=>	600658,  	// SAGAT - SECRETAIRES

											'query'	=>	'307,670'),

							(object) array(	'id'	=>	600656,  	// SAGAT - TRESORIERS

											'query'	=>	'309,690'));



	// Give the current season

	// If we are in or past September, we are in season year + 1, else we are in current year season

	// Exemple : July 2014 is season 2014, but October 2014 is season 2015.

	$now = getdate();

	$season = ($now['mon'] >= (int)9)? (int)$now['year'] + 1 : (int)$now['year'] ;



	// Initialize PDO and connexion to the SGDF Intranet Database

	try{



		// Applies different DSN to PDO if the script is running in dev or prod environment

		// dblib is prod

		// sqlsrv is dev / local

		$dsn = (php_sapi_name() === "cli" || 

				isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === "ws.sgdf.fr")? 

				'dblib:host=46.105.115.173;dbname=sgdf' : 

				'sqlsrv:Server=46.105.115.173;Database=sgdf' ;



		$pdo_db = new PDO(	$dsn, 

							'visiteur_sgdf', 

							'fh!e654er');

	} catch (PDOException $e){

		

		mail (	'informatique@sgdf.fr', 

				"Mailjet List Updater - Exception", 

				"PDOException in file ". $e->getFile() ." line ". $e->getLine(). " : \n". $e->getMessage() . "\n PDO couldn't connect with DSN " . $dsn );

		die();

	}	



	foreach($working_lists as $w_list){

		

		try{

			// The query we will use to request the SGDF intranet the relevant emails

			$mjlist = (int) $w_list->id ;

			$intranet_id = (array) explode(',', $w_list->query) ;

			$intranet_emails = (array) array() ;



			if (is_int($mjlist) && is_array($intranet_id) && count($intranet_id) > 0 ) {

			

				// Load and execute SQL query

				$pdo_query = $pdo_db->prepare("SELECT

											       PER.CODE_ADHERENT AS CODE_ADHERENT,

											       CASE WHEN PER.COURRIEL_PERSONNEL IS NULL OR PER.COURRIEL_PERSONNEL = ''

											             THEN PER.COURRIEL_PROFESSIONNEL

											             ELSE PER.COURRIEL_PERSONNEL

											       END AS COURRIEL

												FROM INSCRIPTION INS

												       JOIN ADHESION ADH

												          ON ADH.ID_ADHERENT = INS.ID_INSCRIT

												       JOIN FONCTION FCT

												          ON INS.ID_FONCTION = FCT.ID_FONCTION

												       JOIN STRUCTURE STR

												          ON STR.ID_STRUCTURE = INS.ID_STRUCTURE

												       JOIN PERSONNE PER

												          ON PER.ID_PERSONNE = INS.ID_INSCRIT

												       JOIN SAISON SAI

												          ON SAI.ID_SAISON = INS.ID_SAISON

												WHERE 

												       INS.ID_SAISON IN(:season) -- saison 2014

												       AND FCT.ID_SAISON = INS.ID_SAISON

												       AND STR.ID_SAISON = INS.ID_SAISON

												       AND ADH.ID_SAISON = INS.ID_SAISON

												       AND FCT.CODE IN(:code) -- RG

												       AND INS.TYPE = '0' -- adhérent

												       AND INS.DERNIERE_INSCRIPTION_SAISON = '1'

												       AND STR.STATUT = '0' -- structure ouverte

												       AND PER.STATUT = '0' -- Inscrit(e)

												       AND ((PER.COURRIEL_PERSONNEL IS NOT NULL AND PER.COURRIEL_PERSONNEL <> '') 

												             OR (PER.COURRIEL_PROFESSIONNEL IS NOT NULL AND PER.COURRIEL_PROFESSIONNEL <> ''))

												ORDER BY PER.CODE_ADHERENT ASC");

				

				$pdo_query->bindParam(':season', $season);

				

				// Loop the function IDs for complex queries and execute them to push 

				// the contacts in the email array

				foreach ($intranet_id as $function_id) {

					$pdo_query->bindParam(':code', $function_id);

					$pdo_query->execute();



					while($adherent = $pdo_query->fetch(PDO::FETCH_OBJ)){

						array_push($intranet_emails, (string) $adherent->COURRIEL);

					}

				}

			} else {

				throw new Exception("Error in the mailjet list configuration. var mjlist should be an integer, or var intranet_id should be an array of at least 1 index", 1);

			}

			

			// Get current Mailjet list and create the email list

			$mailjet_contacts = (object) $mjapi->listsContacts(array(	'id'	=>	(int) $mjlist,

																		'limit'	=>	100000));

			$mailjet_emails = (array) array();



			// If the previous list contains contacts, we empty it

			// otherwise it's useless to empty it as it returns a "304 : Not Modified" status

			if( property_exists($mailjet_contacts, 'result') && $mailjet_contacts->total_cnt > 0){

				foreach ($mailjet_contacts->result as $mj_contact) {

					array_push($mailjet_emails, (string) $mj_contact->email);

				}



				// Empty current Mailjet list

				$mjapi->listsRemovemanycontacts(array(	'method'	=>	'POST',

														'contacts'	=>	(string) implode(',', $mailjet_emails),

														'id' 		=> 	(int) $mjlist));

			}



			// Populate the Mailjet list with the emails requested from the SGDF Intranet

			$mjapi->listsAddmanycontacts(array(	'method' 	=> 'POST',

												'contacts'	=>	(string) implode(',', $intranet_emails),

												'id'		=>	(int) $mjlist));



			// We are done with this list...

		} catch(PDOException $e){

			mail (	'informatique@sgdf.fr', 

					"Mailjet List Updater - Exception", 

					"PDOException in file ". $e->getFile() ." line ". $e->getLine(). " : \n". $e->getMessage() );

			die();

		} catch (Exception $e){

			mail (	'informatique@sgdf.fr', 

					"Mailjet List Updater - Exception", 

					"Exception in file ". $e->getFile() ." line ". $e->getLine(). " : \n". $e->getMessage() );

			die();

		}

	}



	mail (	'informatique@sgdf.fr', 

			"Mailjet List Updater - Everything is okay", 

			"The list update went okay" );

	echo "Everything is okay" ;



}

?>