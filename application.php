<?php

		date_default_timezone_set('UTC');

		$dbhsystem = new PDO("oci:dbname=sharedb101.dc4.pqe:1521/oneprd", 'JPAYNE', 'asnb483hwe56');

		session_start();

    function processEmails($emails) {
        require_once 'Acoustic.php';
		require_once 'Oneadmin.php';
		require_once 'Tracs.php';
		require_once 'Trials.php';

      //  require_once 'Oneadmin.php';

        // Remove all whitespace from list
        //$new_emails = preg_replace( '/\s*/m', '',$emails );

		// If email list is empty or comprises of nothing but spaces, do not process
		//if (strlen($new_emails) > 1) {

			// Convert comma seperated list into array
			//$email_array = explode(',', $new_emails);

			// Get the number of emails the user has inputted
		$no_of_emails = count($emails);

		//Get connection json data
		$data = json_decode( file_get_contents('/var/www/html/dsar/connection.json', true));
    	$counter = 0;
		$is_first = true;

	//	$is_session_alive  = $acoustic->IsSessionAlive();


		//if ($is_session_alive == 'true') {
			for ($x = 0; $x < $no_of_emails; $x++) {

				$data = json_decode( file_get_contents('/var/www/html/dsar/connection.json', true));
				$counter = 0;
				$is_first = true;
				$classes = array();
				$DatabaseResult = array();

				foreach ($data as  $data) {
                   // echo $data->system;
					if ($data->system == 'Acoustic') {
						$classes[$counter] = new $data->system;
						$classes[$counter]->SetEmail($emails[$x]);
						//echo 'Iteration number: '||$counter || ' for ... Accoustic <br>';
					} else {
						$classes[$counter] = new $data->system;
						$classes[$counter]->SetEmail($emails[$x], $data->connection, $data->username, $data->password);
						//echo 'Iteration number: '||$counter || ' for ... other <br>';
					}
						//echo $data->system;
                   // $classes[$counter] =  $data->system->SearchEmail()
					$DatabaseResult[$counter] = $classes[$counter]->SearchEmail();
                    if ($is_first == true) {		
                        $final[$x] = array(
                            'Email' => $DatabaseResult[$counter]['Email'],
                            'System' => array(
                                array(
                                    'SystemName' => $classes[$counter],
                                    'FoundSystemMatch' => $DatabaseResult[$counter]['FoundAnyMatch'],
                                    'DetailSearch' => $DatabaseResult[$counter]['Detail']
                                )
                            )
                        );
						//echo $data->username;
                        $is_first = false;   

                    } else {
                        $final[$x]['System'][$counter] = array(
                            'SystemName' => $classes[$counter],
                            'FoundSystemMatch' => $DatabaseResult[$counter]['FoundAnyMatch'],
                            'DetailSearch' => $DatabaseResult[$counter]['Detail']
                        ); 
                    }
                    $counter = $counter + 1;
				}
			}
			//print_r($final);
			// Convert array to JSON
			$json = json_encode($final);
			return $json;
	/*	}
		else {
			return '';
		}*/
	//	}
    }

    function processExtraDetail ($detail) {
	    $string = "<table> <tr><th>Database Name</th><th>Match Found?</th><th>Entity ID(s)</th></tr>";
	   //$string = "<tr><th>Database Name</th><th>Match Found?</th></tr>";

        foreach ($detail as $id=> $extra_detail)  {
			if ($extra_detail->FoundMatch == 'Yes') {
				$string = $string ."<tr style='background-color: #FFFF00;'><td>". $extra_detail->Name ."</td><td>". $extra_detail->FoundMatch. "</td><td>". $extra_detail->EntityID. "</td></tr>";
			} else {
				$string = $string ."<tr><td>". $extra_detail->Name ."</td><td>". $extra_detail->FoundMatch. "</td><td>". $extra_detail->EntityID. "</td></tr>";
			}
		}
		$string = $string ."</table>";
        return $string;
    }
    function login($username,$password)
	{
		$username = strtolower($username);

		if($username != "" && $password != "")
		{
		//	print $username;
			$valid = validate_user($username,$password);

			if ($valid)
			{
				//echo 'correct username and password';
				$_SESSION["EMAILCHECKER"]["USERNAME"] = $username;

				//$_SESSION["EMAILCHECKER"]["EMAIL"] = $useremail;
			}
		}
    }

    function validate_user($username,$password)
		{
				global $useremail;
				/**************************************************
				  Bind to an Active Directory LDAP server and look
				  something up.
				***************************************************/
				  $SearchFor=$username;               //What string do you want to find?
				 // print $SearchFor;
				  $result = false;
				  $SearchField="samaccountname";   //In what Active Directory field do you want to search for the string?

				//  $LDAPHost = 'ldaphost.proque.st';       //Your LDAP server DNS Name or IP Address
					$ldap_host = 'iadldapdc101.proque.st';
					$ldap_port = 389;
				  $dn = "DC=proque,DC=st"; //Put your Base DN here
				  $LDAPUserDomain = "@something.something";  //Needs the @, but not always the same as the LDAP server domain
				  $LDAPUser = 'FCN-LDAP1Admin@proquest.com';
				  $LDAPUserPassword = 'RejlE3ZCu7@qIL0z';



				  $LDAPFieldsToFind = array("cn", "givenname", "samaccountname", "homedirectory", "telephonenumber", "mail", "distinguishedname");
				  $cnx = ldap_connect('ldap://'. $ldap_host. ':'. $ldap_port) or die("Could not connect to LDAP");
				  ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);  //Set the LDAP Protocol used by your AD service
				  ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0);         //This was necessary for my AD to do anything
				  ldap_bind($cnx,$LDAPUser,$LDAPUserPassword) or die("Could not bind to LDAP");
				  error_reporting (E_ALL ^ E_NOTICE ^ E_WARNING);   //Suppress some unnecessary messages
				  $filter="($SearchField=$SearchFor*)"; //Wildcard is * Remove it if you want an exact match
				  $sr=ldap_search($cnx, $dn, $filter, $LDAPFieldsToFind);
				  $info = ldap_get_entries($cnx, $sr);

				  for ($x=0; $x<$info["count"]; $x++) {
					//print_r($info[$x]);
					$sam=strtolower($info[$x]['samaccountname'][0]);
					$nam=$info[$x]['cn'][0];
					$email=$info[$x]['mail'][0];
					//print $sam;
					/*$giv=$info[$x]['givenname'][0];
					print $giv;
					$tel=$info[$x]['telephonenumber'][0];
					$dir=$info[$x]['homedirectory'][0];
					$dir=strtolower($dir);
					$pos=strpos($dir,"home");
					$pos=$pos+5;*/
					//if (stristr($sam, "$SearchFor")) {
					if   ($sam == strtolower($SearchFor)) {
					/*  print "\nActive Directory says that:\n";
					  print "CN is: $nam \n";
					  print "SAMAccountName is: $sam \n";
					  print "Given Name is: $giv \n";
					  print "Telephone is: $tel \n";
					  print "Home Directory is: $dir \n";
					  print "DN is: ".$info[$x]['distinguishedname'][0]." \n";
					  */
					//Check user and password:
					  $useremail=$info[$x]['mail'][0];
					//  print "email2=".$useremail;
					  $result = ldap_bind($cnx,$info[$x]['distinguishedname'][0],$password); // or die("Could not bind to LDAP");
						//print "result=".$result;
						if ($result == true)
						{

							check_user_exists($sam,$nam,$email);
							return true;
						}
						else
						{
							return false;
						}
					  }

				  }
	}

	function check_user_exists($username,$name,$email)
	{
		$sql = "SELECT ID FROM USERS WHERE USERNAME='".$username."'";

		$rows = fetchsystem($sql);
		if (!(isset($rows) && $rows != null))
		{
		print "insert";
			/* insert */
			$sql = "INSERT INTO JPAYNE.USERS (ID,USERNAME,EMAIL,NAME) VALUES (REPORT_USERS_SEQ.NEXTVAL,'".$username."','".$email."','".$name."')";
			executeSQLsystem($sql);
		}
		else
		{

			$sql = "UPDATE USERS SET LAST_LOGIN=sysdate WHERE ID =".$rows[0][ID];
			executeSQLsystem($sql);
		}
	}

	function fetchsystem($sql)
	{

		global $dbhsystem;
		$stmt = $dbhsystem->prepare($sql);

		if ($stmt->execute()) {

			return $stmt->fetchAll();

		}
		else
		{
			print "<b>ERROR: Could not fetch results</b><br>";
				print_r($stmt->errorInfo());
		}
	}

	function executeSQLsystem($sql)
	{
		global $dbhsystem;
		$stmt = $dbhsystem->prepare($sql);
		if (!($stmt->execute())) {
			print "<b>ERROR: Could not fetch results</b><br>";
				print_r($stmt->errorInfo());
		}

	}

?>
