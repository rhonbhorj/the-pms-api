<?php

use PHPUnit\TextUI\XmlConfiguration\UpdateSchemaLocationTo93;

class Record_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
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
        public function validate_session($data)
    {
        $sql = "select * from users_logs where  sess_id like ? and log_type  ='1'";
        $Q = $this->db->query($sql, array(
            $data['session_id']
        ));
        return $Q->row_array() ? $Q->row_array() : false;
    }

    public function get_transaction_detail($data)
    {
        $query = $this->db->select('*')
        ->from('tbl_transactions') 
         ->where('reference_number',$data['reference_number'])
        ->get();   
        
        return $query->row_array() ? $query->row_array(): false;

    }

  public function check_bill_data($schedule_data)
    {
        $query = $this->db->select('can')
        ->from('tbl_bill')
        ->where('can', $schedule_data['can'])  
        ->where('sched_ticket',$schedule_data['reader_ticket'])
        ->where('status','pending')

        ->get();   
        
        return $query->row_array() ? true : false;

    }
 
    public function check_reader_data($can,$ticket)
    {
        $query = $this->db->select('can')
        ->from('tbl_reading_schedule')
        ->where('can', $can)  
        ->where('sched_ticket',$ticket)
        // ->where('status','success')
        ->get();   
        
        return $query->row_array() ? true : false;

    }
    public function can_details($can)
    {
        $query = $this->db->select('*')
        ->from('tbl_users')
        ->where('tbl_users.can', $can)
        ->join('tbl_contract_account_number', 'tbl_contract_account_number.can = tbl_users.can', 'inner')
        ->get();   
        
        return $query->row_array() ? $query->row_array() : false;


    }



    public function can_list($data)
    {
        $query = $this->db->select('can')
        ->from('tbl_contract_account_number')
        ->where('uid',  $data['user_id'])   
        ->get();   
        
        return $query->result_array(); 

    }

    public function billing_detail($data)
    {
        $query = $this->db->select('*')
                ->from('tbl_bill')
                ->where('can',  $data['can'])
                ->order_by('id', 'DESC')   
                ->limit(1)
                ->get();   
        
        return $query->result_array();    
    } 
        public function billing_details($data)
    {
        $query = $this->db->select('*')
                ->from('tbl_bill')
                ->where('can',  $data['can'])
                ->order_by('id', 'DESC')   
              
                ->get();   
        
        return $query->result_array();    
    } 
    public function user_data($data)
    {
        $query = $this->db->select('user_id,username,company_id,email,first_name,last_name,account_type_id')
                ->from('tbl_users')
                ->where('user_id',  $data['uid'])
              
                ->get();   
        
        return $query->row_array();   
    }
    public function get_unpaid_list()
    {
        $query = $this->db->select('tbl_contract_account_number.*,tbl_users.*')
            ->from('tbl_contract_account_number')

            ->join('tbl_users', 'tbl_users.can = tbl_contract_account_number.can', 'inner')
            ->where('tbl_contract_account_number.status', 'active')
            ->where('tbl_contract_account_number.last_read <= DATE_SUB(NOW(), INTERVAL 5 DAY)', null, false)
            ->get();

        return $query->result_array();
    }
    public function get_billing_unscheduled()
    {
            $query = $this->db->select('tbl_contract_account_number.*,tbl_reading_schedule.*')
            ->from('tbl_contract_account_number')

            ->join('tbl_reading_schedule', 'tbl_reading_schedule.can = tbl_contract_account_number.can', 'inner')
            // ->where('tbl_contract_account_number.biling_status', 'for_sched_billing')
            ->where('tbl_reading_schedule.status', 'success')
            ->where('tbl_reading_schedule.sched_billing_status','for_sched_billing')
            ->get();

        return $query->result_array();
    }
    public function get_officer_list($company_id,$officer)
    {
        $account_type_id = ($officer == 'READER') ? 4 : 6;
        $query = $this->db->select('*')
            ->from('tbl_users')
            ->where_in('account_type_id',$account_type_id)
            ->where('status','activate')
            ->where('company_id',$company_id)
            ->get();

        return $query->result_array();
    }

    // public function get_reading_list($data)
    // {
    //     $query = $this->db->select('
    //             tbl_bill.*,
    //        tbl_users.address,
    //             tbl_users.email
    //         ')
    //         ->from('tbl_bill')
    //         ->join('tbl_users', 'tbl_users.can = tbl_bill.can', 'inner')
    //         ->where('tbl_bill.assign_reader_officer', $data['uid'])
    //         ->where('tbl_bill.status', 'for_reading')
    //         ->get();

    //     return $query->result_array();
    // }


    public function get_reading_list($data,$company_id)
    {
     $query = $this->db->select('*')
            ->from('tbl_reading_schedule')
            ->where_in('officer_uid',$data['uid'])
            ->where('status','for_reading')
            ->where('company_id',$company_id)
            ->get();

            return $query->result_array() ? $query->result_array() : false;
    }
    public function get_reading_history($data,$company_id)
    {

     $query = $this->db->select('*')
            ->from('tbl_reading_schedule')
            ->where_in('officer_uid',$data['uid'])
            ->where('status','success')
            ->where('company_id',$company_id)
            ->get();

            return $query->result_array() ? $query->result_array() : false;


    }    

    public function get_billing_history($data,$company_id)
    {

     $query = $this->db->select('*')
            ->from('tbl_biller_schedule')
            ->where_in('officer_uid',$data['uid'])
            ->where('status','success')
            ->where('company_id',$company_id)
            ->get();

            return $query->result_array() ? $query->result_array() : false;


    }   

    
    public function insert_reading($data)
    {
        $this->db->insert('tbl_bill', $data);
        return $this->db->insert_id();
    }
    public function insert_schedules($insert_data)
    {
         $this->db->trans_begin();

        if (!empty($insert_data)) {

                
            $this->db->insert_batch('tbl_reading_schedule', $insert_data);
        }

        if ($this->db->trans_status() === FALSE) {

            $this->db->trans_rollback();

            return  false;
          
        }else{

            $this->db->trans_commit();

            return true;

            }

        
    }

    public function insert_biller_schedules($insert_data)
    {

                 $this->db->trans_begin();

        if (!empty($insert_data)) {

                
            $this->db->insert_batch('tbl_biller_schedule', $insert_data);
        }

        if ($this->db->trans_status() === FALSE) {

            $this->db->trans_rollback();

            return  false;
          
        }else{

            $this->db->trans_commit();

            return true;

            }
        

    }

    public function cancel_for_reading($can, $table,$status)
    {
        return $this->db
        ->where('can', $can)
        ->where('status',$status )
        ->update($table, [
            'status' => 'cancel'
        ]);


    }
    public function update_can_status($data)
    {
        return $this->db->update_batch('tbl_contract_account_number', $data,'can');
    } 
    public function update_reader_status($data)
    {       
        $update['sched_billing_status'] ="success";
        $this->db->where('sched_ticket',$data['reader_ticket']);
        $this->db->where('can',$data['can']);
        return $this->db->update('tbl_reading_schedule',$update);
    }







    public function update_to_cancel($data)
    {
        $update = [
            'status' => 'CANCELED',
                'modified_at'=> date('Y-m-d H:i:s')
        ];

      

        $this->db->where('reference_number', $data['reference_number']);

        return $this->db->update('tbl_transactions', $update);
    }  
}