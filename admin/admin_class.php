<?php
session_start();
class Action
{
	private $db;

	public function __construct()
	{
		ob_start();
		include 'db_connect.php';

		$this->db = $conn;
	}
	function __destruct()
	{
		$this->db->close();
		ob_end_flush();
	}

	function login()
	{
		extract($_POST);
		$qry = $this->db->query("SELECT * FROM `users` where username = '" . $username . "' ");
		if ($qry->num_rows > 0) {
			$result = $qry->fetch_array();
			$is_verified = password_verify($password, $result['password']);
			if ($is_verified) {
				foreach ($result as $key => $value) {
					if ($key != 'password' && !is_numeric($key))
						$_SESSION['login_' . $key] = $value;
				}
				return 1;
			}
		}
		return 3;
	}
	function login2()
	{
		extract($_POST);

		// Sanitize input
		$email = $this->db->real_escape_string($email);
		$password = $this->db->real_escape_string($password);

		// Fetch user information by email
		$userQuery = $this->db->query("SELECT * FROM user_info WHERE email = '$email'");

		if ($userQuery->num_rows > 0) {
			$user = $userQuery->fetch_array();

			// Verify the password using password_verify function
			if (password_verify($password, $user['password'])) {
				foreach ($user as $key => $value) {
					if ($key != 'password' && !is_numeric($key)) {
						$_SESSION['login_' . $key] = $value;
					}
				}

				// Set session flag for successful login
				$_SESSION['login_success'] = true;

				// Update cart's user_id based on IP
				$ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
				$this->db->query("UPDATE cart SET user_id = '" . $_SESSION['login_user_id'] . "' WHERE client_ip = '$ip' ");

				return 1; // Return success code
			}
		}

		return 3; // Return a code to indicate unsuccessful login
	}


	function logout()
	{
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}
	function logout2()
	{
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:../index.php");
	}

