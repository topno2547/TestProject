<?php
session_start();

$message = "";
$message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account_input = trim($_POST['account_input']);

    if (!empty($account_input)) {
        // 1. สุ่มเลข OTP 6 หลัก
        $otp_code = rand(100000, 999999);
        $_SESSION['reset_account'] = $account_input;
        $_SESSION['generated_otp'] = $otp_code;

        // 2. ตรวจสอบว่าเป็น Email หรือไม่ ถ้าใช่ให้ใช้ API ยิงเมลออกทันที
        if (filter_var($account_input, FILTER_VALIDATE_EMAIL)) {
            
            // --- ตั้งค่าสำหรับส่งผ่าน Brevo API (cURL) ---
            $api_key = ""; 
            $from_email = "b132a5001@smtp-brevo.com";
            $to_email = $account_input;
            
            $subject = "รหัส OTP สำหรับรีเซ็ตรหัสผ่านของคุณ";
            $body = "รหัสตรวจสอบยืนยันตัวตนของคุณคือ: " . $otp_code . " \n(มีอายุการใช้งาน 5 นาที)";

            // จัดเตรียมข้อมูลส่งในรูปแบบ JSON ตามคู่มือของ Brevo
            $data = array(
                "sender" => array("email" => $from_email, "name" => "ระบบรีเซ็ตรหัสผ่าน"),
                "to" => array(array("email" => $to_email)),
                "subject" => $subject,
                "textContent" => $body
            );

            // ใช้ cURL ยิง HTTP POST เข้าหา Brevo API โดยตรง
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.brevo.com/v3/smtp/email');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            
            // ข้ามการเช็คใบรับรอง SSL ของเครื่อง Localhost เพื่อป้องกันการบล็อกความปลอดภัย
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $headers = array();
            $headers[] = 'Accept: application/json';
            $headers[] = 'Api-Key: ' . $api_key;
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            curl_close($ch);

            // ส่งคำสั่งเสร็จแล้ว กระโดดไปหน้ากรอก OTP ทันที
            header("Location: Identity/identity.php");
            exit();

        } elseif (preg_match('/^[0-9]{9,10}$/', $account_input)) {
            // กรณีผู้ใช้กรอกเป็นเบอร์มือถือ ให้สลับไปโหมดจำลอง (โชว์รหัสบนจอ) เพื่อทดสอบ
            header("Location: Identity/identity.php?status=simulated");
            exit();
        } else {
            $message = "รูปแบบอีเมลหรือเบอร์โทรศัพท์ไม่ถูกต้อง";
            $message_class = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน?</title>
    <link rel="stylesheet" href="pwstyle.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>ลืมรหัสผ่าน?</h2>
            <p class="subtitle">กรุณาระบุบัญชีที่คุณต้องการรีเซ็ตรหัสผ่าน<br>หมายเลขโทรศัพท์หรืออีเมล</p>
            
            <?php if(!empty($message)): ?>
                <div class="alert <?php echo $message_class; ?>" style="color:red; margin-bottom:15px; text-align:center;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="pw.php" method="POST">
                <div class="input-group">
                    <input type="text" name="account_input" placeholder="กรุณากรอกหมายเลขโทรศัพท์หรืออีเมล" required autocomplete="off">
                </div>
                <div class="button-group">
                    <button type="button" class="btn-back" onclick="window.history.back();">ย้อนกลับ</button>
                    <button type="submit" class="btn-confirm">ยืนยัน</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>