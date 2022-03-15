<?php

    interface Systems {

        /*
        *API call to the system to check if the email exists in the database. 
        *Emails that are in the system will be returned.
        *If the system only has 1 database, pass through NULL for $database_id.
        */
        Public function SearchEmail();

        /*
        *Takes in an array countaining the email address and the name of the database it is in.
        *This function will append each result to a table.
        *If the system only has 1 database, pass through NULL for $database_id.
        */
       // Public function DisplayEmails($database_id, $email);
	    Public function IsSessionAlive ();

    }
    ?>