<?php

use PHPUnit\TextUI\XmlConfiguration\UpdateSchemaLocationTo93;

class Auth_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }
    public function validate_email($data)
    {
        $query = $this->db->select('*')
        ->from('tbl_users')
        ->where('email',  $data['email'])   
        ->get();   
        
        return $query->row_array(); 

    }
        public function can_list($data)
    {
        $query = $this->db->select('can')
        ->from('tbl_contract_account_number')
        ->where('uid',  $data['user_id'])   
        ->get();   
        
        return $query->result_array(); 

    }



    public function get_user_data($data)
    {
        $query = $this->db
            ->select('
                tbl_users.*,
                
                tbl_account_type.user_type

            ')
            ->from('tbl_users')
            ->join('tbl_account_type', 'tbl_account_type.at_id = tbl_users.user_id', 'innner')
            
            ->where('tbl_users.username', $data['username'])
            ->where('tbl_users.password', md5($data['password']))
            ->get();

        return $query->row_array();
    }

    public function validate_otp($data)
    {
        $query = $this->db->select('*')
                ->from('tbl_users')
                ->where('otp',  $data['otp'])
                ->where('email',  $data['email'])
                ->get();   
        
        return $query->row_array();    
    }



        public function billing_detail($data)
    {
        $query = $this->db->select('*')
                ->from('tbl_bill')
                ->where('owners_id',  $data['user_id'])
                ->where('can',  $data['can'])
                ->order_by('id', 'DESC')   
                ->limit(1)
                ->get();   
        
        return $query->result_array();    
    }



  public function add_otp($where,$update)
    {
        $this->db->where('email', $where)->update('tbl_users', $update);
 		return true;

    }    

    public function account_type_detail($data)
    {
                $query = $this->db->select('*')
                ->from('account_type')
                ->where('at_id',  $data)
                ->get();   
        
        return $query->row_array();  
    }
    
    public function logoutall($uid)
    {    $update['log_type']="0";
		 $update['log_action']="logout";
              $this->db->where('uid', $uid)->update('users_logs', $update);
        // return $this->db->affected_rows();

              return true;
    }

    	public function insert_users_logs($data)
    {
        return $this->db->insert('users_logs', $data);
    }
	 public function update_to_login($uid)
    {   
            $update['is_online']="1";
              $this->db->where('user_id', $uid)->update('tbl_users', $update);
 				return true;
    }
}