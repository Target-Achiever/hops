<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Document_Verification_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /* ======     User document status    ====== */
	public function user_document_status($user_id)
	{

		$model_data = $this->db->select('user_id,user_fullname,user_age,user_address,user_license_num,user_image,doc_verify_status,IFNULL(doc_verify_approved_date,"") as doc_verify_approved_date,doc_verify_created_date')->order_by('doc_verify_id','desc')->limit(1)->get_where('hs_doc_verify',array('user_id'=>$user_id))->row_array();

		return $model_data;	
	}

	/* =============         Insert user document        ======== */
	public function insert_document($data)
	{

		$model_action = $this->db->insert('hs_doc_verify',$data);
		
		if($model_action) {
			$model_data['insert_id'] = $this->db->insert_id();
			$model_data['status'] = "true";
		}
		else {
			$model_data['status'] = "false";
		}

		return $model_data;
	}
	
}
// End of document verification model - api

?>
