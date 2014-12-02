<?php
//Configuration Webservices Intranet
$test = isset($_GET['test']);
$wsurl = $test ? 'http://intranet-qualification.sgdf.fr':'https://intranet.sgdf.fr';
$idappelantAuthentification = $test? '617220306601':'617220306600';
$idappelantIdentification   = '617220306600';

$fctAuth = array(300); //toutes les fonctions sont autorisées à se connecter

//Configuration connexion base SQL Intranet
$intranetServeur = '46.105.115.173';
$intranetUtilisateur = 'visiteur_sgdf';
$intranetMotpasse = 'fh!e654er';

if($_SERVER['REMOTE_ADDR'] != '127.0.0.1' &&		//Localhost
   $_SERVER['REMOTE_ADDR'] != '84.55.161.8' &&		//Sortie Internet SGDF
   $_SERVER['REMOTE_ADDR'] != '178.32.28.118' &&	//WebXY
   $_SERVER['REMOTE_ADDR'] != '146.185.40.1'      	//Oxalide
){
    header('HTTP/1.0 403 Forbidden');
    echo 'IP : '.$_SERVER['REMOTE_ADDR'];
    die('<h1>Vous n\'avez pas le droit d\'acc&eacute;der à cette ressource&nbsp;!</h1>');
}else{
	header("Content-Type: application/soap+xml; charset=UTF-8");
	header("Cache-Control: no-cache, no-store, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0");
	header("Expires: Sun, 19 Nov 1978 05:00:00 GMT");
	header("Pragma: no-cache");

	require('lib/nusoapSGDF.php');

	$server = new nusoap_server;
	$server->configureWSDL('sgwsdl', 'urn:sgdfwsdl');
	
	$server->soap_defencoding = 'UTF-8';
	$server->decode_utf8 = false;
	
	$server->register('prelevementStructureAdherent',
		array(
			'codeAdherent' => 'xsd:string',
			'mdp' => 'xsd:string',
			'codeStructure' => 'xsd:string'
		), // input parameters
		array(
			'Autorisation'	=>	'xsd:boolean',
			'CodeAdherent' => 'xsd:string',
			'Nom' => 'xsd:string',
			'Prenom' => 'xsd:string',
			'Fonction' => 'xsd:string',
			'CodeStructure' => 'xsd:string',
			'NomStructure' => 'xsd:string',
			'CodeErreur'	=> 'xsd:integer'
		), // output parameters
		'urn:sgdfwsdl',                      	// namespace
		'urn:sgdfwsdl#prelevementStructureAdherent',    // soapaction
		'rpc',                               	// style
		'encoded',                           	// use
		'Services Web SGDF'
	);
	
	function prelevementStructureAdherent($codeAdherent, $mdp, $codeStructure){ 
		global $wsurl, $idappelantAuthentification, $idappelantIdentification, $fctAuth, $intranetServeur, $intranetUtilisateur, $intranetMotpasse;
		$ret = array(
            'Autorisation'	=>	false,
            'CodeAdherent' => '',
            'Nom' => '',
            'Prenom' => '',
            'Fonction' => '',
            'CodeStructure' => '',
            'NomStructure' => '',
            'CodeErreur' => 2 //default return : erreur technique
        );

        $fctAuthString = implode(',', $fctAuth);

        /**
         * appel du webservice authentification
         */
        $param_auth = array(
            'idAppelant' => $idappelantAuthentification,
            'numAdherent'  => $codeAdherent,
            'motDePasse' => $mdp,
            'fonctionsAutorises' => $fctAuthString
        );

        $authClient = new soapclient( $wsurl.'/WebServices/Authentification.asmx?wsdl' );
        try{
            $authReturn = $authClient->__soapCall('Authentifier', array('parameters' => $param_auth));
            $authReturn = $authReturn->AuthentifierResult;

            if($authReturn->OK){
				//on interroge la base Intranet pour connaître le nom de l'adhérent
				$intranetConnect = mssql_connect($intranetServeur,$intranetUtilisateur,$intranetMotpasse) 
					or die('Connexion à la base impossible');
				mssql_select_db('sgdf') 
					or die('Sélection de la base impossible');
				$intranetRequete = 'SELECT TOP 1 NOM FROM PERSONNE WHERE CODE_ADHERENT = \''.$codeAdherent.'\'';
				$intranetResultat = mssql_query($intranetRequete) 
					or die('Erreur de requête' . mysql_error());
				if(mssql_num_rows($intranetResultat) == 1) {
					while ($ligne = mssql_fetch_assoc($intranetResultat))
						$nom = $ligne['NOM'];
				}
				else $nom = '';
				mssql_close($intranetConnect)
					or die('Fermeture connexion à la base impossible');
				
				/**
				* appel du webservice identification
				*/
                $param_identification = array(
                    'idAppelant' => $idappelantIdentification,
                    'num'  => $codeAdherent,
                    'nom' => $nom
                );
                $client = new soapclient( $wsurl.'/WebServices/Identification.asmx?wsdl' );

                try{
                    $oReturn = $client->__soapCall('s_estAdherentExtended', array('parameters' => $param_identification));
                    if ($oReturn->s_estAdherentExtendedResult->Type==1 || $oReturn->s_estAdherentExtendedResult->Type==2) { //1 = adhérent ; 2 = pré-inscrit
                        
						/* contrôle de la cohérence du code structure de l'adhérent avec celui saisi */
						if($oReturn->s_estAdherentExtendedResult->Cstruct == $codeStructure) {
							$ret['Autorisation']	= true;
							$ret['CodeAdherent']    = $oReturn->s_estAdherentExtendedResult->CodeClient;
							$ret['Nom']             = $oReturn->s_estAdherentExtendedResult->Nom;
							$ret['Prenom']          = $oReturn->s_estAdherentExtendedResult->Prenom;
							$ret['Fonction']        = $oReturn->s_estAdherentExtendedResult->Fct;
							$ret['CodeStructure']   = $oReturn->s_estAdherentExtendedResult->Cstruct;
							$ret['NomStructure']    = $oReturn->s_estAdherentExtendedResult->NomStruct;
							
							$ret['CodeErreur']      = 0; //tout est OK
						}
						else {
							$ret['CodeErreur']      = 1; //Informations de connexion incorrectes (ex. mot de passe ou code structure)
						}
                    }
                } catch (SoapFault $fault) {
                    trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
                }
            }
            else{
                switch ($authReturn->Code){
                    case '-1':
                        $ret['CodeErreur'] =  1; //Informations de connexion incorrectes (ex. mot de passe ou code structure)
                        break;
                    case '-2':
                        $ret['CodeErreur'] =  3; //Fonction n'a pas le droit d'utiliser le webservice
                        break;
                }
            }
        }catch (SoapFault $fault) {
            trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
        }

		/**
		* Codes erreurs : 
		* 0 -> Tout ok
		* 1 -> Informations de connexion incorrectes (ex. mot de passe ou code structure)
		* 2 -> Impossible de se connecter - erreur technique
		* 3 -> Fonction n'a pas le droit d'utiliser le webservice
		*/
		return $ret;
	}
	
	// Use the request to (try to) invoke the service
	$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
	$server->service($HTTP_RAW_POST_DATA);
}