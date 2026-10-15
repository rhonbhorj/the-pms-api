<?php

class GenerateToken_model extends CI_Model
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
            $sql = "select * from api_keys where `key` = ?";
            $Q = $this->db->query($sql, array(
                $data['key']
            ));
            return $Q->row_array();
        } else {
            return false;
        }
    }

    public function inser_token($data)
    {
        return $this->db->insert('client_token', $data);
    }

    public function exp_all($id)
    {
        $update['status'] = "INACTIVE";
        $this->db->where('company_id', $id)->update('client_token', $update);

        return true;
    }
}