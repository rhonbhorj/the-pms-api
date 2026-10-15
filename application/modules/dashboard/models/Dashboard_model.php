<?php

class Dashboard_model extends CI_Model
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
    // function total_txn_amount_details() {
    //     $sql = "SELECT 
    //                 COALESCE(SUM(txn_amount), 0) AS txn_amount,
    //                 COUNT(txn_amount) AS count_txn_amount,
                    
    //             FROM 
    //                 tbl_transactions 
    //             WHERE 
    //                 status = 'SUCCESS'";
    //     $Q = $this->db->query($sql);
    //     return $Q->row_array() ? $Q->row_array() : false;
    // }
    



    function total_txn_amount_details()
    {
        $this->db->select('
            COALESCE(SUM(txn_amount), 0) AS txn_amount,
            COUNT(txn_amount) AS count_txn_amount,
COALESCE(SUM(lto_fees), 0) AS lto_fees,
COALESCE(SUM(general_fund), 0) AS general_fund,
COALESCE(SUM(seat_belt_act_fee), 0) AS seat_belt_act_fee,
COALESCE(SUM(legal_research_fee), 0) AS legal_research_fee,
COALESCE(SUM(it_fee), 0) AS it_fee,
COALESCE(SUM(mvuc_fee), 0) AS mvuc_fee,
COALESCE(SUM(clean_air_act_fee), 0) AS clean_air_act_fee,
COALESCE(SUM(ngsi_fee), 0) AS ngsi_fee


        ');
        $this->db->from('tbl_transactions');
        $this->db->where('status', 'SUCCESS');

        $query = $this->db->get();

        return $query->row_array() ?: false;
    }
    // function total_txn_amount_today_details() {
    //     $sql = "SELECT 
    //                 COALESCE(SUM(txn_amount), 0) AS txn_amount,
    //                 COUNT(txn_amount) AS count_txn_amount,
                 
    //             FROM 
    //                 tbl_transactions 
    //             WHERE 
    //                 status = 'SUCCESS' AND type = 'tbl_transactions' 
    //                 AND date_modified like '%".date('Y-m-d')."%'"; // Using CURDATE() for today's date
    //     $Q = $this->db->query($sql);
    //     return $Q->row_array() ? $Q->row_array() : false;
    // }


        function total_txn_amount_today_details()
        {
            $today = date('Y-m-d');

            $this->db->select('
                COALESCE(SUM(txn_amount), 0) AS txn_amount,
                COUNT(txn_amount) AS count_txn_amount
            ');
            $this->db->from('tbl_transactions');
            $this->db->where('status', 'SUCCESS');
            // $this->db->where('type', 'tbl_transactions');
            $this->db->like('requested_at', $today, 'after');

            $query = $this->db->get();

            return $query->row_array() ?: false;
        }
    
        public function total_txn_amount_yesterday_details()
        {
            $yesterday = date('Y-m-d', strtotime('-1 day'));

            $this->db->select('
                COALESCE(SUM(txn_amount), 0) AS txn_amount,
                COUNT(*) AS count_txn_amount
            ');
            $this->db->from('tbl_transactions');
            $this->db->where('status', 'SUCCESS');
            $this->db->where('requested_at >=', $yesterday . ' 00:00:00');
            $this->db->where('requested_at <', date('Y-m-d') . ' 00:00:00');

            $query = $this->db->get();

            return $query->row_array() ?: false;
        }
    




function ytd()
{
    $sql = "SELECT
                MONTHNAME(months.month) AS m,
                COALESCE(COUNT(trans.trans_id), 0) AS count,
                COALESCE(SUM(trans.txn_amount), 0) AS txn_amount,
                COALESCE(SUM(trans.lto_fees), 0) AS lto_fees,
                COALESCE(SUM(trans.general_fund), 0) AS general_fund,
                COALESCE(SUM(trans.seat_belt_act_fee), 0) AS seat_belt_act_fee,
                COALESCE(SUM(trans.clean_air_act_fee), 0) AS clean_air_act_fee,
                COALESCE(SUM(trans.legal_research_fee), 0) AS legal_research_fee,
                COALESCE(SUM(trans.it_fee), 0) AS it_fee,
                COALESCE(SUM(trans.ngsi_fee), 0) AS ngsi_fee,
                COALESCE(SUM(trans.mvuc_fee), 0) AS mvuc_fee






            FROM (
                SELECT
                    DATE_FORMAT(
                        CONCAT(
                            YEAR(CURDATE()),
                            '-',
                            LPAD(n, 2, '0'),
                            '-01'
                        ),
                        '%Y-%m-01'
                    ) AS month
                FROM (
                    SELECT 1 AS n
                    UNION ALL SELECT 2
                    UNION ALL SELECT 3
                    UNION ALL SELECT 4
                    UNION ALL SELECT 5
                    UNION ALL SELECT 6
                    UNION ALL SELECT 7
                    UNION ALL SELECT 8
                    UNION ALL SELECT 9
                    UNION ALL SELECT 10
                    UNION ALL SELECT 11
                    UNION ALL SELECT 12
                ) AS numbers
            ) AS months

            LEFT JOIN tbl_transactions AS trans
                ON MONTH(trans.requested_at) = MONTH(months.month)
                AND YEAR(trans.requested_at) = YEAR(CURDATE()) AND trans.status = 'SUCCESS'

            WHERE months.month <= DATE_FORMAT(CURDATE(), '%Y-%m-01') 

            GROUP BY months.month
            ORDER BY months.month";

    $Q = $this->db->query($sql);

    return $Q->num_rows() > 0
        ? $Q->result_array()
        : false;
}
    function daily_graph()
    {
        $sql = "
            SELECT
                day_names.day AS day,

                COALESCE(COUNT(trans.trans_id), 0) AS count,
                COALESCE(SUM(trans.txn_amount), 0) AS txn_amount,
                COALESCE(SUM(trans.lto_fees), 0) AS lto_fees,
                COALESCE(SUM(trans.general_fund), 0) AS general_fund,
                COALESCE(SUM(trans.seat_belt_act_fee), 0) AS seat_belt_act_fee,
                COALESCE(SUM(trans.clean_air_act_fee), 0) AS clean_air_act_fee,
                COALESCE(SUM(trans.legal_research_fee), 0) AS legal_research_fee,
                COALESCE(SUM(trans.it_fee), 0) AS it_fee,
                COALESCE(SUM(trans.ngsi_fee), 0) AS ngsi_fee,
                COALESCE(SUM(trans.mvuc_fee), 0) AS mvuc_fee

            FROM
                (
                    SELECT 'Sunday' AS day
                    UNION ALL SELECT 'Monday'
                    UNION ALL SELECT 'Tuesday'
                    UNION ALL SELECT 'Wednesday'
                    UNION ALL SELECT 'Thursday'
                    UNION ALL SELECT 'Friday'
                    UNION ALL SELECT 'Saturday'
                ) AS day_names

            LEFT JOIN tbl_transactions trans
                ON DAYNAME(trans.requested_at) = day_names.day
                AND YEARWEEK(trans.requested_at, 0) = YEARWEEK(CURDATE(), 0)
                AND trans.status = 'success'

            GROUP BY
                day_names.day

            ORDER BY
                FIELD(
                    day_names.day,
                    'Sunday',
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday'
                )
        ";

        $Q = $this->db->query($sql);

        return $Q->result_array() ?: false;
    }

        function client_data()
        {
            $sql = "SELECT 
    c.company_id,
    c.company_name,
  
        COUNT(CASE WHEN t.type = 'cashin' THEN t.reference_number END) AS cashin_total_request,
        COUNT(CASE WHEN t.type = 'cashout' THEN t.reference_number END) AS cashout_total_request,
    
        SUM(CASE WHEN t.type = 'cashin' THEN t.txn_amount ELSE 0 END) AS cashin_amount,
        SUM(CASE WHEN t.type = 'cashout' THEN t.txn_amount ELSE 0 END) AS cashout_amount,
    
        SUM(CASE WHEN t.type = 'cashin' THEN t.deducted_amount ELSE 0 END) AS cashin_deducted_amount,
        SUM(CASE WHEN t.type = 'cashout' THEN t.deducted_amount ELSE 0 END) AS cashout_deducted_amount
  
            
        FROM 
            tbl_cashin t
        INNER JOIN 
            tbl_company c ON t.company_id = c.company_id
        WHERE 
            t.type IN ('cashin', 'cashout')  
        GROUP BY 
            c.company_id, c.company_name"; 

            
            $Q = $this->db->query($sql);
            return $Q->row_array() ? $Q->result_array() : false;
        }
   
    public function do_update_data()
    {
        // $update['date_modified']=date('Y-m-d H:i:s'); 
        // $where="success";
        // $this->db->where('status', $where)->update('tbl_transactions', $update)->db->limit(20, 0);


        $sql =  "UPDATE tbl_transactions
SET date_modified =".date('Y-m-d H:i:s')."
WHERE status = 'success'
LIMIT 20";
        return $this->db->affected_rows();
    }
}