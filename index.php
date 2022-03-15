<?php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);

   require_once 'application.php';
   //$display_form = true;

   if(isset($_POST["ACTION"]))
	{
      if ($_POST["ACTION"] == 'login')
      {
         login($_POST["username"],$_POST["password"]);
         if(!isset($_SESSION["EMAILCHECKER"]))
            print "Incorrect login details or system selection, please try again.";
      }
   }
   // If email username has not been set (and validated) call the login form again
   if(!isset($_SESSION["EMAILCHECKER"]))
   {

?>

      <h1>ProQuest Email Search Tool</h1>
      <p>Please use your windows username and password to login eg asmith/pa$$w0rd.</p>
      <form name="login" action="<?php $PHP_SELF ?>" method="POST">
      <input type="hidden" name="ACTION" value="login">
      <table>
      <tr><th>Username:</th><td><input type="text" name="username" style="width:250px"></td></tr>
      <tr><th>Password:</th><td><input type="password" name="password" style="width:250px"></td></tr>
      <tr><td colspan="2" align="right"><input type="submit" value="Login"></td></tr>
      </table>

<?php
   }
   else
   {
      // If the user has been successfully validated call' emails.php'
      include 'emails.php';
   }


   ?>
