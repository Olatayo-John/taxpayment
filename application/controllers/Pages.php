<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pages extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model("PagesMdl");
	}

	public function index()
	{
		if ($this->session->userdata('logged_in')) {
			redirect('home');
		} else {
			redirect('login');
		}
	}


	public function contact()
	{
		$data['title'] = "contact us";

		$this->form_validation->set_rules('name', 'Full Name', 'required|trim|html_escape');
		$this->form_validation->set_rules('email', 'E-mail', 'required|trim|valid_email|html_escape');
		$this->form_validation->set_rules('msg', 'Message', 'required|trim|html_escape');

		if ($this->form_validation->run() === FALSE) {
			$this->load->view('templates/header', $data);
			$this->load->view('templates/contactus');
			$this->load->view('templates/footer');
		} else {
			$recaptchaResponse = trim($this->input->post('g-recaptcha-response'));
			$userIp = $this->input->ip_address();
			$secret = "6Lec4E4aAAAAAE572v5dAT3Qwn9B-IreUdtlHgHi";

			$url = "https://www.google.com/recaptcha/api/siteverify?secret=" . $secret . "&response=" . $recaptchaResponse . "&remoteip=" . $userIp;

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);
			curl_close($ch);

			$status = json_decode($output, true);

			if ($status['success']) {
				$name = htmlentities($this->input->post('name'));
				$user_mail = htmlentities($this->input->post('email'));
				$bdy = htmlentities($this->input->post('msg'));

				$this->load->library('emailconfig');
				$mail_res = $this->emailconfig->support_mail($name, $user_mail, $bdy);
				// $mail_res = true;

				if ($mail_res !== true) {
					$this->Logmodel->log_act($type = "mail_err");
					$this->session->set_flashdata('invalid', 'Error sending your message');
					redirect($_SERVER['HTTP_REFERER']);
				} else {
					$res = $this->Adminmodel->contact();
					$this->Logmodel->log_act($type = "cnt_us");
					$this->session->set_flashdata('valid', 'Message sent. We will get back to you as soon as possible');
					redirect($_SERVER['HTTP_REFERER']);
				}
			} else {
				$this->session->set_flashdata('invalid', 'Google Recaptcha Unsuccessfull');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
}
