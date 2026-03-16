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

    $IdUser = $_SESSION['user'];
    $Message_encrypted = $_POST["Message"];
    $IdPost = $_POST["IdPost"];

	$secretKey = "qazxswedcvfrtgbn";
	$Message = decryptAES($Message_encrypted, $secretKey);
	
	// Проверка на пустое сообщение после расшифровки
	if (!$Message || trim($Message) == '') {
		echo "Error: Empty message";
		exit();
	}
	
	// Экранируем спецсимволы для предотвращения XSS-атак
	$Message = $mysqli->real_escape_string($Message);
	$IdPost = (int)$IdPost;
	$IdUser = (int)$IdUser;

    $mysqli->query("INSERT INTO `comments`(`IdUser`, `IdPost`, `Messages`) VALUES ({$IdUser}, {$IdPost}, '{$Message}');");
	
	echo "Comment added successfully";
?>
