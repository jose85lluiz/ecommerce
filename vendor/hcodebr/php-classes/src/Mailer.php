<?php

namespace Hcode;
use Rain\Tpl;


class Mailer {

	const USERNAME = "joseluizzzzzzeeeeee@gmail.com";
	
	const PASSWORD = "*********";
	const NAME_FROM ="Hcode Store";
    private $mail;
    
       public function __construct($toAddress,$toName,$subject,$tplName,$data = array())
     {

     $config = array(
		"tpl_dir"    => $_SERVER["DOCUMENT_ROOT"]. "/views/email/",
		"cache_dir"  => $_SERVER["DOCUMENT_ROOT"]. "/views-cache/",
		"debug"      => false
				   );

	Tpl::configure( $config );
    
    $tpl = new Tpl;

    foreach ($data as $key => $value) {
    	$tpl ->assign($key, $value);
    }
    $html = $tpl->draw($tplName, true);
    

     $this -> mail = new \PHPMailer(true);

//Tell PHPMailer to use SMTP
$this -> mail->isSMTP();

//Enable SMTP debugging
//SMTP::DEBUG_OFF = off (for production use)
//SMTP::DEBUG_CLIENT = client messages
//SMTP::DEBUG_SERVER = client and server messages
$this -> mail->SMTPDebug = 1;
$this->mail->Debugoutput = 'html';

//Set the hostname of the mail server
$this -> mail->Host ='smtp.gmail.com';
//Use `$this -> mail->Host = gethostbyname('smtp.gmail.com'SERVER
//if your network does not support SMTP over IPv6,
//though this may cause issues with TLS

//Set the SMTP port number:
// - 465 for SMTP with implicit TLS, a.k.a. RFC8314 SMTPS or
// - 587 for SMTP+STARTTLS
$this -> mail->Port = 587;
//Set the encryption mechanism to use:
// - SMTPS (implicit TLS on port 465)
// - STARTTLS (explicit TLS on port 587)
// $this->mail->SMTPOptions = array('ssl'=>array('verify_peer'=>false, 'verify_peer_name'=>false, 'allow_self_signed'=>true)); 

$this -> mail->SMTPSecure = "tls";


//Whether to use SMTP authentication
$this -> mail->SMTPAuth = true;

//$this -> mail->SMTPAutoTLS = false;
//$this -> mail->Authtype = "XOAUTH2";

//Username to use for SMTP authentication - use full email address for gmail
$this -> mail->Username = 'joseluizzzzzeeeeee@gmail.com';
     //Mailer::USERNAME;

//Password to use for SMTP authentication
$this -> mail->Password = Mailer::PASSWORD; 





$this -> mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);

//Set an alternative reply-to address
//This is a good place to put user-submitted addresses
//$this -> mail->addReplyTo('replyto@example.com', 'First Last');

//Set who the message is to be sent to
$this -> mail->addAddress($toAddress, $toName);

//Set the subject line
$this -> mail->Subject = $subject ;

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$this -> mail->msgHTML($html);

//Replace the plain text body with one created manually
$this -> mail->AltBody = 'This is a plain-text message body';

//Attach an image file
//$this -> mail->addAttachment('images/phpmailer_mini.png');

}

public function send()
{

return $this ->mail->send();


}


}


?>