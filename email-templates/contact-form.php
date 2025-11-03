<?php
// Enable error reporting for debugging (disabled for production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Set content type for JSON response (only if not CLI)
if (!php_sapi_name() === 'cli') {
    header('Content-Type: application/json');
    
    // Enable CORS for AJAX requests
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type');
}

if( ! empty( $_POST['email'] ) ) {

	// Enable / Disable SMTP
	$enable_smtp = 'no'; // yes OR no - Set to 'no' for local development (XAMPP)

	// Email Receiver Address
	$receiver_email = 'info@phitsol.com';

	// Email Receiver Name for SMTP Email
	$receiver_name 	= 'PHITSOL Team';

	// Email Subject
	$subject = 'New Contact Form Submission - PHITSOL';

	// Google reCaptcha secret Key (disabled for now - can be enabled later)
	$grecaptcha_secret_key = ''; // Disabled for testing

	$from 	= $_POST['email'];
	$name 	= isset( $_POST['name'] ) ? $_POST['name'] : '';

	if( ! empty( $grecaptcha_secret_key ) && ! empty( $_POST['g-recaptcha-response'] ) ) {

		$token = $_POST['g-recaptcha-response'];

		// call curl to POST request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://www.google.com/recaptcha/api/siteverify");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( array( 'secret' => $grecaptcha_secret_key, 'response' => $token ) ) );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		$arrResponse = json_decode($response, true);

		// verify the response
		if( isset( $_POST['action'] ) && ! ( isset( $arrResponse['success'] ) && $arrResponse['success'] == '1' && $arrResponse['action'] == $_POST['action'] && $arrResponse['score'] = 0.5 ) ) {

			echo '{ "alert": "alert-danger", "message": "Your message could not been sent due to invalid reCaptcha!" }';
			die;

		} else if( ! isset( $_POST['action'] ) && ! ( isset( $arrResponse['success'] ) && $arrResponse['success'] == '1' ) ) {

			echo '{ "alert": "alert-danger", "message": "Your message could not been sent due to invalid reCaptcha!" }';
			die;
		}
	}

	if( !isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] == 'POST' ) {

		$prefix		= !empty( $_POST['prefix'] ) ? $_POST['prefix'] : '';
		$submits	= $_POST;
		$botpassed	= false;

		$fields = array();
		foreach( $submits as $name => $value ) {
			if( empty( $value ) ) {
				continue;
			}

			$name = str_replace( $prefix , '', $name );
			$name = function_exists('mb_convert_case') ? mb_convert_case( $name, MB_CASE_TITLE, "UTF-8" ) : ucwords($name);

			if( is_array( $value ) ) {
				$value = implode( ', ', $value );
			}

			$fields[$name] = nl2br( filter_var( $value, FILTER_SANITIZE_SPECIAL_CHARS ) );
		}

		$response = array();
		foreach( $fields as $fieldname => $fieldvalue ) {
			
                    $fieldname = '<tr>
                                                            <td align="right" valign="top" style="border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;">' . $fieldname . ': </td>';
                    $fieldvalue = '<td align="left" valign="top" style="border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;">' . $fieldvalue . '</td>
                                                    </tr>';
                    $response[] = $fieldname . $fieldvalue;

		}

		$message = '<html>
			<head>
				<title>HTML email</title>
			</head>
			<body>
				<table width="50%" border="0" align="center" cellpadding="0" cellspacing="0">
				<tr>
				<td colspan="2" align="center" valign="top"><img style="margin-top: 15px;" src="http://www.yourdomain.com/images/logo-email.png" ></td>
				</tr>
				<tr>
				<td width="50%" align="right">&nbsp;</td>
				<td align="left">&nbsp;</td>
				</tr>
				' . implode( '', $response ) . '
				</table>
			</body>
			</html>';
		if( $enable_smtp == 'no' ) { // Simple Email

			// Always set content-type when sending HTML email
			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			// More headers
			$headers .= 'From: ' . $fields['Name'] . ' <' . $fields['Email'] . '>' . "\r\n";
			$headers .= 'Reply-To: ' . $fields['Email'] . "\r\n";
			$headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
			
			// For local development, we'll simulate success since mail() might not work
			$is_local_dev = (php_sapi_name() === 'cli' || 
							(isset($_SERVER['HTTP_HOST']) && (
								strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
								strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
								strpos($_SERVER['HTTP_HOST'], 'xampp') !== false
							)));
			
			if( $is_local_dev ) {
				// Log the email content for local development
				error_log('LOCAL DEV - Contact form submission:');
				error_log('To: ' . $receiver_email);
				error_log('Subject: ' . $subject);
				error_log('From: ' . $fields['Name'] . ' <' . $fields['Email'] . '>');
				error_log('Message: ' . strip_tags($message));
				
				// Simulate success for local development
				echo '{ "alert": "alert-success", "message": "Your message has been received! (Local development mode - email logged to server)" }';
			} else {
				// Try to send actual email in production
				if( mail( $receiver_email, $subject, $message, $headers ) ) {
					// Redirect to success page
					$redirect_page_url = ! empty( $_POST['redirect'] ) ? $_POST['redirect'] : '';
					if( ! empty( $redirect_page_url ) ) {
						header( "Location: " . $redirect_page_url );
						exit();
					}

				   	//Success Message
				  	echo '{ "alert": "alert-success", "message": "Your message has been sent successfully!" }';
				} else {
					//Fail Message
					error_log('PHP mail() function failed');
				  	echo '{ "alert": "alert-danger", "message": "Your message could not be sent. Please try again or contact us directly." }';
				}
			}
			
		} else { // SMTP
			// Email Receiver Addresses
			$toemailaddresses = array();
			$toemailaddresses[] = array(
				'email' => $receiver_email, // Your Email Address
				'name' 	=> $receiver_name // Your Name
			);

			// Check if PHPMailer files exist
			if (!file_exists('phpmailer/Exception.php') || !file_exists('phpmailer/PHPMailer.php') || !file_exists('phpmailer/SMTP.php')) {
				error_log('PHPMailer files not found');
				echo '{ "alert": "alert-danger", "message": "Email system configuration error. Please contact support." }';
				exit();
			}

			require 'phpmailer/Exception.php';
			require 'phpmailer/PHPMailer.php';
			require 'phpmailer/SMTP.php';

			$mail = new PHPMailer\PHPMailer\PHPMailer(true); // Enable exceptions

			// SMTP Configuration for GoDaddy Hosting
			$mail->isSMTP();
			$mail->Host     = 'smtpout.secureserver.net'; // GoDaddy SMTP Host
			$mail->SMTPAuth = true;
			$mail->Username = 'info@phitsol.com'; // GoDaddy Email
			$mail->Password = 'Kkcc2025#'; // GoDaddy Password
			$mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS; // SSL/TLS
			$mail->Port     = 465; // GoDaddy SMTP Port
			
			// Additional GoDaddy-specific settings
			$mail->SMTPOptions = array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				)
			);
			$mail->Timeout = 60; // 60 seconds timeout
			$mail->SMTPKeepAlive = true; // Keep connection alive
			$mail->CharSet = 'UTF-8'; // Set character encoding
			
			// Debug mode for live environment (set to 0 for production)
			$mail->SMTPDebug = 0; // Set to 2 for debugging
			
			// Set from address - use the form submitter's email
			$from_email = !empty($fields['Email']) ? $fields['Email'] : 'noreply@phitsol.com';
			$from_name = !empty($fields['Name']) ? $fields['Name'] : 'Contact Form';
			$mail->setFrom($from_email, $from_name);
			
			// Add reply-to address
			$mail->addReplyTo($from_email, $from_name);
			
			foreach( $toemailaddresses as $toemailaddress ) {
				$mail->AddAddress( $toemailaddress['email'], $toemailaddress['name'] );
			}

			$mail->Subject = $subject;
			$mail->isHTML( true );
			$mail->Body = $message;
			
			// Add alternative text version
			$mail->AltBody = strip_tags($message);

			try {
				if( $mail->send() ) {
					
					// Log successful email
					error_log('Contact form email sent successfully to: ' . $receiver_email);
					
					// Redirect to success page
					$redirect_page_url = ! empty( $_POST['redirect'] ) ? $_POST['redirect'] : '';
					if( ! empty( $redirect_page_url ) ) {
						header( "Location: " . $redirect_page_url );
						exit();
					}

				  	//Success Message
				 	echo '{ "alert": "alert-success", "message": "Your message has been sent successfully!" }';
				} else {
					//Fail Message
					error_log('PHPMailer send() returned false');
				 	echo '{ "alert": "alert-danger", "message": "Your message could not be sent. Please try again." }';
				}
			} catch (Exception $e) {
				// Error handling for GoDaddy hosting
				$error_message = 'Email sending failed: ' . $e->getMessage();
				error_log($error_message);
				
				// Log additional debugging info
				error_log('PHPMailer Error Info: ' . $mail->ErrorInfo);
				error_log('Form data: ' . print_r($_POST, true));
				
				echo '{ "alert": "alert-danger", "message": "Email service temporarily unavailable. Please try again later or contact us directly." }';
			}
		}
	}
} else {
	//Empty Email Message
	echo '{ "alert": "alert-danger", "message": "Please add an email address!" }';
}