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

		$txt = "Dear {{name}}\n";
		fwrite($myfile, $txt);
		$txt = "Your property tax / House tax is due on your property ID : {{ID}}\n";
		fwrite($myfile, $txt);
		$txt = "Please pay the tax by the link : {{Pay}}\n";
		fwrite($myfile, $txt);
		$txt = "before " . date("Y-m-d") . " to avoid penalties and legal actions.\n";
		fwrite($myfile, $txt);
		$txt = "Please ignore if already paid.\n\n";
		fwrite($myfile, $txt);
		$txt = "Nagar Palika Parishad\n";
		fwrite($myfile, $txt);
		$txt = "Muni Ki Reti - Dhalwala";
		fwrite($myfile, $txt);

		fclose($myfile);

		$data['token'] = $this->security->get_csrf_hash();
		echo json_encode($data);
	}

	//import smsFile
	public function import_sms()
	{
		if ($_FILES['smsFile']['size'] > 0) {
			$flag = null;
			$invalidRow = array();
			$data[] = $this->security->get_csrf_hash(); //token

			$file_data = fopen($_FILES['smsFile']['tmp_name'], 'r');
			fgetcsv($file_data);
			$line = 2;
			while ($row = fgetcsv($file_data)) {
				$info = "";

				//name
				if (empty($row[0]) || !isset($row[0])) {
					array_push($invalidRow, "Name:" . $row[0] . " at line " . $line);
					$flag = true;
				} else {
					$info = $row[0];
				}

				//number
				if (empty($row[1]) || !isset($row[1]) || strlen($row[1]) !== 10 || !is_numeric($row[1])) {
					array_push($invalidRow, "Mobile: " . $row[1] . " at line " . $line);
					$flag = true;
				} else {
					$info .= "," . $row[1];
				}

				//ID
				if (empty($row[2]) || !isset($row[2]) || strlen($row[2]) !== 6 || !is_numeric($row[2])) {
					array_push($invalidRow, "PropertyID: " . $row[2] . " at line " . $line);
					$flag = true;
				} else {
					$info .= "," . $row[2];
				}

				array_push($data, $info);
				$line = $line + 1;
			}

			//check for flag=true i.e triggered by invalid number found
			if ($flag !== null) {
				$data['status'] = false;
				$data['msg'] = "Found Invalid Fields";
				$data['invalidRow'] = $invalidRow;
			}
		} else {
			$data['status'] = false;
			$data['msg'] = "No file uploaded";
			$data['smsFile'] = $_FILES['smsFile'];
		}

		// $data['token'] = $this->security->get_csrf_hash();
		echo json_encode($data);
	}

	// sample smsFile
	public function sample_smsFile()
	{
		header("Content-Type: text/csv; charset=utf-8");
		header("Content-Disposition: attachment; filename=date_sample.csv");
		$output = fopen("php://output", "w");
		fputcsv($output, array('Name', 'Mobile', 'PropertyID'));
		$data[] = array(
			'Name' => 'John',
			'Mobile' => '0123456789',
			'PropertyID' => '002199',
		);
		foreach ($data as $row) {
			fputcsv($output, $row);
		}

		fclose($output);
	}

	//send
	public function send_sms()
	{
		if (count($_POST) > 0) {
			if (is_array($_POST['mobile'])) {
				$DataArr = array();
				$notsentArr = array();
				$notsentFlag = null;

				foreach ($_POST['mobile'] as $mobile) {
					//explode Array
					$dataInfo = explode(",", $mobile);

					$name = $dataInfo[0];
					$mobileNo = $dataInfo[1];
					$propertyID = $dataInfo[2];
					$paymentlink = "https://nagarsewa.uk.gov.in/citizen/withoutAuth/egov-common/pay?consumerCode=PT-248426-" . $propertyID . "&tenantId=uk.munikireti&businessService=PT";

					$msgBody = "Dear " . $name . "\nYour property tax / House tax is due on your property ID : " . $propertyID . "\nPlease pay the tax by the link : " . $paymentlink . "\nbefore " . date("Y-m-d") . " to avoid penalties and legal actions.\nPlease ignore if already paid.\n\nNagar Palika Parishad\nMuni Ki Reti - Dhalwala";
					array_push($DataArr, array($name, $mobileNo, $propertyID, $paymentlink));

					//API send
					$url = "http://savshka.in/api/pushsms?user=502893&authkey=926pJyyVe2aK&sender=SSURVE&mobile=" . $mobileNo . "&text=" . urlencode($msgBody) . "&entityid=1001715674475461342&templateid=1007838850146399750&rpt=0";
					$req = curl_init();

					curl_setopt($req, CURLOPT_URL, $url);
					curl_setopt($req, CURLOPT_RETURNTRANSFER, TRUE);
					$result = curl_exec($req);

					$httpCode = curl_getinfo($req, CURLINFO_HTTP_CODE);
					$Jresult = json_decode($result, true);
					// $httpCode =44;

					if ($httpCode !== 200) {
						$data = array(
							'mobile' => $mobileNo,
							'msg' => $msgBody,
							'error' => $httpCode . " SERVER ERROR",
							'status' => "ERROR"
						);
						$this->UserMdl->save_sent($data);

						array_push($notsentArr, array("error" => $httpCode . " SERVER ERROR", "data" => $mobile));
						$notsentFlag = true;
					} else {
						if ($Jresult['STATUS'] == "ERROR") {
							$data = array(
								'mobile' => $mobileNo,
								'msg' => $msgBody,
								'error' => $Jresult['RESPONSE']['CODE'] . " - " . $Jresult['RESPONSE']['INFO'],
								'status' => "ERROR"
							);
							$this->UserMdl->save_sent($data);

							array_push($notsentArr, array("error" => $Jresult['RESPONSE']['CODE'] . " - " . $Jresult['RESPONSE']['INFO'], "data" => $mobile));
							$notsentFlag = true;
						} else {
							$data = array(
								'mobile' => $mobileNo,
								'msg' => $msgBody,
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
					$data['msg'] = "Unable to send to these data";
					$data['notsentArr'] = $notsentArr;
				} else {
					$data['status'] = true;
					$data['msg'] = "";
				}

				// if (count($DataArr) > 0) {
				// 	header("Content-Type: text/csv; charset=utf-8");
				// 	header("Content-Disposition: attachment; filename=processed_data.csv");
				// 	$output = fopen("php://output", "w");
				// 	fputcsv($output, array('Name', 'Mobile', 'PropertyID','Link'));
				// 	foreach ($DataArr as $row) {
				// 		fputcsv($output, $row);
				// 	}
				// 	fclose($output);
				// }

				$data['DataArr'] = $DataArr;
				$data['invalidNo'] = "";
			} else {
				if (empty($_POST['mobile']) || !isset($_POST['mobile']) || strlen($_POST['mobile']) !== 10 || !is_numeric($_POST['mobile'])) {
					$data['status'] = false;
					$data['msg'] = "Invalid Mobile format";
					$data['notsentArr'] = "";
				} else {
					//API send to single No.
					$url = "http://savshka.in/api/pushsms?user=502893&authkey=926pJyyVe2aK&sender=SSURVE&mobile=" . $_POST['mobile'] . "&text=" . urlencode($_POST['msgBody']) . "&entityid=1001715674475461342&templateid=1007838850146399750&rpt=0";
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
						}
					}

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
