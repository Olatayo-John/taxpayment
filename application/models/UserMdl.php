<?php

class UserMdl extends CI_Model
{
    public function save_PaymentLinkHit()
    {
        $data = array(
            'ip' => $_SERVER['REMOTE_ADDR'],
            'number' => null
        );
        $query = $this->db->insert('paymentHit', $data);
        if ($query) {
            return true;
        } else {
            return false;
        }

        exit;
    }

    public function get_PaymentLinkHits()
    {
        $query = $this->db->get('paymentHit');
        return $query;
        exit;
    }
}
