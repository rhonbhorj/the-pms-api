<?php

class Middleware_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    public function get_reading_list($data)
    {
        $query = $this->db->select('billing_ref,company_id,
        can,
        prev_reading,
        pres_reading,
        prev_consumption,
        pres_consumption,
        total_consumption,amount,due_date,status')
        ->from('tbl_bill')
        ->where('can', $data['can'])
        ->order_by('id', 'DESC')   // change 'id' to your desired column
        ->limit(1)
        ->get();   
        
      return $query->result_array() ? $query->result_array() : false;
    }
    public function validate_can($data)
    {
                $query = $this->db->select('*')
                ->from('tbl_contract_account_number')
                ->where('can', $data['can'])
                ->order_by('can_id', 'DESC')   // change 'id' to your desired column
                ->limit(1)
                ->get();   
        
        return $query->result_array();

    }
        function chk_token($company_id,$token)
    {
        
        $sql = "select * from client_token  where company_id = ? and token = ? and status = 'ACTIVE'";

        $Q   = $this->db->query($sql,
                array($company_id,$token));
        return $Q->result_array() ? $Q->result_array() : false;
       
    }

    public function chk_ref_no($data)
    {
        $query = $this->db->select('*')
        ->from('tbl_transactions')
        ->where('ref_no', $data['ref_no'])
        ->order_by('trans_id', 'DESC')   //
        ->limit(1)
        ->get();   
        
        return $query->result_array();

    }

    public function chk_trace_number($data)
    {
        $query = $this->db->select('*')
        ->from('tbl_transactions')
        ->where('trace_number', $data['trace_number'])
        ->order_by('trans_id', 'DESC')   //trace_number
        ->limit(1)
        ->get();   
        
        return $query->row_array();

    }

    public function get_billing_data($data)
    {
        $query = $this->db->select('*')
        ->from('tbl_bill')
        ->where('billing_ref', $data['billing_ref'])
        ->order_by('id', 'DESC')   
        ->limit(1)
        ->get();   
        
        return $query->row_array();

    }

    public function user_data($user_id)
    {
        $query = $this->db->select('*')
            ->from('tbl_users')
            ->where('user_id', $user_id)
            ->order_by('user_id', 'DESC')
            ->limit(1)
            ->get();

        return $query->row_array();
    }

    public function update_to_paid($where)
    {
        $updata['status'] = "paid";
        $updata['paid_date'] = date('Y-m-d H:i:s');
        return $this->db->where('billing_ref', $where['billing_ref'])->update('tbl_bill', $updata);
    }

    public function insert_transaction($pdata){

        return $this->db->insert('tbl_transactions', $pdata);

    }
}