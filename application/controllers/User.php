<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model("UserMdl");
	}

	public function index()
	{
		$data['title'] = "home";

		$this->load->view('templates/header', $data);
		$this->load->view('user/index');
		$this->load->view('templates/footer');
	}

	public function login()
	{
		$data['title'] = "login";

		$this->load->view('templates/header', $data);
		// $this->load->view('templates/login');
		$this->load->view('templates/footer');
	}

	public function register()
	{
		$data['title'] = "register";

		$this->load->view('templates/header', $data);
		// $this->load->view('templates/register');
		$this->load->view('templates/footer');
	}

	public function logout()
	{
		$this->session->unset_userdata('logged_in');

		$this->session->set_flashdata('valid', 'Logged out');
		redirect('/');
	}

	public function get_body()
	{
		$myfile = fopen("body.txt", "w") or die("Unable to open file!");

		$txt = "To complete your payment.\n";
		fwrite($myfile, $txt);
		$txt = base_url() . "payment/" . "\n\n";
		fwrite($myfile, $txt);
		$txt = "Nagar Nigam Ghaziabad";
		fwrite($myfile, $txt);
		fclose($myfile);

		$data['token'] = $this->security->get_csrf_hash();
		echo json_encode($data);
	}

	//import smsFile
	public function import_sms()
	{
		if ($_FILES['smsFile']['size'] > 0) {
			$file_data = fopen($_FILES['smsFile']['tmp_name'], 'r');
			fgetcsv($file_data);
			$data[] = $this->security->get_csrf_hash(); //token
			while ($row = fgetcsv($file_data)) {
				$data[] = array(
					'Phonenumber' => $row[0],
				);
			}
		} else {
			$data['status'] = false;
			$data['msg'] = "No file uploaded";
			$data['smsFile'] = $_FILES['smsFile'];
			$data['token'] = $this->security->get_csrf_hash();
		}

		// $data['token'] = $this->security->get_csrf_hash();
		echo json_encode($data);
	}

	//send
	public function send_sms()
	{
		if (count($_POST) > 0) {
			if (is_array($_POST['mobile'])) {
				$notsentArr = array();
				foreach ($_POST['mobile'] as $mobile) {
					//validate each is a validNumber
					if (empty($mobile) || !isset($mobile) || strlen($mobile) !== 13 || !is_numeric($mobile)) {
						array_push($notsentArr, $mobile);
					}
				}

				if (count($notsentArr) > 0) {
					$data['status'] = false;
					$data['msg'] = "Found Invalid data";
					$data['notsentArr'] = $notsentArr;
				} else if (count($notsentArr) == 0) {
					//API send to multiple No.
					#code...

					$data['status'] = true;
					$data['msg'] = "";
				}
			} else {
				if (empty($_POST['mobile']) || !isset($_POST['mobile']) || strlen($_POST['mobile']) !== 13 || !is_numeric($_POST['mobile'])) {
					$data['status'] = false;
					$data['msg'] = "Invalid Mobile format";
				}else{
					//API send to single No.

					$data['status'] = true;
					$data['msg'] = "";
				}
			}
		} else {
			$data['status'] = false;
			$data['msg'] = "Missiing data";
		}

		$data['token'] = $this->security->get_csrf_hash();
		echo json_encode($data);
	}


	//save a hit and redirect to paymentPage
	public function payment()
	{
		$res = $this->UserMdl->save_PaymentLinkHit();
		if ($res === true) {
			//redirect to paymentPage
			header('Location: https://google.com');
		}
	}

	//get all hits
	public function get_PaymentLinkHits()
	{
		$res = $this->UserMdl->get_PaymentLinkHits();
		$data['title'] = "hits";
		$data['hits'] = $res;

		$this->load->view('templates/header', $data);
		$this->load->view('user/hits');
		$this->load->view('templates/footer');
	}
}
