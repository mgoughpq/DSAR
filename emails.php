<?php
        $display_results = false;
        $final_email_list = array();
        
        if (isset($_POST) && isset($_POST['email']))
         {

            // Remove all whitespace from list
            $new_emails = preg_replace( '/\s*/m', '',$_POST['email'] );

			// Convert comma seperated list into array
            $email_array = explode(',', $new_emails);

            // Get number of emails
            $no_of_emails = count($email_array);

            /*
             * If email list containted extra  commas (',') it will have passed through empty elements into the array
             * If array element is null remove it from the array
             * Otherwise keep array intact
             * As the array key numbers will be lost, we will need to push valid elements to a new array
             */

            for ($x = 0; $x < $no_of_emails; $x++) {
              
               /* 
                * Email validation,  if the email has:
                * less than 5 characters
                * no '.' characters
                * more than 1 or 0 '@' characters...
                * then the email is invalid.
                */

                if ((strlen($email_array[$x]) < 5) or (strpos($email_array[$x], '.') === FALSE) or (substr_count($email_array[$x], '@') > 1) or (substr_count($email_array[$x], '@') == 0)) {
                    unset($email_array[$x]);
                } else {
                    array_push($final_email_list, $email_array[$x]);
                }
            }

            // If the new array of emails contains 1 or more element process it and set $display_results to true
            if (count($final_email_list) > 0) {
                $json = processEmails($final_email_list);
                $display_results = true;
            } else {
                $display_results = false;
            }
        } else {
            $_POST['email'] = '';
        }
?>
    <html>
    <head>
        <link href="main.css" rel='stylesheet'>
		<link href="bootstrap.css" rel='stylesheet'>
		<script src="jquery-3.3.1.min.js"></script>
		<script src="bootstrap.js"></script>
   </head>
    <body>
        <div class="title">
            <img src="pq-logo.png" alt="logo">
            &nbsp;
            <h2> Email search tool</h2>
        </div>

        <div class="form">
            <p>Please enter a comma seperated list of all of the emails you would like to search for.</p>
            <form action="<?php $PHP_SELF ?>" target ="_self" method="post" id="email">
                <label for="email">Email list:</label><br>
                <textarea rows="20" cols="30" name="email" form="email" required><?= $_POST['email']?></textarea>
                <input type="submit" value="Submit">
            </form> 
        </div>

    <?php

    require_once 'Acoustic.php';

	$acoustic = new Acoustic;

	$is_session_alive  = $acoustic->IsSessionAlive();
			
	if ($is_session_alive == 'true') {
		
		if ($display_results == true){
			$data =  json_decode($json);
			$extra_detail ='';
			$id = 0;
            $detail_array = 'const detail = [];';
            $systems_array = array();

            //print_r($data);
            $systemnames = json_decode( file_get_contents('/var/www/html/dsar/connection.json', true));

			if (count($data)) { 
                echo '<div class ="results">';
				echo '<table id="emailtable">';

                echo "<tr>";
                    echo "<th>Email</th>";
                foreach ($systemnames as  $systemnames) {
                    echo '<th>'.$systemnames->database.'</th>';
                    array_push($systems_array, $systemnames->database);
                } 
				echo "</tr>";
                
				foreach ($data as $idx=> $data) {
                     $i = 0;
                    
                     echo "<tr>";
                     echo "<td>".$data->Email."</td>";
					 foreach ($data->System as $sys =>$system) { 
						 $extra_detail = processExtraDetail($system->DetailSearch);
                         $detail_array .= "detail[".$id."] = \"".$extra_detail."\";";                    						
                        
                         //print($system->DetailSearch);
						echo '<td>';
                            echo '<button class = "btn btn-primary btn-lg" data-toggle = "modal" 
                                    data-target = "#myModal" 
                                    onclick = "sDetails('.$id.', \''.$data->Email. '\''.', \''.$systems_array[$i]. '\');">'.$system->FoundSystemMatch.
                                 '</button>';
						echo '</td>';
                        //echo $id;
                        $id = $id + 1;
                        $i = $i + 1;

					}
                    echo "</tr>";
				}
				 // End of table
				
				echo "</table>";
                echo "</div>"; ?>
				<!-- Modal -->
                <div class = "modal fade" id = "myModal" tabindex = "-1" role = "dialog" 
                    aria-labelledby = "myModalLabel" aria-hidden = "true">
                
                <div class="modal-dialog modal-lg">
                    <div class = "modal-content">
                        
                        <div class = "modal-header">
                            <button type = "button" class = "close" data-dismiss = "modal" aria-hidden = "true">
                                &times;
                            </button>

                        <h4 class = "modal-title" id ="myModalLabel"></h4>
                        </div>
                                    
                        <div class = "modal-body">
                            <p id="details"></p>
                        </div>
                                
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
         
                <script>
                    <?php echo $detail_array; ?>
                    function sDetails(id, email, system) {
                        //alert(detail[id]);
                        document.getElementById("details").innerHTML = detail[id];
                        document.getElementById("myModalLabel").innerHTML = system + ' - ' + email;
                    }
                </script>
                <?php
			}
		}
	} else {
		print('Failed to connect to Acoustic. Please create and IT ticket.');
	}
    ?>   
        </body>
        </html>