	function save_user()
	{
		extract($_POST);
		$password = password_hash($password, PASSWORD_DEFAULT);
		$data = " `name` = '$name' ";
		$data .= ", `username` = '$username' ";
		$data .= ", `password` = '$password' ";
		$data .= ", `type` = '$type' ";
		if (empty($id)) {
			$save = $this->db->query("INSERT INTO users set " . $data);
		} else {
			$save = $this->db->query("UPDATE users set " . $data . " where id = " . $id);
		}
		if ($save) {
			return 1;
		}
	}
	function delete_user()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM users where id = " . $id);
		if ($delete)
			return 1;
	}
	function save_user_info()
	{
		extract($_POST);
		$password = password_hash($password, PASSWORD_DEFAULT);
		$data = " `first_name` = '$first_name' ";
		$data .= ", `last_name` = '$last_name' ";
		$data .= ", `email` = '$email' ";
		$data .= ", `mobile` = '$mobile' ";
		$data .= ", `password` = '$password' ";
		if (empty($user_id)) {
			$save = $this->db->query("INSERT INTO user_info SET " . $data);
		} else {
			$save = $this->db->query("UPDATE user_info SET " . $data . " WHERE user_id = $user_id");
		}
		if ($save)
			return 1;
	}

	function delete_user_info()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM user_info WHERE user_id = $user_id");
		if ($delete)
			return 1;
	}




	function signup()
	{
		extract($_POST);
		$password = password_hash($password, PASSWORD_DEFAULT);
		$data = " first_name = '$first_name' ";
		$data .= ", last_name = '$last_name' ";
		$data .= ", mobile = '$mobile' ";
		$data .= ", email = '$email' ";
		$data .= ", password = '$password' ";
		$chk = $this->db->query("SELECT * FROM user_info where email = '$email' ")->num_rows;
		if ($chk > 0) {
			return 2;
			exit;
		}
		$save = $this->db->query("INSERT INTO user_info set " . $data);
		if ($save) {
			$login = $this->login2();
			return 1;
		}
	}

	function save_settings()
	{
		extract($_POST);
		$data = " name = '$name' ";
		$data .= ", email = '$email' ";
		$data .= ", contact = '$contact' ";
		$data .= ", about_content = '" . htmlentities(str_replace("'", "&#x2019;", $about)) . "' ";
		if ($_FILES['img']['tmp_name'] != '') {
			$fname = strtotime(date('y-m-d H:i')) . '_' . $_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'], '../assets/img/' . $fname);
			$data .= ", cover_img = '$fname' ";
		}

		// echo "INSERT INTO system_settings set ".$data;
		$chk = $this->db->query("SELECT * FROM system_settings");
		if ($chk->num_rows > 0) {
			$save = $this->db->query("UPDATE system_settings set " . $data . " where id =" . $chk->fetch_array()['id']);
		} else {
			$save = $this->db->query("INSERT INTO system_settings set " . $data);
		}
		if ($save) {
			$query = $this->db->query("SELECT * FROM system_settings limit 1")->fetch_array();
			foreach ($query as $key => $value) {
				if (!is_numeric($key))
					$_SESSION['setting_' . $key] = $value;
			}

			return 1;
		}
	}


	function save_category()
	{
		extract($_POST);
		$data = " name = '$name' ";

		if (isset($_POST['status']) && $_POST['status'] == 'on') {
			$data .= ", status = 1 ";
		} else {
			$data .= ", status = 0 ";
		}

		if ($_FILES['img']['tmp_name'] != '') {
			$fname = strtotime(date('y-m-d H:i')) . '_' . $_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'], '../assets/img/category/' . $fname);
			$data .= ", img_path = '$fname' ";
		}

		if (empty($id)) {
			$save = $this->db->query("INSERT INTO category_list SET " . $data);
		} else {
			$save = $this->db->query("UPDATE category_list SET " . $data . " WHERE id = " . $id);
		}

		// Update card_list status separately if necessary
		if (!$save) {
			// Handle error here (return an error code or message)
			return 0;
		}

		if (isset($_POST['status']) != 'on') {
			$updateCardListStatus = $this->db->query("UPDATE card_list SET status = 0 WHERE category_id = " . $id);
			if (!$updateCardListStatus) {
				// Handle error here (return an error code or message)
				return 0;
			}
		} else {
			$updateCardListStatus = $this->db->query("UPDATE card_list SET status = 1 WHERE category_id = " . $id);
		}

		return 1;
	}

	function delete_category()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM category_list where id = " . $id);
		if ($delete)
			return 1;
	}
	function save_menu()
	{
		extract($_POST);
		$data = " name = '$name' ";
		$data .= ", category_id = '$category_id' ";
		$data .= ", description = '$description' ";
		if (isset($status) && $status  == 'on')
			$data .= ", status = 1 ";
		else
			$data .= ", status = 0 ";

		if ($_FILES['img']['tmp_name'] != '') {
			$fname = strtotime(date('y-m-d H:i')) . '_' . $_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'], '../assets/img/' . $fname);
			$data .= ", img_path = '$fname' ";
		}
		if (empty($id)) {
			$save = $this->db->query("INSERT INTO card_list set " . $data);
		} else {
			$save = $this->db->query("UPDATE card_list set " . $data . " where id=" . $id);
		}
		if ($save)
			return 1;
	}

	function delete_menu()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM card_list where id = " . $id);
		if ($delete)
			return 1;
	}
	function delete_cart()
	{
		extract($_GET);
		$delete = $this->db->query("DELETE FROM cart where id = " . $id);
		if ($delete)
			header('location:' . $_SERVER['HTTP_REFERER']);
	}
	function add_to_cart()
	{
		extract($_POST);
		$data = " product_id = $pid ";
		$qty = isset($qty) ? $qty : 1;
		$data .= ", qty = $qty ";
		if (isset($_SESSION['login_user_id'])) {
			$data .= ", user_id = '" . $_SESSION['login_user_id'] . "' ";
		} else {
			$ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
			$data .= ", client_ip = '" . $ip . "' ";
		}
		$save = $this->db->query("INSERT INTO cart set " . $data);
		if ($save)
			return 1;
	}



	function get_cart_count()
	{
		extract($_POST);
		if (isset($_SESSION['login_user_id'])) {
			$where = " where user_id = '" . $_SESSION['login_user_id'] . "'  ";
		} else {
			$ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
			$where = " where client_ip = '$ip'  ";
		}
		$get = $this->db->query("SELECT sum(qty) as cart FROM cart " . $where);
		if ($get->num_rows > 0) {
			return $get->fetch_array()['cart'];
		} else {
			return '0';
		}
	}

	function update_cart_qty()
	{
		extract($_POST);
		$data = " qty = $qty ";
		$save = $this->db->query("UPDATE cart set " . $data . " where id = " . $id);
		if ($save)
			return 1;
	}
	function save_order($name, $fromEmail, $toEmail, $canvasState, $imagePath, $message)
	{
		error_log("save_order called with data: " . json_encode(func_get_args()));

		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}

		$stmt = $this->db->prepare("INSERT INTO orders (name, from_email, to_email, canvas_state, image_path, message) VALUES (?, ?, ?, ?, ?, ?)");

		if ($stmt === false) {
			error_log("Failed to prepare statement: " . $this->db->error);
			return "Failed to prepare statement: " . $this->db->error;
		}

		$stmt->bind_param("ssssss", $name, $fromEmail, $toEmail, $canvasState, $imagePath, $message);

		if ($stmt->execute()) {
			error_log("Successfully executed the query. Insert ID: " . $stmt->insert_id);
			return true;
		} else {
			error_log("Error: " . $stmt->error);
			return "Error: " . $stmt->error;
		}
	}


	function save_scheduled_email($emailData)
	{
		$stmt = $this->db->prepare("INSERT INTO scheduled_emails 
    (name, from_email, to_email, message, canvas_state, image_path, schedule_datetime) 
    VALUES (?, ?, ?, ?, ?, ?, ?)");

		if ($stmt === false) {
			return "Could not prepare statement: " . $this->db->error;
		}

		$stmt->bind_param("sssssss", $emailData['name'], $emailData['from_email'], $emailData['to_email'], $emailData['message'], $emailData['canvas_state'], $emailData['image_path'], $emailData['schedule_datetime']);

		if ($stmt->execute() === false) {
			return "Could not execute statement: " . $stmt->error;
		}

		$stmt->close();
		return true;
	}



	function fetch_scheduled_emails($current_datetime)
	{
		$stmt = $this->db->prepare("SELECT * FROM scheduled_emails WHERE schedule_datetime <= ? AND is_sent = 0");
		$stmt->bind_param("s", $current_datetime);
		$stmt->execute();

		$result = $stmt->get_result();
		$emails_to_send = $result->fetch_all(MYSQLI_ASSOC);

		$stmt->close();
		return $emails_to_send;
	}

	function mark_email_as_sent($id)
	{
		error_log("mark_email_as_sent called with ID: " . $id);

		$stmt = $this->db->prepare("UPDATE scheduled_emails SET is_sent = 1 WHERE id = ?");

		if ($stmt === false) {
			error_log("Prepare failed: (" . $this->db->errno . ") " . $this->db->error);
			return false;
		}

		$stmt->bind_param("i", $id);
		if (!$stmt->execute()) {
			error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
			return false;
		}

		if ($stmt->affected_rows == 0) {
			error_log("No rows were updated. ID might not exist: " . $id);
		}

		$stmt->close();
		return true;
	}




	function confirm_order()
	{
		extract($_POST);
		$save = $this->db->query("UPDATE orders set status = 1 where id= " . $id);
		if ($save)
			return 1;
	}
}
