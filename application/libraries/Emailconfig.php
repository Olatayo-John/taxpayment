<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Emailconfig
{
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();

        $config['protocol']    = 'smtp';
        $config['smtp_host']    = 'ssl://smtp.gmail.com';
        $config['smtp_port']    = '465';
        $config['smtp_timeout'] = '7';
        $config['smtp_user']    = 'jvweedtest@gmail.com';
        $config['smtp_pass']    = 'Jvweedtest9!';
        $config['charset']    = 'iso-8859-1';
        $config['mailtype'] = 'text';
        $config['validation'] = TRUE;

        $this->CI->load->library('email', $config);
        $this->CI->email->set_newline("\r\n");
    }

    public function support_mail($name, $user_mail, $bdy)
    {
        if ($user_mail) {
            $subj = "Support message from " . $user_mail;
        } else if (!$user_mail) {
            $subj = "Support Mail";
        }

        $this->CI->email->from('jvweedtest@gmail.com', 'Rating');
        $this->CI->email->to('john.nktech@gmail.com');
        $this->CI->email->subject($subj);
        $this->CI->email->message($bdy);

        if ($this->CI->email->send()) {
            return true;
        } else {
            return $this->CI->email->print_debugger();
        }
    }
}
