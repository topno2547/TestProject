<?php

// ป้องกัน Session
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

session_start();

// จำกัดการ Login ผิด
if (!isset($_SESSION['login_attempt'])) {
    $_SESSION['login_attempt'] = 0;
}

if ($_SESSION['login_attempt'] >= 5) {
    die("คุณพยายามเข้าสู่ระบบเกินกำหนด กรุณาลองใหม่ภายหลัง");
}

// --- 1. ตั้งค่าการเชื่อมต่อ ---
$conn = new mysqli("localhost", "root", "", "cockfighting_system");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

$error = "";

// --- 2. ตรวจสอบการส่งค่าจาก Form ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_input = trim($_POST['user_name']);
    $pass_input = trim($_POST['u_password']);

    // ตรวจสอบข้อมูล
    if (empty($user_input) || empty($pass_input)) {
        $error = "กรุณากรอกชื่อผู้ใช้งานและรหัสผ่าน";
    } elseif (strlen($user_input) > 50 || strlen($pass_input) > 100) {
        $error = "ข้อมูลไม่ถูกต้อง";
    } else {

        // ป้องกัน SQL Injection ด้วย Prepared Statement
        $stmt = $conn->prepare("SELECT user_id, u_password, real_name FROM member WHERE user_name = ? LIMIT 1");
        $stmt->bind_param("s", $user_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {

            // ตรวจสอบรหัสผ่าน
            if ($pass_input === $row['u_password']) {

                session_regenerate_id(true);

                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['real_name'] = $row['real_name'];

                // รีเซ็ตจำนวนครั้งที่ Login ผิด
                $_SESSION['login_attempt'] = 0;

                header("Location: dashboard.php");
                exit();

            } else {
                $_SESSION['login_attempt']++;
                $error = "รหัสผ่านไม่ถูกต้อง";
            }

        } else {
            $_SESSION['login_attempt']++;
            $error = "ไม่พบชื่อผู้ใช้งานนี้";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - Super Kaichon</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&family=Prompt:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>

<body class="login-page">

    <a href="../index.html" class="back-btn">
        <i class="bi bi-arrow-left"></i>
    </a>

    <main class="min-h-screen flex items-center justify-center px-4">
        <section class="login-card w-full max-w-[90%] sm:max-w-[430px] md:max-w-[520px]">

            <h1 class="text-2xl font-bold text-center mb-6">
                เข้าสู่ระบบเพื่อใช้งาน
            </h1>

            <?php if($error): ?>
                <div class="error-msg">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form id="login-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

                <div class="mb-4">
                    <label class="login-label">ชื่อผู้เข้าใช้งาน</label>

                    <div class="input-group">
                        <i class="bi bi-person input-icon"></i>
                        <input
                            type="text"
                            id="username"
                            name="user_name"
                            class="login-input"
                            placeholder="กรุณากรอกชื่อผู้เข้าใช้งาน"
                            required
                        >
                    </div>
                </div>

                <div class="mb-2">
                    <label class="login-label">รหัสผ่าน</label>

                    <div class="input-group">
                        <i class="bi bi-lock input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="u_password"
                            class="login-input"
                            placeholder="กรุณากรอกรหัสผ่าน"
                            required
                        >
                    </div>
                </div>

                <div class="text-right mb-6">
                    <a href="../html/Forgot-Password.html" class="text-xs font-semibold text-gray-700 hover:text-emerald-600">
                        ลืมรหัสผ่าน?
                    </a>
                </div>

                <button type="submit" class="login-submit-btn">
                    เข้าสู่ระบบ
                </button>

                <p class="text-center text-sm mt-6">
                    ยังไม่มีบัญชีใช่ไหม?
                    <a href="../html/register.html" class="font-bold text-emerald-700">
                        สมัครสมาชิก
                    </a>
                </p>

            </form>
        </section>
    </main>

</body>
</html>
