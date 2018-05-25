<?php

// Mail sending function - user
function send_mail_user($data)
{

	$this_obj =& get_instance();

    $this_obj->email->from(ADMIN_MAIL, ADMIN_NAME); 
    $this_obj->email->to($data['to_email']);    
    $this_obj->email->message($data['message']); 
    //Send mail 
    if($this_obj->email->send()) {
       	return true;
    }
    else {
    	return false;
    }
}

// Mail sending function - admin
function send_mail_admin($data)
{

	$this_obj =& get_instance();

    $this_obj->email->from(ADMIN_MAIL, ADMIN_NAME); 
    $this_obj->email->to(ADMIN_MAIL);
    $this_obj->email->subject($data['subject']); 
    $this_obj->email->message($data['message']); 

    //Send mail 
    if($this_obj->email->send()) {
       	return true;
    }
    else {
    	return false;
    }
}