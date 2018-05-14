<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Notification_model extends CI_Model {

  public function __construct() {
      parent::__construct();
  }

  /* ===========         To get notification list   ======= */
  public function get_notification_list($user_id)
  {

    $where_cond = '(notification_to_id="'.$user_id.'" AND notification_status!=3)';

    $this->db->select('n.notification_id,n.notification_type,n.navigate_id,CONCAT(u.user_fullname," ",n.notification_message) as notification_message,n.notification_status,n.notification_created_date');
    $this->db->from('hs_notifications n');
    $this->db->join('hs_users u','u.user_id=n.notification_from_id','left');

    $model_data = $this->db->where($where_cond)->order_by('notification_id desc')->get()->result_array();

    return $model_data;
  }
    
	/* ===========         To view notification details   ======= */
	public function get_notification_view_details($data)
	{

		switch ($data['notification_type']) {
			case 1:

				$where_cond= '(n.notification_id="'.$data['notification_id'].'" AND n.notification_status!=3)';
			$this->db->select('o.order_id,o.pickup_address,o.drop_address,o.pickup_latitude,o.pickup_longitude,o.drop_latitude,o.drop_longitude,o.pickup_time,o.drop_time,o.package_size,o.package_name,o.package_image,o.total_cost,o.order_status,IFNULL(o.order_cancel_reason,"") as order_cancel_reason,IFNULL(u.user_fullname,"") as user_fullname,IFNULL(u.user_email,"") as user_email,IFNULL(u.user_mobile,"") as user_mobile,IFNULL(u.user_profile_image,"") as user_profile_image');
		  	$this->db->from('hs_notifications n');
		  	$this->db->join('hs_orders o','o.order_id=n.navigate_id','left');
		  	$this->db->join('hs_users u','u.user_id=n.notification_from_id','left');
		  	$model_data = $this->db->where($where_cond)->get()->row_array();
				
				break;

			case 2:

				$where_cond= '(n.notification_id="'.$data['notification_id'].'" AND n.notification_status!=3)';
			$this->db->select('o.order_id,o.pickup_address,o.drop_address,o.pickup_latitude,o.pickup_longitude,o.drop_latitude,o.drop_longitude,o.pickup_time,o.drop_time,o.package_size,o.package_name,o.package_image,o.total_cost,o.order_status,IFNULL(o.order_cancel_reason,"") as order_cancel_reason,IFNULL(u.user_fullname,"") as user_fullname,IFNULL(u.user_email,"") as user_email,IFNULL(u.user_mobile,"") as user_mobile,IFNULL(u.user_profile_image,"") as user_profile_image');
		  	$this->db->from('hs_notifications n');
		  	$this->db->join('hs_orders o','o.order_id=n.navigate_id','left');
		  	$this->db->join('hs_users u','u.user_id=n.notification_from_id','left');
		  	$model_data = $this->db->where($where_cond)->get()->row_array();
			
			default:
				
				$model_data = array();
				break;
		}

    return $model_data;
	}

  /* ===========         To update the notification status   ======= */
  public function update_notification_status($notification_id)
  {
    $notification_data = $this->db->get_where('hs_notifications',array('notification_id'=>$notification_id,'notification_status'=>1))->num_rows();

    if($notification_data > 0) {
      $update_notification = $this->db->where('notification_id',$notification_id)->update('hs_notifications',array('notification_status'=>2,'notification_updated_date'=>date('Y-m-d H:i:s')));
    }

    return TRUE;
  }
	


	

}
// End of notification model - api


?>
