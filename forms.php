<!-- ==================ON THE FORM PAGE======================== -->
<!-- On form page to see if they have JS enabled on submision-->

<script type=\"text/javascript\">
	$(document).ready(function(){
		$('<input>').attr({
		    type: 'hidden',
		    name: 'JQcheck',
		    value: 'hasJQ'
		}).appendTo('form#contact');
	}); //END doc.ready
</script>


<?php
// When they try to submit the form without JS enabled it will redirect them back to form page with querry string "?error=nojava" and this will fire.
//    We are grabbing there name from the form to personalize the message
	if (isset($_GET['error'])){
		echo "<p class=\"error\"><i><b>ERROR:</b> ".$_GET['first_name'].", you do not have javascript enabled...</p>";
  }
?>

<!-- Simple contact form -->
<form action="parse.php" method="post" name="contact" id="contact">
	<span id="firstName">
  		<input type="text" name="firstName" size="18" tabindex="20" placeholder="First Name*" required/>
	</span>
	<span id="lastName">
		<input type="text" name="lastName" size="30" tabindex="30" placeholder="Last Name*" required/>
	</span>
	<span id="phone">
		<input type="text" name="telephone" size="30"tabindex="40" placeholder="Phone*" required/>
	</span>
	<span id="email">
		<input type="email" name="email" size="30" tabindex="50" placeholder="Email*" required/>
	</span>
	<textarea name="message" cols="28" rows="6" tabindex="70" placeholder="please enter your question here:"></textarea>
	<input class="contact-submit" tabindex="80" name="submit" type="submit" value="submit contact" />
</form>


<!-- ==================ON PARSE.PHP======================== -->
<!-- ============(proccess form on server)================= -->
<?php
//for sending mail [on github](https://github.com/PHPMailer/PHPMailer)
require_once("../assets/PHPMailer/PHPMailerAutoload.php");

//set some variables from the form inputs
$first_name = trim($_POST['firstName']);
// Build the query string to be attached to the redirected URL
$query_string = '?first_name=' . $first_name;
// Redirection domain and phisical dir
$server_dir = $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/';
$next_page = 'submit.php';
/* The header() function sends a HTTP message The 303 code asks the server to use GET when redirecting to another page */
header('HTTP/1.1 303 See Other');

//check for our hidden input if not there send back to form and display error
if (!isset($_POST['JQcheck'])){
	// Back to contact page
   	$next_page = 'index.php';
   	// Add error message to the query string
	$error = "nojava";
  	$query_string .= '&error=' . $error;
  	// redirect to contact page
  	 header('Location: http://' . $server_dir . $next_page . $query_string);
}else{ //if there see if $_POST[] is an array
	if (is_array($_POST)){
		$body  = sprintf("<html>"); 
		$body .= sprintf("<body>");
		$body .= sprintf("<h2>Contact form submission results:</h2>\n");
		$body .= sprintf("<hr />");
		$body .= sprintf("\nName: <strong>%s %s</strong><br />\n",$_POST['firstName'],$_POST['lastName']);
		$body .= sprintf("\nTelephone: <strong>%s</strong><br />\n",$_POST['telephone']);
		$body .= sprintf("\nEmail: <strong>%s</strong><br />\n",$_POST['email']);
		//check if message filled out. If so strip_tags and wordwrap 75 char limit and add \n's
		if ($_POST['message'] == "please enter your question here:"){
			$message = "Woops! the visitor didn't leave a message";
		}else{
			$message = $_POST['message'];
		}
		$messageSafe = strip_tags($message);
		$body .= wordwrap(sprintf("\nMessage:\n\n".$messageSafe."<br />",75,"\n"));

		//check if email submited and then compile email to send
		if ($_POST['email']){
			$mail = new PHPMailer;
			$mail->setFrom($_POST['email'], $_POST['firstName']." ".$_POST['lastName']);
			$mail->addReplyTo($_POST['email'], $_POST['firstName']." ".$_POST['lastName']);
			$mail->addAddress('reciever@theirEmail.com');
			$mail->Subject = "Contact From - " . $_POST['firstName'];
			$mail->msgHTML($body);
			//send the email and check for errors
			if (!$mail->send()){
    			$status = "error"; 										//for query on redirect
    			$mail_error = $mail->ErrorInfo;							//put error into variable
    			date_default_timezone_set('America/Los_Angeles');		//set date time
				$error_date = date('m\-d\-Y\-h:iA');					//set date/time for error
    			$log = "assets/logs/error.txt";							//set error log file name and location
				$fp = fopen($log,"a+");									//open or create log
				fwrite($fp,$error_date . "\n" . $mail_error . "\n\n");	//append error and time stamp to log
				fclose($fp);											//close log file
				//redirect and notify visitor of error with querry string the same way JScheck error is done
    			header('Location: http://' . $server_dir . $next_page . $query_string . $status);
			}else{
				//set querry string for successfull form sending
    			$status = "success";
    			//redirect and notify visitor of successfull form completion the same way JScheck error is done
    			header('Location: http://' . $server_dir . $next_page . $query_string . $status);
			}
		}
	}
}
?>