<?php

class Request_qr_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }





    public function chk_reference_number($data)
    {
        $query = $this->db->select('*')
        ->from('tbl_transactions')
        ->where('reference_number', $data['reference_number'])
        ->order_by('trans_id', 'DESC')   //trace_number
        ->limit(1)
        ->get();   
        
        return $query->row_array();

    }


    public function validate_session($data)
    {
        $sql = "select * from users_logs where  sess_id like ? and log_type  ='1'";
        $Q = $this->db->query($sql, array($data['session_id']));

        return $Q->row_array() ? $Q->row_array() : false;
    }

    public function user_data($can)
    {
        $query = $this->db->select('*')
            ->from('tbl_users')
            ->where('can', $can)
            ->order_by('user_id', 'DESC')
            ->limit(1)
            ->get();

        return $query->row_array();
    }


    public function insert_transaction($pdata){

        return $this->db->insert('tbl_transactions', $pdata);

    }

    public function do_apilogs($pdata)
    {
        return $this->db->insert_id($this->db->insert('api_logs', $pdata));
    }
    
    public function doUpdateApilogs($update, $where)
    {
        $this->db->where('api_id', $where)->update('api_logs', $update);
        return $this->db->affected_rows();
    }
   public function update_transaction_data($update, $where)
    {
        $this->db->where('reference_number', $where)->update('tbl_transactions', $update);
        return $this->db->affected_rows();
    }

}