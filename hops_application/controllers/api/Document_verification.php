<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//include Rest Controller library
require APPPATH . 'libraries/REST_Controller.php';

class Document_Verification extends REST_Controller {

	/* Constructor */
	public function __construct()
    {
        parent::__construct();
        $this->load->library('../controllers/api/common/');
        $this->load->model('api/document_verification_model');
    }

    // Check document status
	public function index_get($arg=array())
	{
		
      	$data = $arg;

		if(!empty($data)) {

			$doc_status = $this->document_verification_model->user_document_status($data['user_id']);

			if(!empty($doc_status)) {

				if($doc_status['doc_verify_status'] == 1) {
					$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message' => $this->lang->line('text_doc_submitted'),'server_data'=>$doc_status);
				}
				else if($doc_status['doc_verify_status'] == 2) {
					$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message' => $this->lang->line('text_doc_approved'),'server_data'=>$doc_status);
				}
				else {
					$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message'=>$this->lang->line('text_doc_declined'));
				}
			}
			else {
				$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message'=>$this->lang->line('text_doc_failure'));
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// Document submit
	public function document_submit_post($arg=array())
	{
		
      	$data = $this->input->post();

		if(!empty($data)) {

			$data['user_id'] = $arg['user_id'];

			$check_already = $this->document_verification_model->user_document_status($data['user_id']);

			// continue
			if(!empty($check_already) && $check_already['doc_verify_status'] != 3) {

				$message = ($check_already['doc_verify_status'] == 1) ? $this->lang->line('text_doc_already_submitted') : $this->lang->line('text_doc_already_approved');
				$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message'=>$message);
			}
			else {

				// Create a new directory if not exists
				if(!is_dir('./'.UPLOADS.'document_profile/')) {
					mkdir('./'.UPLOADS.'document_profile/',0777,true);
					// 1- execute 2- write 4- read
					// second parameter - First val is always zero,second one for owner,third one for owner user group,fourth one for everybody
				}

				if(!empty($_FILES['user_image'])) {

					$file_path = '';

					$file_ext = ".".strtolower(end((explode('.',$_FILES['user_image']['name']))));
					$file_name   = time().mt_rand(000,999).$file_ext;
					$config['upload_path'] = "./".UPLOADS."document_profile/";
				    $config['allowed_types'] = '*';
					$config['file_name']   = $file_name;
					$this->load->library('upload', $config);

					if ($this->upload->do_upload('user_image')) {
						$filepath = $config['upload_path'].$file_name;
						$create_thumb = $this->common->create_thumb($filepath);
						$file_path = str_replace("./", "", $filepath);
         			}
         			$data['user_image'] = $file_path;         
				}
				$data['doc_verify_status'] = 1;

				$document_insert = $this->document_verification_model->insert_document($data);

				if($document_insert['status'] == "true") {
					$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message' => $this->lang->line('text_doc_submit'));
				}
				else {
					$response = array('status' => FALSE,'status_code'=>HTTP_SERVER,'message'=>$this->lang->line('text_query_error'));
				}
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

}
// End of document verification controller - api