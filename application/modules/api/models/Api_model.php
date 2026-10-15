<?php

class Api_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    private function generateRandomString($length = 25)

    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i ++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
  
      function chk_access($data)
    {
        if ($data) {
            $sql = "select * from api_keys ak left join api_users au on ak.id=au.key_id where ak.key like ? and au.api_name like ? and au.api_password like ?";
            
            $Q = $this->db->query($sql, array(
                $data['key'],
                $data['username'],
                $data['userpassword']
            ));
            return $Q->row_array();
        } else {
            return false;
        }
    }
    public function finddata($data)
    {
        $qry = 'select * from tbl_callback where reference_number like ? ';
        $Q = $this->db->query($qry, $data);
        return $Q->row_array() ? $Q->result_array() : false;
    }

     public function doInsertCallback($data)
    {

        $lastId = $this->db->insert_id($this->db->insert('tbl_callback', $data));
        return    $lastId;
    }

     public function select_refNumber($data)
    {
        $qry = 'select callback_uri,status from tbl_transactions where reference_number like ? ';
        $Q = $this->db->query($qry, $data);
        return $Q->row_array() ? $Q->row_array() : false;
    }



      public function do_apilogs($pdata)
    {
        return $this->db->insert_id($this->db->insert('api_logs', $pdata));
    }

    public function updateTransaction($update, $where)
    {
        $this->db->where('reference_number', $where)->limit(1)->update('tbl_transactions', $update);
        return $this->db->affected_rows();

        // $this->db->where('reference_number', $where)->update('transactions', $update);
        // return $this->db->affected_rows();
    }

    public function updateCallBack($update, $where)
    {
        $this->db->where('tbl_id', $where)->update('tbl_callback', $update);
        return $this->db->affected_rows();
    }
}