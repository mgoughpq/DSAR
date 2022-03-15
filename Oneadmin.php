<?php

    //include "interface.php";

    class Oneadmin implements Systems {

        private $session_id;
        private $is_session_id = 'true';

		public $email;
		public $connection;  
        public $username; 
		public $password;  
		public $table;  
		public $conn;


		function SetEmail($email, $connection, $username, $password) {
			$this->email  = $email;
            $this->connection  = $connection;
            $this->username = $username;
			$this->password  = $password;
			//$this->table  = $table;
			
			$this->conn = new PDO($connection, $username, $password);

       	}

        Public function SearchEmail() {
			if ($this->is_session_id == 'true') {

				$valid_emails = array();

				$found_any_match = false;
				
			/* * * * * * * * * * * * * * * * * * * * * * * * * * * * */ 
			/* CHANGE THE VALUE BELLOW IF TABLES ARE ADDED OR REMOVED */
				$num_of_tables = 3;
			/* * * * * * * * * * * * * * * * * * * * * * * * * * * * */ 

				for ($i = 0; $i < $num_of_tables; $i++) {
					if ($i == 0) {
						// Get full string returned from PLSQL prcedure
						$result = $this->DoesEmailExistAdmin();										
						// Table name
						$table_name = 'Admin';
					} elseif ($i == 1) {
						$result = $this->DoesEmailExistAccessProfile();
						$table_name = 'Access Profile';
					} elseif ($i == 2) {
						$result = $this->DoesEmailExistTMOrder();
						$table_name = 'TM Order Master';
					}
					// Check if email exists - Reduce string to true or false
					$does_exist = $this->DoesEmailExist($result);
					// Return the list of IDs
					$entity_id = $this->TrimEntityID($result);

					if ($does_exist == 'true') {
						$result = 'Yes';
						$found_any_match = true;
					} else {
						$result = 'No';
					}
					array_push($valid_emails, array("Name"=>$table_name,"FoundMatch"=>$result, "EntityID"=>$entity_id));
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
			} else {
				print('Failed to connect to Oneadmin. Please create and IT ticket.');
			}
		}
		Public function IsSessionAlive () {
			$is_session_alive = $this->is_session_id;
			
			return $is_session_alive;
		}

		Private function DoesEmailExistAdmin () {
			//Spoof email;
			$email = 'mail@mail.com';
		    // SQL statement with placeholder
			$sql = 'SELECT ONEADMIN.GDPR_SEARCH.SEARCH_ADMIN(:email) FROM DUAL';
			// Prepare SQL statement
			$stmtdev = $this->conn->prepare($sql);
			// Bind variable
			$stmtdev->bindParam(':email', $email);
			// Replace email
			$email = $this->email;

			if (!($stmtdev->execute())) {
				print "<b>ERROR: Could not fetch results</b><br>";
				print_r($stmtdev->errorInfo());
			} else {					
				$resultdev = $stmtdev->fetchColumn();  
				return $resultdev;
    		}
			
		}

		Private function DoesEmailExistAccessProfile () {
			//Spoof email;
			$email = 'mail@mail.com';
		    // SQL statement with placeholder
			$sql = 'SELECT ONEADMIN.GDPR_SEARCH.search_access_profile(:email) FROM DUAL';
			// Prepare SQL statement
			$stmtdev = $this->conn->prepare($sql);
			// Bind variable
			$stmtdev->bindParam(':email', $email);
			// Replace email
			$email = $this->email;

			if (!($stmtdev->execute())) {
				print "<b>ERROR: Could not fetch results</b><br>";
				print_r($stmtdev->errorInfo());
			} else {					
				$resultdev = $stmtdev->fetchColumn();  
				
				return $resultdev;
    		}
			
		}

		Private function DoesEmailExistTMOrder () {
			//Spoof email;
			$email = 'mail@mail.com';
		    // SQL statement with placeholder
			$sql = 'SELECT ONEADMIN.GDPR_SEARCH.search_tm_order_master(:email) FROM DUAL';
			// Prepare SQL statement
			$stmtdev = $this->conn->prepare($sql);
			// Bind variable
			$stmtdev->bindParam(':email', $email);
			// Replace email
			$email = $this->email;

			if (!($stmtdev->execute())) {
				print "<b>ERROR: Could not fetch results</b><br>";
				print_r($stmtdev->errorInfo());
			} else {					
				$resultdev = $stmtdev->fetchColumn();  
				
				return $resultdev;
    		}
			
		}

		Private function TrimEntityID ($entity) {
			// Reduce string to entity ID(s)
			$temp = strrpos($entity, '|');
			$entity_id = substr($entity,$temp + 1);

			return $entity_id;
		}

		Private function DoesEmailExist ($email) {
			// Reduce string to true or false
			$does_exist = strtok($email, '|');
			
			return $does_exist;
		}
    }