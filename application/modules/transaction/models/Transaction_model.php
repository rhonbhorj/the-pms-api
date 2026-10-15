<?php

class Transaction_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }




    
    public function validate_session($data)
    {
        $sql = "select * from users_logs where  sess_id like ? and log_type  ='1'";
        $Q = $this->db->query($sql, array(
            $data['session_id']
        ));
        return $Q->row_array() ? $Q->row_array() : false;
    }

    public function get_draw_history_by_date_range($data)
    {
        $this->db->select('*');
        $this->db->from('tbl_result_draw_history');
        $this->db->where('date_at >=', $data['date_from']);
        $this->db->where('date_at <=', $data['date_to']);
        $this->db->where('status ', $data['status']);
        

        return $this->db->get()->result_array()?:false;
    }


    public function get_transaction_by_date_range($data)
    {
        $this->db->select('*');
        $this->db->from('tbl_transactions');
        $this->db->where('requested_at >=', $data['date_from'] . ' 00:00:00');
        $this->db->where('requested_at <=', $data['date_to'] . ' 23:59:59'); 

        if (strtoupper($data['status'])!= 'ALL') {
        $this->db->where('status',strtoupper($data['status']));
        } 
        return $this->db->get()->result_array() ?: false;
    }
    public function search_transaction_details($datapost)
    {
        $this->db->select( 'reference_number ,
                            TxId,name,email,  
                            merchant_ref ,
                            txn_amount, 
                            status ,
                            callback_uri ,
                            method ,
                            txn_type ,
                            mobile_number ,
                            lto_fees ,
                            general_fund ,
                            seat_belt_act_fee ,
                            mvuc_fee ,
                            clean_air_act_fee ,
                            legal_research_fee ,
                            it_fee ,
                            ngsi_fee ,
                            requested_at ,
                            modified_at ,
                            return_url ');
        $this->db->from('tbl_transactions');

        if (!empty($datapost['search'])) {
            $this->db->group_start();
            $this->db->like('reference_number', $datapost['search']);
            $this->db->or_like('TxId', $datapost['search']);
            $this->db->or_like("merchant_ref", $datapost['search']);
            $this->db->or_like("txn_amount", $datapost['search']);
            $this->db->or_like("status", $datapost['search']);
            $this->db->or_like("callback_uri", $datapost['search']); 
            $this->db->or_like("method", $datapost['search']); 
            $this->db->or_like("txn_type", $datapost['search']); 
            $this->db->or_like("mobile_number", $datapost['search']); 
            $this->db->or_like("lto_fees", $datapost['search']); 
            $this->db->or_like("general_fund", $datapost['search']); 
            $this->db->or_like("seat_belt_act_fee", $datapost['search']); 
            $this->db->or_like("mvuc_fee", $datapost['search']); 
            $this->db->or_like("clean_air_act_fee", $datapost['search']); 
            $this->db->or_like("legal_research_fee", $datapost['search']); 
            $this->db->or_like("it_fee", $datapost['search']); 
            $this->db->or_like("ngsi_fee", $datapost['search']); 
            $this->db->or_like("requested_at", $datapost['search']); 
            $this->db->or_like("modified_at", $datapost['search']); 
            $this->db->or_like("return_url", $datapost['search']); 
            $this->db->group_end();
        }

            $this->db->order_by('trans_id', 'ASC');

        return $this->db->get()->result_array() ?: false;
    }
}