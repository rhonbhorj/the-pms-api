<?php

use PHPUnit\TextUI\XmlConfiguration\UpdateSchemaLocationTo93;

class Account_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }



	    public function validate_session($data)
    {
        $sql = "select * from users_logs where  sess_id like ? and log_type  ='1'";
        $Q = $this->db->query($sql, array(
            $data['sess_id']
        ));
        return $Q->row_array() ? $Q->row_array() : false;
    }




    public function get_account_type()
    {
       $query = $this->db->select( 'at_id , user_type, status , group_id' )
                ->from('account_type')
                ->where('status', 'active')
                ->get();   
        
        return $query->result_array();
    }


    public function chk_username_exist($data)
    {
        $sql = "select * from tbl_users   where username ='". $data."'";
        $Q = $this->db->query($sql);
        return $Q->row_array() ? $Q->row_array() : false;


    
    }
   
    public function insert_deposit($pdata)
    {
        return $this->db->insert('tbl_deposit', $pdata);
    }

	 public function update_to_login($uid)
    {    $update['is_online']="1";
		
              $this->db->where('user_id', $uid)->update('tbl_users', $update);
 				return true;
    }
 
		 public function logoutall($uid)
    {    $update['log_type']="0";
		 $update['log_action']="logout";
              $this->db->where('uid', $uid)->update('users_logs', $update);
        // return $this->db->affected_rows();

              return true;
    }
			 public function logout($uid)
    {    $update['log_type']="0";
		 $update['log_action']="logout";
	
             $this->db->where(array(
				'uid' => $uid['uid'],
				'sess_id' => $uid['session_id']
			))->update('users_logs', $update);
        return $this->db->affected_rows();

            //   return true;
    }
}