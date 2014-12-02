<?php
//config
$test = isset($_GET['test']);
$wsurl = $test? 'http://intranet-qualification.sgdf.fr':'https://intranet.sgdf.fr';
$idappelantAuthentification = $test? '617220306601':'617220306600';
$idappelantIdentification   = '617220306600';
$idAppelantMarquer = '617220306600';
$marqueur = 'Mission Aventure 2014';

$fctAuth = array(300, 301, 307, 309, 399, 500, 501, 505, 503, 504, 502, 598, 912);
$fctAuthMarquer = '300,301,307,309,399';

if($_SERVER['REMOTE_ADDR'] != '127.0.0.1' &&
   $_SERVER['REMOTE_ADDR'] != '84.55.161.8' &&      //Sortie Internet SGDF
   $_SERVER['REMOTE_ADDR'] != '62.160.99.32' &&     // BSE dev.
   $_SERVER['REMOTE_ADDR'] != '178.33.108.47' &&    // BSE prod.
   $_SERVER['REMOTE_ADDR'] != '46.105.113.51'       // BSE prod.
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
	
	$server->wsdl->addComplexType(
		'ArrayOfString',
		'complexType',
		'array',
		'sequence',
		'',
		array(
			'itemName' => array(
				'name' => 'itemName', 
				'type' => 'xsd:string',
				'minOccurs' => '0', 
				'maxOccurs' => 'unbounded'
			)
		)
	);

	$server->register('authAdh',
		array(
			'nom' => 'xsd:string',
			'num' => 'xsd:string',
			'mdp' => 'xsd:string'
		),// input parameters
		array(
			'Auth'	=>	'xsd:boolean',
			'CodeAdh' => 'xsd:string',
			'Nom' => 'xsd:string',
			'Prenom' => 'xsd:string',
			'FctStruct' => 'SOAP-ENC:Array', //'xsd:string',
			'Fct' => 'xsd:string',
			'CodeStructure' => 'xsd:string',
			'NomStructure' => 'xsd:string',
			'CodeErreur'	=> 'xsd:integer'
		),// output parameters
		'urn:sgdfwsdl',                      // namespace
		'urn:sgdfwsdl#authAdh',              // soapaction
		'rpc',                               // style
		'encoded',                           // use
		'Services Web SGDF'
	);
	
$server->wsdl->addComplexType(
	  'ArrayOfString',
	  'complexType',
	  'array',
	  'sequence',
	  '',
	  array(
		'itemName' => array(
		  'name' => 'itemName', 
		  'type' => 'xsd:string',
		  'minOccurs' => '0', 
		  'maxOccurs' => 'unbounded'
		)
	  )
	);
	$server->register('marquerStructure',
		array(
			'num' => 'xsd:string',
			'mdp' => 'xsd:string',
			'codeStructure' => 'xsd:string'
		),// input parameters
		array(
			'OK'	=>	'xsd:boolean',
			'Messages' => 'xsd:string',
		),// output parameters
		'urn:sgdfwsdl',                      // namespace
		'urn:sgdfwsdl#marquerStructure',     // soapaction
		'rpc',                               // style
		'encoded',                           // use
		'Services Web SGDF'
	);

	function authAdh($nom, $num, $mdp){
		global $wsurl, $idappelantAuthentification, $idappelantIdentification, $fctAuth;
		$ret = array(
            'Auth'	=>	false,
            'CodeAdh' => '',
            'Nom' => '',
            'Prenom' => '',
            'FctStruct' => '',
            'Fct' => '',
            'CodeStructure' => '',
            'NomStructure' => '',
            'CodeErreur' => 2 //default return : impossible de se connecter
        );

        $fctAuthString = implode(',', $fctAuth);
        $nom = urldecode($nom);
        $mdp = urldecode($mdp);

        /**
         * we call ws authentification to verify if user can auth
         */
        $param_auth = array(
            'idAppelant' => $idappelantAuthentification,
            'numAdherent'  => $num,
            'motDePasse' => $mdp,
            'fonctionsAutorises' => $fctAuthString
        );

        $authClient = new soapclient( $wsurl.'/WebServices/Authentification.asmx?wsdl' );
        try{
            $authReturn = $authClient->__soapCall('Authentifier', array('parameters' => $param_auth));
            $authReturn = $authReturn->AuthentifierResult;
            /*print_r($idappelantAuthentification);
            print_r($idappelantIdentification);
            print_r($authReturn);*/

            if($authReturn->OK){
                $param_identification = array(
                    'idAppelant' => $idappelantIdentification,
                    'num'  => $num,
                    'nom' => $nom
                );
                $client = new soapclient( $wsurl.'/WebServices/Identification.asmx?wsdl' );

                try{
                    $oReturn = $client->__soapCall('s_estAdherentExtended', array('parameters' => $param_identification));
                    if ($oReturn->s_estAdherentExtendedResult->Type==1) {

                        $fctUser = array();
                        $fctStruct = array();

                        $fctUser[] = array($oReturn->s_estAdherentExtendedResult->Fct, $oReturn->s_estAdherentExtendedResult->Cstruct);
                        $fctStruct[$oReturn->s_estAdherentExtendedResult->Fct] = $oReturn->s_estAdherentExtendedResult->Cstruct;

                        $fctSec = isset($oReturn->s_estAdherentExtendedResult->FctSecond->ArrayOfString) ? $oReturn->s_estAdherentExtendedResult->FctSecond->ArrayOfString : array();

                        if (count($fctSec, COUNT_RECURSIVE) >= 1 ){
                            foreach($oReturn->s_estAdherentExtendedResult->FctSecond as $key){
                                if ( count($key) == 1 ){
                                    if ( in_array($key->string[0], $fctAuth ) ) {
                                        $fct = $key->string[0];
                                        $fctStruct[$fct] = $key->string[2];
                                        $fctUser[] = array($fct, $key->string[2]);
                                    }
                                }

                                if ( count($key) > 1 ){
                                    foreach($key as $k ){
                                        if ( in_array($k->string[0], $fctAuth ) ) {
                                            $fct = $k->string[0];
                                            $fctStruct[$fct] = $k->string[2];
                                            $fctUser[] = array($fct,$k->string[2]);
                                        }
                                    }
                                }
                            }
                        }

                        $ret['Auth']	        =	true;
                        $ret['CodeAdh']         = $oReturn->s_estAdherentExtendedResult->CodeClient;
                        $ret['Nom']             = $oReturn->s_estAdherentExtendedResult->Nom;
                        $ret['Prenom']          = $oReturn->s_estAdherentExtendedResult->Prenom;
                        $ret['FctStruct']       = $fctUser; //'xsd:string',
                        $ret['Fct']             = $oReturn->s_estAdherentExtendedResult->Fct;
                        $ret['CodeStructure']   = $oReturn->s_estAdherentExtendedResult->Cstruct;
                        $ret['NomStructure']    = $oReturn->s_estAdherentExtendedResult->NomStruct;
                        $ret['CodeErreur']      = 0;
                    }
                } catch (SoapFault $fault) {
                    trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
                }
            }
            else{
                switch ($authReturn->Code){
                    case '-1':
                        $ret['CodeErreur'] =  1;
                        break;
                    case '-2':
                        $ret['CodeErreur'] =  3;
                        break;
                }
            }
        }catch (SoapFault $fault) {
            trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
        }

		/**
		*
		* Codes erreurs : 
		* 0 -> Tout ok
		* 1 -> Infos de connexion incorrect
		* 2 -> Impossible de se connecter
		* 3 -> Fonction n'a pas le droit d'inscrire le groupe
		*
		*/
		return $ret;
	}

	function marquerStructure($num, $mdp, $codeStructure){
		global $wsurl, $idAppelantMarquer, $fctAuthMarquer, $marqueur;

		/*
		* 3 types de marquage possible : 
		*	0 : marquer la structure seul.
		*   1 : marquer la structure et ses enfants dans l’échelon.
		*   2 : marquer la structure et ses structures enfants cumulées.
		*/

		$param = array(
			'idAutorisation' 					=> $idAppelantMarquer,
			'numAdherent'	 					=> $num,
			'motDePasse'	 					=> urldecode($mdp),
			'marqueur' 	 						=> $marqueur,
			'fonctionsAutorises'				=> $fctAuthMarquer,
			'codesStructuresDeFoncPrincEtFoncSecondAMarquer'	=> $codeStructure,
			'typeMarquage'						=> 0
		);

		try {
			//$client = new SoapClient('http://intranet-qualification.sgdf.fr/WebServices/Identification.asmx?wsdl', array());
			$client = new soapclient( $wsurl.'/WebServices/Identification.asmx?wsdl' );

			try{
				//$oReturn =  $client->estAdherentExtended($param);
				$oReturn = $client->__soapCall('MarquerStructure', array('parameters' => $param));
			} catch (SoapFault $fault) {
				trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
			}

			$ret = array(
				$oReturn->MarquerStructureResult->OK,
				$oReturn->MarquerStructureResult->Messages
			);
		}
		catch (SoapFault $fault) {
			trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
			$ret = array(
				false,
				'Une erreur inconnue s\'est produite'
			);
		}
		
		return $ret;
	}
	// Use the request to (try to) invoke the service
	$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
	$server->service($HTTP_RAW_POST_DATA);
}