<?php require('includes/config.php'); 
//if not logged in redirect to login page
//if(!$user->is_logged_in()){ header('Location: login.php'); } 

require('layout/header.php'); 
?>


<?php 
//include header template
require('layout/footer.php'); 
?>
<!DOCTYPE html>
<html>
 <head>
  <title>RocketBaby Dashboard Panel</title>
 <style>
        .buttonStyle{
        margin:15px 0;
        }
 </style>
 </head>
 <body>
 	<h2>Member only page - Welcome <?php echo $_SESSION['username']; ?></h2>
				<p><a href='logout.php'>Logout</a></p>
				<hr>
 <FORM>
<div class="buttonStyle">
 <INPUT TYPE="button" value="Importa" onClick="parent.location='import_20170806.php'">
</div>
<div class="buttonStyle">
 <INPUT TYPE="button" value="Analizza" onClick="parent.location='analyze_20170520.php'">
</div>
 </FORM>


<form action="export.php" method="post">
    <select name="table">
        <option>brt</option>          
		<option>cambi</option>        
		<option>cancellati</option>   
		<option>dashboard</option>    
		<option>dbpo</option>         
		<option>pro</option>          
		<option>ric</option>          
		<option>shopify</option>      
		<option>simfdb</option>       
		<option>soldout</option>      
		<option>spe</option>
			<!-- //Mario 20170806 - STK -->
		<option>stk</option>
			<!-- //Mario 20170810 - STK -->
		<option>stk_dashboard</option>
    <select>
    <input id='submit' type='submit' name = 'export' value = 'Esporta Tabella'>
</form> 

 </body>
</html>   