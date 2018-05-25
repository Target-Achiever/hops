<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//include Rest Controller library
require APPPATH . 'libraries/REST_Controller.php';

class Package extends REST_Controller {

	/* Constructor */
	public function __construct()
    {
        parent::__construct();
        $this->load->library('../controllers/api/common/');
        $this->load->model('api/package_model');
    }

    // Package cost calculation
	public function package_cost_get($arg=array())
	{
		
      	$data = $this->input->get();

		if(!empty($data)) {

			$total_cost = 0;

			// -------

			// $distance_cost = DISTANCE_COST * $data['distance'];
			// $package_cost = $this->package_model->package_cost($data['package_size']);

			// $total_cost = $distance_cost + $package_cost;

			// -------- OR
			$package_cost = $this->package_model->package_cost($data['package_size']);
			$total_cost = $data['distance'] * $package_cost;			


			if($total_cost != 0) {
				$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message' => $this->lang->line('text_cost_success'),'server_data'=>array('cost'=>$total_cost));
			}
			else {
				$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message' => $this->lang->line('text_cost_failure'));
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// Order submit
	public function order_submit_post($arg=array())
	{
			
     	$data = $this->input->post();

		if(!empty($data)) {

			$file_path = '';
			$user_id = $arg['user_id'];
			$data['user_id'] = $user_id;

			// if($arg['user_category'] != 1) {
			// 	$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message' => 'Something went wrong. Please try again later');
			// 	$this->response($response);
			// }


			// check eligible condition - check whether the mobile number is updated or not, check whether the user has valid card details or not, check whether the user has any pending payment

			$user_eligibility = $this->package_model->check_user_cards($user_id);

			$err_message = (!empty($arg['user_mobile'])) ? (($user_eligibility > 0) ? '' : $this->lang->line('text_card_not_update'))  : $this->lang->line('text_mobile_not_update');
			$status_type = (!empty($arg['user_mobile'])) ? (($user_eligibility > 0) ? '' : 2)  : 1;
			
			// status type --  1(mobile), 2(card), 3(notification sent), 4(pending payment)
			if(empty($err_message)) {

				// -- have to check the user has pending payment or not
				// $last_order_paid = $this->package_model->check_last_order_paid($user_id);
				$last_order_paid['status'] = "true";

				if($last_order_paid['status'] == "false") {
					$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message' => $this->lang->line('text_pending_payment'),'status_type'=>4);
					$this->response($response);
				}

				$notifier_name = $arg['user_fullname'];

				$data['distance'] = DISTANCE;
				$searched_user_list = $this->package_model->search_user_list($data);

				if(!empty($searched_user_list)) {


					// Image upload
					if(!empty($_FILES['package_image'])) { 

						$file_ext = ".".strtolower(end((explode('.',$_FILES['package_image']['name']))));
						$file_name   = time().mt_rand(000,999).$file_ext;
						$config['upload_path'] = "./".UPLOADS."package/";
					    $config['allowed_types'] = '*';
						$config['file_name']   = $file_name;
						$this->load->library('upload', $config);

						if ($this->upload->do_upload('package_image')) {
							$filepath = $config['upload_path'].$file_name;
							$create_thumb = $this->common->create_thumb($filepath);
							$file_path = str_replace("./", "", $filepath);
		     			}
					}

					$data['package_image'] = $file_path;
					$data['order_status'] = 1;
					$data['orders_updated_date'] = date('Y-m-d H:i:s');
					unset($data['distance']);

					$insert_order = $this->package_model->insert_order($data);
						
					if($insert_order['status'] == "true") {

						// Notification
						foreach ($searched_user_list as $nk => $nv) {
							
							// Save notifications
							$notification_data[] = array('notification_from_id'=> $data['user_id'],'notification_to_id'=> $nv['user_id'],'notification_type'=> 1,'navigate_id'=>$insert_order['insert_id'],'notification_message'=>"wants to deliver the package",'notification_status'=> 1,'notification_updated_date'=>date('Y-m-d H:i:s'));
						}

						$save_notifications = $this->package_model->save_notifications_batch($notification_data);

						// Push notification
						if(!empty($save_notifications['insert_ids'])) {

							$message = "$notifier_name wants to deliver the package";
							$notification_ids = $save_notifications['insert_ids'];

							$device_details = array();

							foreach ($searched_user_list as $uk => $uv) {

								if($uv['logs_login_status'] == 1 && $uv['push_alert'] == 1) {
									
									$notification_id_key = array_search($uv['user_id'], array_column($notification_ids, 'notification_to_id'));
									$uv['notification_id'] = $notification_ids[$notification_id_key]['notification_id'];
									if($uv['logs_device_type'] == 1) {
										$device_details['android'][] = array('logs_device_token'=>$uv['logs_device_token'],'notification_id'=>$uv['notification_id']);
									}
									else {
										$device_details['ios'][] = array('logs_device_token'=>$uv['logs_device_token'],'notification_id'=>$uv['notification_id']);
									}
								}								
							}

							// Push
							if(!empty($device_details)) {

								$msg = array (
									'title' => "You have a new notification.",
									'message' => $message,
									'notification_type' => 1,
									'notification_from_id' => $data['user_id'],
									'navigate_id' => $insert_order['insert_id'] // order id
								);
								$send_notification = $this->common->multiple_push_notification_service($device_details,$msg);
							}
						}

						$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'status_type'=>3,'message' => $this->lang->line('text_order_request'));
					}
					else {
						$response = array('status' => FALSE,'status_code'=>HTTP_SERVER,'message' => $this->lang->line('text_query_error'));
					}
				}
				else {
					$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message' => $this->lang->line('text_no_records'));
				}
			}
			else {
				$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'status_type'=>$status_type,'message'=>$err_message);
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// Orders list
	public function orders_list_get($arg=array())
	{
		
      	$data = $this->input->get();

		if(!empty($data)) {

			$user_id = $arg['user_id'];

			if($data['action'] == "user") {

				$orders_list = $this->package_model->get_orders_list_user($user_id);

				if(!empty($orders_list)) {

					$order_array['in_progress'] = array();
					$order_array['completed'] = array();

					$orders_data = array_walk($orders_list,function($v,$k) use (&$order_array) {
										if($v['order_status'] == 1 || $v['order_status'] == 2 || $v['order_status'] == 4) {
											$order_array['in_progress'][] = $v;
										}
										else {
											$order_array['completed'][] = $v;
										}
								    });

					$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'server_data'=>$order_array,'message'=>$this->lang->line('text_list_success'));
				}
				else {
					$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message' => $this->lang->line('text_no_records'));
				}
			}
			else {

				$orders_list = $this->package_model->get_orders_list_hopper($user_id);

				if(!empty($orders_list)) {

					$schedules_array['accepted'] = array();
					$schedules_array['completed'] = array();

					$orders_data = array_walk($orders_list,function($v,$k) use (&$schedules_array) {
										if($v['schedule_status'] == 1) {
											$schedules_array['accepted'][] = $v;
										}
										else {
											$schedules_array['completed'][] = $v;
										}
								    });

					$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'server_data'=>$schedules_array,'message'=>$this->lang->line('text_list_success'));
				}
				else {
					$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message' => $this->lang->line('text_no_records'));
				}
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// Accept the order
	public function accept_orders_post($arg=array())
	{
		
      	$data = json_decode(file_get_contents('php://input'),true);

		if(!empty($data)) {

			$data['user_id'] = $arg['user_id'];

			$order_data = $this->package_model->check_order($data['order_id']);

			if(!empty($order_data))
			{

				if($arg['user_category'] == 1 || empty($arg)) {
					$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message' => $this->lang->line('text_something_wrong'));
				}

				$notifier_name = (!empty($arg['user_fullname'])) ? ucfirst($arg['user_fullname']) : 'User';

				$data['pickup_time'] = $order_data['pickup_time'];
				$data['drop_time'] = $order_data['drop_time'];
				$data['current_date'] = date('Y-m-d H:i:s');

				$check_hopper_time = $this->package_model->check_hopper_time($data);

				if(!empty($check_hopper_time)) {
					$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message' => $this->lang->line('text_schedule_error'));
					$this->response($response);
				}

				$create_schedule = $this->package_model->create_hopper_schedule($data);

				if($create_schedule['status'] == "true") {

					$package_name = $order_data['package_name'];
					$order_user_id = $order_data['user_id'];

					$message = 'has accepted your order- '.$package_name;

					// Save notifications
					$notification_data = array('notification_from_id'=> $data['user_id'],'notification_to_id'=> $order_data['user_id'],'notification_type'=> 2,'navigate_id'=> $create_schedule['insert_id'],'notification_message'=>$message,'notification_status'=> 1,'notification_updated_date'=>date('Y-m-d H:i:s'));

					$save_notifications = $this->package_model->save_notifications($notification_data);

					// Push notification
					if($save_notifications['status'] == "true") {

						$message = "$notifier_name has accepted your order- $package_name";
						$notification_id = $save_notifications['insert_id'];

						$device_details = $this->package_model->get_users_device_details($order_user_id);

						// Push
						if(!empty($device_details)) {

							$msg = array (
									'title' => "You have a new notification.",
									'message' => $message,
									'notification_type' => 2,
									'notification_id' => $save_notifications['insert_id'],
									'navigate_id' => $create_schedule['insert_id'] // order id
								);

							$send_notification = $this->common->single_push_notification_service($device_details,$msg);
						}
					}

					$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message'=>$this->lang->line('text_order_accpet_success'));
				}
				else {
					$response = array('status' => FALSE,'status_code'=>HTTP_SERVER,'message' => $this->lang->line('text_query_error'));
				}
			}
			else {
				$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message' => $this->lang->line('text_order_accpet_error'));
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// Order submit - schedule
	// public function order_submit_details_post($arg=array())
	// {
			
 //     	$data = $this->input->post();

	// 	if(!empty($data)) {

	// 		$user_id = $arg['user_id'];
	// 		$data['user_id'] = $user_id;
	// 		$_POST['user_id'] = $user_id;


	// 		$data['distance'] = DISTANCE;
	// 		$searched_user_list = $this->package_model->search_user_list($data);

	// 		// print_r($searched_user_list);
	// 		// exit;


	// 		if(!empty($searched_user_list)) {

	// 			$user_id = $searched_user_list[0]['user_id'];
	// 			$orders_user_id_array[] = $user_id;

	// 			$insert_order = $this->package_model->insert_test(array('test_value'=>$user_id,'order_value'=>$data['order_name']));

	// 			sleep(3);
	// 			$this->orders_search_extend($orders_user_id_array);
	// 		}
	// 		else {
	// 			$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'status_type'=>$status_type,'message'=>$err_message);
	// 		}
	// 	}
	// 	else {
	// 		$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
	// 	}

	// 	$this->response($response);
	// }

	// Order submit - schedule1
	// public function orders_search_extend($orders_array)
	// {

	// 	$data = $this->input->post();

	// 	$data['distance'] = DISTANCE;
	// 	$searched_user_list = $this->package_model->search_user_list_schedule($data,$orders_array);

	// 	if(!empty($searched_user_list)) {

	// 		$user_id = $searched_user_list[0]['user_id'];
	// 		$orders_user_id_array[] = $user_id;

	// 		$insert_order = $this->package_model->insert_test(array('test_value'=>$user_id,'order_value'=>$data['order_name']));

	// 		sleep(3);
	// 		$this->orders_search_extend($orders_user_id_array);
	// 	}
	// 	else {
	// 		echo "completed";
	// 	}
			
     	
	// }

}
// End of package controller - api