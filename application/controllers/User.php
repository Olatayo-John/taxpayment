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

		// $txt = "Please vote for your city to become No.1\r\nBelow is the link to vote\r\nhttp://localhost/ss2021/rate/74873514\r\n\r\nDownload Swachhta App\r\nhttp://localhost/ss2021/download-swachhta-app\r\n\r\nBest Regards\r\nNITL";
		// $txt = "Please vote for your city to become No.1\r\nBelow is the link to vote\r\nvarI varII varIII\r\n\r\nDownload Swachhta App\r\nvarI varII varIII\r\n\r\nBest Regards\r\nNITL";
		// fwrite($myfile, $txt);

		fclose($myfile);

		$data['token'] = $this->security->get_csrf_hash();
		echo json_encode($data);
	}

	//import smsFile
	public function import_sms()
	{
		if ($_FILES['smsFile']['size'] > 0) {
			$flag = null;
			$invalidNo = array();
			$data[] = $this->security->get_csrf_hash(); //token

			$file_data = fopen($_FILES['smsFile']['tmp_name'], 'r');
			fgetcsv($file_data);
			while ($row = fgetcsv($file_data)) {
				//validate each number i.e row[0]
				if (!empty($row[0]) && isset($row[0]) && strlen($row[0]) == 10 && is_numeric($row[0])) {
					array_push($data, $row[0]);
				} else {
					array_push($invalidNo, $row[0]);
					$flag = true;
				}
			}

			//check for flag=true i.e triggered by invalid number found
			if ($flag !== null) {
				$data['status'] = false;
				$data['msg'] = "Found Invalid numbers";
				$data['invalidNo'] = $invalidNo;
			}
		} else {
			$data['status'] = false;
			$data['msg'] = "No file uploaded";
			$data['smsFile'] = $_FILES['smsFile'];
		}

		// $data['token'] = $this->security->get_csrf_hash();
		echo json_encode($data);
	}

	//send
	public function send_sms()
	{
		if (count($_POST) > 0) {
			if (is_array($_POST['mobile'])) {
				$invalidNo = array();
				$flag = null;

				//validate each is a validNumber
				foreach ($_POST['mobile'] as $mobile) {
					if (empty($mobile) || !isset($mobile) || strlen($mobile) !== 10 || !is_numeric($mobile)) {
						array_push($invalidNo, $mobile);
						$flag = true;
					}
				}

				if (count($invalidNo) > 0 || $flag !== null) {
					$data['status'] = false;
					$data['msg'] = "Found Invalid numbers";
					$data['invalidNo'] = $invalidNo;
					$data['notsentArr'] = "";
				} else if (count($invalidNo) == 0) {
					$notsentArr = array();
					$notsentFlag = null;

					foreach ($_POST['mobile'] as $mobile) {
						//API send to multiple No.
						$url = "http://savshka.in/api/pushsms?user=swachh&authkey=926pJyyVe2aK&sender=NKTECI&mobile=" . $mobile . "&text=";
						$req = curl_init();
						$complete_url = $url . curl_escape($req, $_POST['msgBody']) . "&rpt=0";
						curl_setopt($req, CURLOPT_URL, $complete_url);
						curl_setopt($req, CURLOPT_RETURNTRANSFER, TRUE);
						$result = curl_exec($req);

						$httpCode = curl_getinfo($req, CURLINFO_HTTP_CODE);
						$Jresult = json_decode($result, true);
						// $httpCode = 44;

						if ($httpCode !== 200) {
							$data = array(
								'mobile' => $mobile,
								'msg' => $_POST['msgBody'],
								'error' => $httpCode . " SERVER ERROR",
								'status' => "ERROR"
							);
							$this->UserMdl->save_sent($data);

							array_push($notsentArr, array("error" => $httpCode . " SERVER ERROR", "mobile" => $mobile));
							$notsentFlag = true;
						} else {
							if ($Jresult['STATUS'] == "ERROR") {
								$data = array(
									'mobile' => $mobile,
									'msg' => $_POST['msgBody'],
									'error' => $Jresult['RESPONSE']['CODE'] . " - " . $Jresult['RESPONSE']['INFO'],
									'status' => "ERROR"
								);
								$this->UserMdl->save_sent($data);
								
								array_push($notsentArr, array("error" => $Jresult['RESPONSE']['CODE'] . " - " . $Jresult['RESPONSE']['INFO'], "mobile" => $mobile));
								$notsentFlag = true;

							}else{
								$data = array(
									'mobile' => $mobile,
									'msg' => $_POST['msgBody'],
									'error' => $Jresult['RESPONSE']['CODE'] . " - " . $Jresult['RESPONSE']['INFO'],
									'status' => "OK"
								);
								$this->UserMdl->save_sent($data);
							}
						}

						curl_close($req);
					}

					if (count($notsentArr) > 0 || $notsentFlag !== null) {
						$data['status'] = false;
						$data['msg'] = "Unable to send to these numbers";
						$data['notsentArr'] = $notsentArr;
					} else {
						$data['status'] = true;
						$data['msg'] = "";
					}

					$data['invalidNo'] = "";
				}
			} else {
				if (empty($_POST['mobile']) || !isset($_POST['mobile']) || strlen($_POST['mobile']) !== 10 || !is_numeric($_POST['mobile'])) {
					$data['status'] = false;
					$data['msg'] = "Invalid Mobile format";
					$data['invalidNo'] = array($_POST['mobile']);
					$data['notsentArr'] = "";
				} else {
					//API send to single No.
					// $url = "http://savshka.in/api/pushsms?user=swachh&authkey=926pJyyVe2aK&sender=NKTECI&mobile=" . $_POST['mobile'] . "&text=";
					// $req = curl_init();
					// $complete_url = $url . curl_escape($req, $_POST['msgBody']) . "&entityid=1001715674475461342&templateid=1007043429553393803&rpt=0";

					$url = "http://savshka.in/api/pushsms?user=swachh&authkey=926pJyyVe2aK&sender=NKTECI&mobile=" . $_POST['mobile'] . "&text=".urlencode($_POST['msgBody'])."&entityid=1001715674475461342&templateid=1007043429553393803&rpt=0";
					$req = curl_init();

					curl_setopt($req, CURLOPT_URL, $url);
					curl_setopt($req, CURLOPT_RETURNTRANSFER, TRUE);
					$result = curl_exec($req);

					$httpCode = curl_getinfo($req, CURLINFO_HTTP_CODE);
					$Jresult = json_decode($result, true);
					// $httpCode = 44;

					if ($httpCode !== 200) {
						$data = array(
							'mobile' => $_POST['mobile'],
							'msg' => $_POST['msgBody'],
							'error' => $httpCode . " SERVER ERROR",
							'status' => "ERROR"
						);
						$this->UserMdl->save_sent($data);

						$data['status'] = false;
						$data['msg'] = $httpCode . " SERVER ERROR";
					} else {
						if ($Jresult['STATUS'] == "ERROR") {
							$data = array(
								'mobile' => $_POST['mobile'],
								'msg' => $_POST['msgBody'],
								'error' => $Jresult['RESPONSE']['CODE'] . " - " . $Jresult['RESPONSE']['INFO'],
								'status' => "ERROR"
							);
							$this->UserMdl->save_sent($data);

							$data['status'] = false;
							$data['msg'] = $Jresult['RESPONSE']['CODE'] . " - " . $Jresult['RESPONSE']['INFO'];
						} else if ($Jresult['STATUS'] == "OK") {
							$data = array(
								'mobile' => $_POST['mobile'],
								'msg' => $_POST['msgBody'],
								'error' => $Jresult['RESPONSE']['CODE'] . " - " . $Jresult['RESPONSE']['INFO'],
								'status' => "OK"
							);
							$this->UserMdl->save_sent($data);

							$data['status'] = true;
							$data['msg'] = $Jresult['RESPONSE']['CODE'] . " - " . $Jresult['RESPONSE']['INFO'];
							// $this->session->set_userdata('valid', $data['msg']);
						}
					}

					$data['invalidNo'] = "";
					$data['notsentArr'] = "";
					$data['httpCode'] = $httpCode;
					$data['Jresult'] = $Jresult;
				}

				curl_close($req);
			}
		} else {
			$data['status'] = false;
			$data['msg'] = "Missing data";
			$data['e_AfterSend'] = "";
			$data['invalidNo'] = "";
			$data['notsentArr'] = "";
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

	//get all sentLinks
	public function get_sent()
	{
		$res = $this->UserMdl->get_sent();
		$data['title'] = "logs";
		$data['sent'] = $res;

		$this->load->view('templates/header', $data);
		$this->load->view('user/sent');
		$this->load->view('templates/footer');
	}
}
