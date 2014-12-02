<?php
//config
$test = isset($_GET['test']);
$wsurl = $test? 'http://intranet-qualification.sgdf.fr':'https://intranet.sgdf.fr';
$idappelantAuthentification = $test? '617220306601':'617220306600';
$idappelantIdentification   = '617220306600';

//$fctAuth = array(300, 301, 307, 309, 399, 500, 501, 504, 505, 598, 912, 670, 910);
$fctAuth = array(500, 501, 504, 505, 598, 912, 670);

if($_SERVER['REMOTE_ADDR'] != '127.0.0.1' &&
   $_SERVER['REMOTE_ADDR'] != '84.55.161.8' &&      //Sortie Internet SGDF
   $_SERVER['REMOTE_ADDR'] != '146.185.40.1' &&      //Oxalide
   $_SERVER['REMOTE_ADDR'] != '109.24.167.181'       //Kura - développement
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
	
	$server->register('authentifierAdherent',
		array(
			'nom' => 'xsd:string',
			'codeAdherent' => 'xsd:string',
			'mdp' => 'xsd:string'
		),// input parameters
		array(
			'Authentification'	=>	'xsd:boolean',
			'CodeAdherent' => 'xsd:string',
			'Nom' => 'xsd:string',
			'Prenom' => 'xsd:string',
			'Fonction' => 'xsd:string',
			'CodeStructure' => 'xsd:string',
			'NomStructure' => 'xsd:string',
			'CodeTerritoire' => 'xsd:string',
			'CodeCentreRessources' => 'xsd:string',
			'CodeErreur'	=> 'xsd:integer'
		),// output parameters
		'urn:sgdfwsdl',                      // namespace
		'urn:sgdfwsdl#authifierAdherent',    // soapaction
		'rpc',                               // style
		'encoded',                           // use
		'Services Web SGDF'
	);
	

	function authentifierAdherent($nom, $codeAdherent, $mdp){
		global $wsurl, $idappelantAuthentification, $idappelantIdentification, $fctAuth;
		$ret = array(
            'Authentification'	=>	false,
            'CodeAdherent' => '',
            'Nom' => '',
            'Prenom' => '',
            'Fonction' => '',
            'CodeStructure' => '',
            'NomStructure' => '',
			'CodeTerritoire' => '',
			'CodeCentreRessources' => '',
            'CodeErreur' => 2 //default return : impossible de se connecter
        );

        $fctAuthString = implode(',', $fctAuth);
        $nom = urldecode($nom);
        $mdp = urldecode($mdp);
		
		//dans urldecode les + sont remplacés par des espaces (cf. doc PHP)
		//on fait dons l'opération inverse pour remettre un + - cela peut arriver dans les mot de passe
		$mdp = str_replace(' ','+',$mdp);

        /**
         * we call ws authentification to verify if user can auth
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
                $param_identification = array(
                    'idAppelant' => $idappelantIdentification,
                    'num'  => $codeAdherent,
                    'nom' => $nom
                );
                $client = new soapclient( $wsurl.'/WebServices/Identification.asmx?wsdl' );

                try{
                    $oReturn = $client->__soapCall('s_estAdherentExtended', array('parameters' => $param_identification));
                    if ($oReturn->s_estAdherentExtendedResult->Type==1) {
                        $ret['Authentification']= true;
                        $ret['CodeAdherent']    = $oReturn->s_estAdherentExtendedResult->CodeClient;
                        $ret['Nom']             = $oReturn->s_estAdherentExtendedResult->Nom;
                        $ret['Prenom']          = $oReturn->s_estAdherentExtendedResult->Prenom;
                        $ret['Fonction']		= $oReturn->s_estAdherentExtendedResult->Fct;
                        $ret['CodeStructure']   = $oReturn->s_estAdherentExtendedResult->Cstruct;
                        $ret['NomStructure']    = $oReturn->s_estAdherentExtendedResult->NomStruct;
						$ret['CodeTerritoire']	= substr($oReturn->s_estAdherentExtendedResult->Cstruct,0,5).'0000'; 
						$ret['CodeCentreRessources'] = substr($oReturn->s_estAdherentExtendedResult->Cstruct,0,2).'0000000';
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
		* 1 -> Informations de connexion incorrectes (ex. mot de passe)
		* 2 -> Impossible de se connecter
		* 3 -> Fonction n'a pas le droit d'utiliser le webservice
		*
		*/
		return $ret;
	}
	
	// Use the request to (try to) invoke the service
	$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
	$server->service($HTTP_RAW_POST_DATA);
}