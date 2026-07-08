<?php
session_start();

// ตรวจสอบว่ามีข้อมูล Session หรือไม่ ถ้าไม่มีให้ดีดกลับไปหน้าแรกป้องกันคนแอบเข้าลัดขั้นตอน
if (!isset($_SESSION['generated_otp'])) {
    header("Location: ../pw.php");
    exit();
}

$display_account = $_SESSION['reset_account'] ?? 'ไม่ระบุข้อมูล';
$alert_message = "";
$alert_class = "";

// ตรวจสอบว่ามาจากฝั่งเบอร์โทรศัพท์ที่ต้องจำลองเลขโค้ดขึ้นหน้าจอเพื่อใช้ส่งงานหรือไม่
if (isset($_GET['status']) && $_GET['status'] == 'simulated') {
    $alert_message = "📢 [โหมดจำลองระบบ SMS ฟรี] รหัส OTP ของคุณคือ: " . $_SESSION['generated_otp'];
    $alert_class = "info";
}

// เมื่อผู้ใช้กรอกรหัสครบ 6 ช่องแล้วกดปุ่ม "ยืนยัน"
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'])) {
    // รวมรหัสแยกกล่องจาก otp[] มารวมเป็นข้อความยาวตัวเดียว เช่น [1,2,3,4,5,6] -> 123456
    $user_otp = implode('', $_POST['otp']);

    if ($user_otp == $_SESSION['generated_otp']) {
        $alert_message = "✅ ยืนยันรหัสถูกต้อง! กำลังพาท่านไปหน้าเปลี่ยนรหัสผ่านใหม่...";
        $alert_class = "success";
        // ตรงนี้เปิดทางพุ่งไปหน้าตั้งรหัสผ่านใหม่ได้เลย เช่น: header("Refresh: 2; url=../newpw.php");
    } else {
        $alert_message = "❌ รหัส OTP ไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง";
        $alert_class = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันตัวตน OTP</title>
    <link rel="stylesheet" href="identitystyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">
    <div class="otp-card">
        <h2>ยืนยันตัวตนของคุณ</h2>
        <p>กรุณากรอกรหัส OTP ผ่าน SMS เพื่อดำเนินการต่อไป <br>
        เบอร์โทรศัพท์ : <span class="phone"></span> 
        <a href="#" class="change-num">เปลี่ยนหมายเลข</a>
        </p>

        <form action="/verify" method="POST" id="otp-form">
            <div class="otp-inputs">
                <input type="text" name="otp[]" maxlength="1" class="otp-field">
                <input type="text" name="otp[]" maxlength="1" class="otp-field">
                <input type="text" name="otp[]" maxlength="1" class="otp-field">
                <input type="text" name="otp[]" maxlength="1" class="otp-field">
                <input type="text" name="otp[]" maxlength="1" class="otp-field">
                <input type="text" name="otp[]" maxlength="1" class="otp-field">
            </div>

            <p class="timer">ส่ง OTP อีกครั้งใน 60 วินาที</p>

            <div class="btn-group">
                <button type="button" class="btn btn-back">ย้อนกลับ</button>
                <button type="submit" class="btn btn-confirm">ยืนยัน</button>
            </div>
        </form>
    </div>
</div>

<script src="identityscrip..js"></script>
</body>
</html>