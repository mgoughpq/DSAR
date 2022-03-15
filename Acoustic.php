<?php

    include "interface.php";

    class Acoustic implements Systems {
        private $access_token;
        private $session_id;
        private $database_list = array();
        private $nonce = '1092392721538686';
        private $key = 'isthisthesandboxacc';
		private $is_session_id;
        public $email;

        Public function __construct() {
            // Call to store access token
            $this->AuthenticateSystem();
            // Get session_id
            $this->getSessionID();
            // Call to store list of database IDs
            $this->GetAllDatabases();
            $this->email;
        }

        function SetEmail($em) {
            $this->email = $em;
        }

        Private function AuthenticateSystem() {

            $url           = 'https://api4.ibmmarketingcloud.com/oauth/token';
            $client_id     = '99de8788-60e3-4ba4-83f8-798d732ff59b';
            $client_secret = 'c3dba4ad-9fce-4e31-9031-f2f00fe827ba';
            $refresh_token = 'rOhEELj9Pz6da0J1ktTEpwUTRIKjWjNY6xJ7D6Gsg6qUS1';
            $grant_type    = 'refresh_token';
            $json          = '';
            $access_token  = '';


            $data = array('client_id' => $client_id,
                        'client_secret' => $client_secret,
                        'refresh_token' => $refresh_token,
                        'grant_type' => $grant_type
                        );
            $headers = array();
            $headers['Content-Type'] = "application/x-www-form-urlencoded";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, count($data));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $json_result = curl_exec($ch);

            if (curl_errno($ch)) {
                echo 'Error Number : ' . curl_errno($ch). ' ' . curl_error( $ch );
            }
            curl_close($ch);

            $json = json_decode($json_result,true);
            $this->access_token = $json["access_token"];
        }

        Private function GetAllDatabases() {

            //Variables
            $url           = 'https://api4.ibmmarketingcloud.com/XMLAPI';
            $auth          = 'Bearer ' .$this->access_token;
            $database_list = array();
            $dbCounter = 0;

            // Check if XML file exists
            if (file_exists('databaseid.xml')) {
                $xml = file_get_contents('databaseid.xml');
            } else {
                exit('Failed to open databaseid.xml.');
            }

            //Set XML playload as a SimpleXmlElement. Store it in $request
            $xmlObj = new SimpleXmlElement($xml);
            $request = $xmlObj->asXml();

            /*
            *POST request to server
            *Pass in URL
            *Add XML payload by calling 'CURLOPT_POSTFIELDS'
            */

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            //Headers of request
            $headers = array(
                'Content-Type: text/xml',
                'Authorization: ' . $auth,
                'Content-Length: ' . strlen($request),
            );
            /*
            *Add headers to the request
            *Disable SSL
            */
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);

            //Response from server is now stored in $xmlResponse, with some tags removed
            $response = curl_exec($ch);
            curl_close($ch);
            $elem = new SimpleXMLElement($response);
            $xmlResponse = $elem->Body->RESULT;


            //Get the number of Databases, value is stored in $dbCount
            foreach ($xmlResponse as $id) {
                    $dbCount = $id->count()-1;
                }

            /*Loop once for every database found, store each database_id in $db_id
             *Push each database_id into $database_list array
             */
            while ($dbCounter < $dbCount) {
                $db_id = (String) $xmlResponse->LIST[$dbCounter]->ID;
                $db_name = (String) $xmlResponse->LIST[$dbCounter]->NAME;
                $is_folder = (String) $xmlResponse->LIST[$dbCounter]->IS_FOLDER;

                if ($is_folder == 'false') {
                    array_push($this->database_list, array("DatabaseName"=> $db_name, "DatabaseID"=>$db_id));
                   // array_push($this->database_list, $db_id);
                }

                $dbCounter = $dbCounter +1;
            }
           // return($database_list);
           //$this->database_list = $database_list;
        }
        Private function getSessionID() {
            $data = json_decode( file_get_contents('/var/www/cgi-bin/connections/json_content.json', true));
            $encrypted_email = $data->AcousticEmail;
            $encrypted_password = $data->AcousticPassword;

            // Store the cipher method
            $ciphering = "AES-128-CTR";

            // Use OpenSSl Encryption method
            $iv_length = openssl_cipher_iv_length($ciphering);
            $options = 0;

            // Non-NULL Initialization Vector for decryption
            $nonce = $this->nonce;

            // Store the decryption key
            $decryption_key = $this->key;

            // Use openssl_decrypt() function to decrypt the data
            $decrypt_email = openssl_decrypt ($encrypted_email, $ciphering, $decryption_key, $options, $nonce);
            $decrypt_password = openssl_decrypt ($encrypted_password, $ciphering, $decryption_key, $options, $nonce);

            // Remove padded characters
            $email_pad = substr($decrypt_email, 5);
            $email = substr($email_pad, 0, -5);
            $pw_pad = substr($decrypt_password, 5);
            $pw = substr($pw_pad, 0, -5);
			
			$email = 'pq-imcerrors@proquest.com';
			$pw = 'pR0QUESTD3vt3aM!';

            $url           = 'http://api4.ibmmarketingcloud.com/SoapApi';

            $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sil="SilverpopApi:EngageService.SessionMgmt.Login">
                            <soapenv:Body>
                                <sil:Login>
                                    <sil:USERNAME>'.$email.'</sil:USERNAME>
                                    <sil:PASSWORD>'.$pw.'</sil:PASSWORD>
                                </sil:Login>
                            </soapenv:Body>
                        </soapenv:Envelope>';
          //  print $xml;
            /*
            *SOAP request to server
            *Pass in URL
            *Add XML payload by calling 'CURLOPT_POSTFIELDS'
            */

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            //Headers of request
            $headers = array(
                'Content-Type: text/xml;charset=UTF-8',
                'SOAPAction: SilverpopApi:Engageservice.Login'
            );

            /*
            *Add headers to the request
            *Enable SSL
            */

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);

            //Response from server is now stored in $final, with some tags reformated
            $response = curl_exec($ch);
            //print "hello";
            //print "response=".$response;
