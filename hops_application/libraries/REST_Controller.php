<?php
defined('BASEPATH') OR exit('No direct script access allowed');

abstract class REST_Controller extends CI_Controller {

    protected $rest = NULL;

    protected $_allow = TRUE;

    protected $is_valid_request = TRUE;   
   
    public function __construct($config = 'rest')
    {
        parent::__construct();

        // Load the rest.php configuration file
        $this->get_local_config($config);
        
        // Get the language
        $language = $this->config->item('rest_language');
       
        // Load the language file
        $this->lang->load('rest_controller', $language, FALSE, TRUE);
        
        // Api key validation
        if ($this->config->item('rest_enable_keys'))
        {
            $this->_allow = $this->_detect_api_key();
        }
    }

    // Load rest.php file and store it in config variable
    private function get_local_config($config_file)
    {
        if(file_exists(__DIR__."/../config/".$config_file.".php"))
        {
            $config = array();
            include(__DIR__ . "/../config/" . $config_file . ".php");
            
            foreach($config AS $key => $value)
            {
                $this->config->set_item($key, $value);
            }
        }
        else {
            throw new Exception($config_file.'.php is missing!');
        }
    }

    // Check whether the given api key is detected or not
    protected function _detect_api_key()
    {
        // Get the api key name variable set in the rest config file
        $api_key_variable = $this->config->item('rest_key_name');

        // Work out the name of the SERVER entry based on config
        $key_name = 'HTTP_' . strtoupper(str_replace('-', '_', $api_key_variable));
     
        $key_val = $this->input->server($key_name);

        if(!empty($key_val)) {

            $row = $this->db->where(array($this->config->item('rest_key_column')=>$key_val,'keys_status'=>1))->get($this->config->item('rest_keys_table'))->row();
            if(!empty($row)) {             
                return TRUE;
            }
        }

        $this->is_valid_request = false;
        $this->response([
                    'status' => FALSE,
                    'message' => $this->lang->line('text_invalid_api_key')
                ],HTTP_BAD_REQUEST);
    }

    // Custom response with header
    public function response($data = NULL, $http_code = 200)
    {   

        if(!empty($data) && !empty($http_code)) {

            $this->output->set_status_header($http_code);
            $this->output->set_content_type('application/json','utf-8');
            $this->output->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))->_display();
        }
        else {

            $data = array('status'=>false,'message'=>'API Not Found');            
            $this->output->set_status_header(HTTP_NOT_FOUND);
            $this->output->set_content_type('application/json','utf-8');
            $this->output->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))->_display();
        }
    
        exit;
    }
 
    // Remap the url
    public function _remap($object_called, $arguments = [])
    {

        $current_controller = $this->router->fetch_class();
        $current_method = $object_called;
        $current_route = $this->uri->segment(1);

        // Authorization
        $auth_key_variable = $this->config->item('auth_key_name');
        $auth_key_name = 'HTTP_' . strtoupper(str_replace('-', '_', $auth_key_variable));
        $auth_key = $this->input->server($auth_key_name);

        // Check auth key
        if($current_controller != 'user') {

            // $user_row = $this->db->select('user_id')->where(array($this->config->item('auth_key_column')=>$auth_key,'logs_login_status'=>1))->get($this->config->item('auth_keys_table'))->row_array();

            $user_row = $this->db->select('u.user_id,u.user_fullname,u.user_email,u.user_mobile,u.user_profile_image,u.user_category')->from('hs_user_device_logs d')->join('hs_users u','u.user_id=d.user_id AND u.user_status=1','inner')->where(array('unique_id'=>$auth_key,'logs_login_status'=>1))->get()->row_array();
            
            if(empty($user_row)) {
                $this->is_valid_request = false;
                $this->response([
                       'status' => FALSE,
                       'status_code'=>HTTP_SESSION_EXPIRE,
                       'message' => $this->lang->line('text_session_expired')
                    ]);
            }
            $arguments = $user_row;
        }


        $method = $current_method."_".$this->input->method();

        // Check whether the given method
        if(!method_exists($this, $method)) {
          
            $this->is_valid_request = false;
            $this->response([
                    'status' => FALSE,
                    'message' => $this->lang->line('text_method_not_found')
                ],HTTP_BAD_REQUEST);
        }

        if($this->is_valid_request) {
            call_user_func([$this, $method], $arguments);    
        }
        else {
            $this->response([
                    'status' => FALSE,
                    'message' => $this->lang->line('text_server_error')
                ],HTTP_SERVER);
        }
    }
}