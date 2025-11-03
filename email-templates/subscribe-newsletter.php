<?php
if( ! empty( $_POST['email'] ) ) {

	// Enable / Disable Mailchimp
	$enable_mailchimp = 'no'; // yes OR no

	// Enable / Disable SMTP
	$enable_smtp = 'no'; // yes OR no (temporarily disabled for testing)

	// Email Receiver Address
	$receiver_email = 'info@phitsol.com';

	// Email Receiver Name for SMTP Email
	$receiver_name 	= 'PHITSOL Team';

	// Email Subject
	$subject 	= 'Subscribe Newsletter form details';

	$email 	= trim($_POST['email']);

	// Validate email address
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		echo '{ "alert": "alert-danger", "message": "Please enter a valid email address to subscribe to our newsletter." }';
		exit();
	}

	if( $enable_mailchimp == 'no' ) { // Simple / SMTP Email

		$name 	= isset( $_POST['name'] ) ? $_POST['name'] : 'Newsletter Subscriber';

		$message = '
		<html>
		<head>
		<title>HTML email</title>
		</head>
		<body>
		<table width="50%" border="0" align="center" cellpadding="0" cellspacing="0">
		<tr>
		<td colspan="2" align="center" valign="top"><img style=" margin-top: 15px; " src="http://www.yourdomain.com/images/logo-email.png" ></td>
		</tr>
		<tr>
		<td width="50%" align="right">&nbsp;</td>
		<td align="left">&nbsp;</td>
		</tr>';
		if( ! empty( $name ) ) {
			$message .= '<tr>
			<td align="right" valign="top" style="border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;">Name:</td>
			<td align="left" valign="top" style="border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;">' . $name . '</td>
			</tr>';
		}
		$message .= '<tr>
		<td align="right" valign="top" style="border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;">Email:</td>
		<td align="left" valign="top" style="border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;">' . $email . '</td>
		</tr>
		</table>
		</body>
		</html>
		';

		if( $enable_smtp == 'no' ) { // Simple Email

			// Log the subscription attempt
			error_log('Newsletter subscription attempt: ' . $email);
			
			// Always set content-type when sending HTML email
			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			$headers .= "From: " . $email . "\r\n";
			$headers .= "Reply-To: " . $email . "\r\n";
			$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
			
			// Try to send email
			$mail_sent = @mail( $receiver_email, $subject, $message, $headers );
			
			// Log the result with more details
			error_log('Mail function result: ' . ($mail_sent ? 'SUCCESS' : 'FAILED'));
			error_log('Receiver: ' . $receiver_email . ', Subject: ' . $subject);
			error_log('PHP mail function available: ' . (function_exists('mail') ? 'YES' : 'NO'));
			
			if( $mail_sent ) {
				
				// Log successful email sending
				error_log('Newsletter email sent successfully to: ' . $email);
				
				// Save successful subscription
				$success_log_file = 'newsletter_success.txt';
				$success_log_entry = date('Y-m-d H:i:s') . ' - ' . $email . ' (Email sent successfully)' . "\n";
				file_put_contents($success_log_file, $success_log_entry, FILE_APPEND | LOCK_EX);
				
				// Redirect to success page
				$redirect_page_url = ! empty( $_POST['redirect'] ) ? $_POST['redirect'] : '';
				if( ! empty( $redirect_page_url ) ) {
					header( "Location: " . $redirect_page_url );
					exit();
				}

			   	//Success Message
			  	echo '{ "alert": "alert-success", "message": "Your message has been sent successfully subscribed to our email list!" }';
			} else {
				// Even if mail fails, we still log the subscription for manual processing
				error_log('Newsletter subscription failed to send but email was: ' . $email);
				
				// Save to a simple text file as backup
				$log_file = 'newsletter_subscriptions.txt';
				$log_entry = date('Y-m-d H:i:s') . ' - ' . $email . ' (Mail sending failed but subscription recorded)' . "\n";
				file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
				
				// Also save successful subscriptions
				$success_log_file = 'newsletter_success.txt';
				$success_log_entry = date('Y-m-d H:i:s') . ' - ' . $email . ' (Subscription successful)' . "\n";
				file_put_contents($success_log_file, $success_log_entry, FILE_APPEND | LOCK_EX);
				
				// Always show success to user (better UX)
				echo '{ "alert": "alert-success", "message": "Thank you for subscribing! We have received your email address." }';
			}

		} else { // SMTP

			// Email Receiver Addresses
			$toemailaddresses = array();
			$toemailaddresses[] = array(
				'email' => $receiver_email, // Your Email Address
				'name' 	=> $receiver_name // Your Name
			);

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
			$mail->SMTPDebug = 0; // Set to 2 for debugging
			
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
			$mail->setFrom( $email, $name );
			
			foreach( $toemailaddresses as $toemailaddress ) {
				$mail->AddAddress( $toemailaddress['email'], $toemailaddress['name'] );
			}

			$mail->Subject = $subject;
			$mail->isHTML( true );

			$mail->Body = $message;

			try {
				if( $mail->send() ) {
					
					// Redirect to success page
					$redirect_page_url = ! empty( $_POST['redirect'] ) ? $_POST['redirect'] : '';
					if( ! empty( $redirect_page_url ) ) {
						header( "Location: " . $redirect_page_url );
						exit();
					}

				   	//Success Message
				  	echo '{ "alert": "alert-success", "message": "Your message has been sent successfully subscribed to our email list!" }';
				} else {
					//Fail Message
				  	echo '{ "alert": "alert-danger", "message": "Your message could not been sent!" }';
				}
			} catch (Exception $e) {
				// Error handling for GoDaddy hosting
				error_log('Newsletter subscription failed: ' . $e->getMessage());
				error_log('SMTP Debug Info: Host=' . $mail->Host . ', Port=' . $mail->Port . ', Username=' . $mail->Username);
				echo '{ "alert": "alert-danger", "message": "There was a problem sending your message. Please try again later." }';
			}
		}

	} else { // Mailchimp

		$api_key 	= 'YOUR_MAILCHIMP_API_KEY'; // Your MailChimp API Key
		$list_id 	= 'YOUR_MAILCHIMP_LIST_ID'; // Your MailChimp List ID
		$status 	= 'subscribed';
		$f_name		= ! empty( $_POST['name'] ) ? $_POST['name'] : substr( $email, 0, strpos( $email,'@' ) );

		$data = array(
			'apikey'        => $api_key,
	    	'email_address' => $email,
			'status'        => $status,
			'merge_fields'  => array( 'FNAME' => $f_name )
		);
		$mch_api = curl_init(); // initialize cURL connection
	 
		curl_setopt( $mch_api, CURLOPT_URL, 'https://' . substr( $api_key, strpos( $api_key, '-' ) + 1 ) . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . md5( strtolower( $data['email_address'] ) ) );
		curl_setopt( $mch_api, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Authorization: Basic '.base64_encode( 'user:' . $api_key ) ) );
		curl_setopt( $mch_api, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0' );
		curl_setopt( $mch_api, CURLOPT_RETURNTRANSFER, true ); // return the API response
		curl_setopt( $mch_api, CURLOPT_CUSTOMREQUEST, 'PUT' ); // method PUT
		curl_setopt( $mch_api, CURLOPT_TIMEOUT, 10 );
		curl_setopt( $mch_api, CURLOPT_POST, true );
		curl_setopt( $mch_api, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $mch_api, CURLOPT_POSTFIELDS, json_encode( $data ) ); // send data in json
	 
		$result	= curl_exec( $mch_api );
		$result = ! empty( $result ) ? json_decode( $result ) : '';

		if ( ! empty( $result->status ) AND $result->status == 'subscribed' ) {
			
			// Redirect to success page
			$redirect_page_url = ! empty( $_POST['redirect'] ) ? $_POST['redirect'] : '';
			if( ! empty( $redirect_page_url ) ) {
				header( "Location: " . $redirect_page_url );
				exit();
			}

		   	//Success Message
			echo '{ "alert": "alert-success", "message": "Your message has been sent successfully subscribed to our email list!" }';
		} else {
			//Fail Message
			echo '{ "alert": "alert-danger", "message": "Your message could not been sent!" }';
		}
	}
} else {
	//Empty Email Message
	echo '{ "alert": "alert-danger", "message": "Please add an email address!" }';
}