//
            curl_close($ch);
            $cleanxml = str_ireplace('envelope:Envelope', 'Envelope', $response);
            $cleanxml = str_ireplace('envelope:Body', 'Body', $cleanxml);
            $cleanxml = str_ireplace('envelope:Header', 'Header', $cleanxml);

            $response_xml = simplexml_load_string($cleanxml);

            //$final_xml = (String) $response_xml->Body->RESULT->SESSIONID;
            //print_r($response_xml);
            $this->session_id = (String) $response_xml->Body->RESULT->SESSIONID;
			$this->is_session_id = (String) $response_xml->Body->RESULT->SUCCESS;
        }

        Private function SearchEmailWithDB($databaseID, $email) {

            $url           = 'http://api4.ibmmarketingcloud.com/SoapApi';
            $auth          = $this->session_id;
			
			//print('is_session_id; '. $this->is_session_id);
			IF ($this->is_session_id == 'true') {
			
				$xml ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sil="SilverpopApi:EngageService.SessionHeader" xmlns:sil1="SilverpopApi:EngageService.ListMgmt.RecipientActions">
						<soapenv:Header>
							<sil:sessionHeader>
								<sil:sessionid>'.$auth.'</sil:sessionid>
							</sil:sessionHeader>
						</soapenv:Header>
						<soapenv:Body>
							<sil1:SelectRecipientData>
								<sil1:LIST_ID>'.$databaseID.'</sil1:LIST_ID>
								<sil1:EMAIL>'.$email.'</sil1:EMAIL>
								<sil1:COLUMN>
									<sil1:NAME>EMAIL</sil1:NAME>
									<sil1:VALUE>'.$email.'</sil1:VALUE>
								</sil1:COLUMN>
							</sil1:SelectRecipientData>
						</soapenv:Body>
						</soapenv:Envelope>';
				/*
				*SOAP request to server
				*Pass in URL
				*Add XML payload by calling 'CURLOPT_POSTFIELDS'
				*/

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
				curl_setopt($ch, CURLINFO_HEADER_OUT, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				//Headers of request
				$headers = array(
					'Content-Type: text/xml;charset=UTF-8',
					'SOAPAction: SilverpopApi:Engageservice.SelectRecipientData'
				);

				/*
				*Add headers to the request
				*Enable SSL
				*/
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
				curl_setopt($ch, CURLOPT_TIMEOUT, 60);

				$response = curl_exec($ch);
				 //print_r('received response from Acoustic server '. $response);
				// print_r('databaseID '. $databaseID);

				if(curl_error($ch)) {
					echo 'Error Number : ' . curl_errno($ch). ' ' . curl_error( $ch );
				} else {
					$cleanxml = str_ireplace('envelope:Envelope', 'Envelope', $response);
					$cleanxml = str_ireplace('envelope:Body', 'Body', $cleanxml);
					$cleanxml = str_ireplace('envelope:Header', 'Header', $cleanxml);

					$response_xml = simplexml_load_string($cleanxml);
					if (isset($response_xml->Body->RESULT->SUCCESS)) {
					  $final_xml = (String) $response_xml->Body->RESULT->SUCCESS;
					}

					return $final_xml;
				}
					curl_close($ch);
			}
        }

        Public function SearchEmail() {
			IF ($this->is_session_id == 'true') {

				$valid_emails = array();

				$found_any_match = false;
				$has_added_email = false;
				for ($db_counter = 0; $db_counter < count($this->database_list); $db_counter++) {
					if ($this->SearchEmailWithDB($this->database_list[$db_counter]['DatabaseID'], $this->email) == 'true') {

						//Check if there is any match on the acoustic platform for email
						if ($found_any_match === false) {
							$found_any_match = true;
						}
					    array_push($valid_emails, array("NameID"=>$this->database_list[$db_counter]['DatabaseID'],
                                                        "Name"=>$this->database_list[$db_counter]['DatabaseName'],
                                                        "EntityID"=>"",
                                                        "FoundMatch"=>"Yes"));
					} else {
						array_push($valid_emails, array("NameID"=>$this->database_list[$db_counter]['DatabaseID'],
                                                        "Name"=>$this->database_list[$db_counter]['DatabaseName'],
                                                        "EntityID"=>"",
                                                        "FoundMatch"=>"No"));
					}
				}
				//Final array if inputted email exists an any of the acoustic databases
				if ($found_any_match === true) {
					$final = array(
						'Email' => $this->email,
						'FoundAnyMatch' => 'Yes',
						'Detail' => $valid_emails
					  );
				//Final array if inputted email doesnt exist an any of the acoustic databases
				} else {
					$final = array(
						'Email' => $this->email,
						'FoundAnyMatch' => 'No',
						'Detail' => $valid_emails
					);
				}
				//print_r($final);
				return $final;
			} ELSE {
				print('Failed to connect to Acoustic. Please create and IT ticket.');
			}
		}
		
		Public function IsSessionAlive () {
			$is_session_alive = $this->is_session_id;
			
			return $is_session_alive;
		}
	}
?>
