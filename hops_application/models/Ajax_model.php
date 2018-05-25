<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Ajax_model extends CI_Model {

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
	

}
// End of ajax model - api


?>
