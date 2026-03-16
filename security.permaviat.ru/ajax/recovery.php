<?php
	session_start();
	include("../settings/connect_datebase.php");
	
	function decryptAES($encryptedData, $key) {
		$data = base64_decode($encryptedData);

		if ($data === false || strlen($data) < 17) {
			error_log("Invalid data or too short");
			return false;
		}

		$iv = substr($data, 0, 16);
		$encrypted = substr($data, 16);

		$keyHash = md5($key);
		$keyBytes = hex2bin($keyHash);

		$decrypted = openssl_decrypt(
			$encrypted,
			'aes-128-cbc',
			$keyBytes,
			OPENSSL_RAW_DATA,
			$iv
		);

		return $decrypted;
	}

	$login_encrypted = $_POST['login'];
	$secretKey = "qazxswedcvfrtgbn";

	$login = decryptAES($login_encrypted, $secretKey);
	
	// ищем пользователя
	$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$login."';");
	
	$id = -1;
	if($user_read = $query_user->fetch_row()) {
		// создаём новый пароль
		$id = $user_read[0];
	}
	
	function PasswordGeneration() {
		// создаём пароль
		$chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP"; // матрица
		$max=10; // количество
		$size=StrLen($chars)-1; // Определяем количество символов в $chars
		$password="";
		
		while($max--) {
			$password.=$chars[rand(0,$size)];
		}
		
		return $password;
	}
	
	if($id != -1) {
		//обновляем пароль
		$password = PasswordGeneration();
		$hashed_password = md5($password);
		
		// проверяем не используется ли пароль 
		$query_password = $mysqli->query("SELECT * FROM `users` WHERE `password`= '".$hashed_password."';");
		while($password_read = $query_password->fetch_row()) {
			// создаём новый пароль
			$password = PasswordGeneration();
			$hashed_password = md5($password);
		}
		
		// обновляем пароль
		$mysqli->query("UPDATE `users` SET `password`='".$hashed_password."' WHERE `login` = '".$login."'");
		
		// отсылаем на почту (раскомментировать для реальной отправки)
		//mail($login, 'Безопасность web-приложений КГАПОУ "Авиатехникум"', "Ваш пароль был только что изменён. Новый пароль: ".$password);
		
		// Для отладки можно записать в лог
		error_log("New password for ".$login.": ".$password);
	}
	
	echo $id;
?>
