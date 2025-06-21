<?php
// 케이넷소프트 영수증 관리 시스템
// Copyright ⓒ 2025, knetsoft All rights reserved.

session_start();

// DB 연결
$db = new SQLite3(__DIR__ . '/receipt.db');

// 비밀번호 테이블 생성 (최초 1회만 실행)
$db->exec("CREATE TABLE IF NOT EXISTS admin_password (id INTEGER PRIMARY KEY, password TEXT)");

$row = $db->querySingle("SELECT password FROM admin_password WHERE id=1");
if (!$row) {
    $default_pw = password_hash('qwsno5407A', PASSWORD_DEFAULT);
    $db->exec("INSERT INTO admin_password (id, password) VALUES (1, '$default_pw')");
}

// 로그인 처리
if (!isset($_SESSION['admin_login'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_pw'])) {
        $pw_row = $db->querySingle("SELECT password FROM admin_password WHERE id=1");
        if ($pw_row && password_verify($_POST['admin_pw'], $pw_row)) {
            $_SESSION['admin_login'] = true;
            header("Location: admin.php");
            exit;
        } else {
            $error = "비밀번호가 올바르지 않습니다.";
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="ko">
    <head>
        <meta charset="UTF-8">
        <title>관리자 로그인</title>
        <style>
            body {
                background: #f7f7f7;
                font-family: 'Pretendard', 'Malgun Gothic', Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }
            .login-box {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 4px 24px rgba(0,0,0,0.08);
                width: 20%;
                min-width: 260px;
                max-width: 400px;
                padding: 16px 3vw 48px 3vw; /* 위쪽 패딩 줄이고 아래쪽 늘림 */
                border: 1px dashed #d1d5db;
                text-align: center;
                margin-top: 12px;    /* 위쪽 여백 줄임 */
                margin-bottom: 48px; /* 아래쪽 여백 늘림 */
            }
            input[type="password"] {
                width: 80%;
                padding: 10px;
                font-size: 1rem;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                margin-bottom: 16px;
            }
            button {
                padding: 10px 20px;
                font-size: 1rem;
                background: #2d6a4f;
                color: #fff;
                border: none;
                border-radius: 6px;
                cursor: pointer;
            }
            button:hover {
                background: #40916c;
            }
            .error {
                color: #c00;
                margin-bottom: 10px;
            }

            /* 태블릿: 화면 너비 60vw 이하 */
            @media (max-width: 60vw) {
                .login-box {
                    width: 128vw;
                    max-width: 148vw;
                    min-width: 0;
                    padding-left: 0;
                    padding-right: 0;
                }
            }

            /* 모바일: 화면 너비 35vw 이하 */
            @media (max-width: 35vw) {
                .login-box {
                    width: 100vw;
                    max-width: 100vw;
                    min-width: 0;
                    height: 100vh;           /* 높이 전체 */
                    margin: 0;               /* 여백 제거 */
                    border-radius: 0;        /* 모서리 둥글기 제거 */
                    box-shadow: none;        /* 그림자 제거 */
                    padding: 0 0 48px 0;     /* 좌우 패딩 제거, 아래쪽만 유지 */
                    display: flex;
                    flex-direction: column;
                    justify-content: center; /* 세로 중앙 정렬 */
                }
                body {
                    margin: 0;
                    padding: 0;
                    background: #fff;
                }
            }
        </style>
    </head>
    <body>
        <form class="login-box" method="post">
            <div style="font-size:1.2rem;font-weight:bold;margin-bottom:18px;">관리자 비밀번호 입력</div>
            <?php if (isset($error)): ?>
                <div class="error"><?=htmlspecialchars($error)?></div>
            <?php endif; ?>
            <input type="password" name="admin_pw" placeholder="비밀번호" required autofocus>
            <br>
            <button type="submit">로그인</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// 로그아웃 처리
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// 영수증번호 자동 생성 함수
function generate_receipt_no() {
    $date = date('YmdHis'); // 20250618003400
    $rand = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT); // 001~999
    return $date . $rand;
}

// 폼 제출 시 데이터 저장
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = date('Y-m-d\TH:i'); // HTML datetime-local 형식
    $receipt_no = generate_receipt_no();
    $manager = '박건희';
    $phone = '010-2870-3861';
    $email = 'stone@knetsoft.kr';
    $payment_method = $_POST['payment_method'];
    $card_no = $_POST['card_no'];
    $approval_no = $_POST['approval_no'];
    $customer_name = $_POST['customer_name'];
    $customer_phone = $_POST['customer_phone'];
    $items = $_POST['item_name'];
    $prices = $_POST['price'];

    // 영수증 저장 (고객 정보 컬럼 추가 필요)
    $stmt = $db->prepare('INSERT INTO receipts (receipt_no, date, manager, phone, email, payment_method, card_no, approval_no, customer_name, customer_phone) VALUES (:receipt_no, :date, :manager, :phone, :email, :payment_method, :card_no, :approval_no, :customer_name, :customer_phone)');
    $stmt->bindValue(':receipt_no', $receipt_no, SQLITE3_TEXT);
    $stmt->bindValue(':date', $date, SQLITE3_TEXT);
    $stmt->bindValue(':manager', $manager, SQLITE3_TEXT);
    $stmt->bindValue(':phone', $phone, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':payment_method', $payment_method, SQLITE3_TEXT);
    $stmt->bindValue(':card_no', $card_no, SQLITE3_TEXT);
    $stmt->bindValue(':approval_no', $approval_no, SQLITE3_TEXT);
    $stmt->bindValue(':customer_name', $customer_name, SQLITE3_TEXT);
    $stmt->bindValue(':customer_phone', $customer_phone, SQLITE3_TEXT);
    $stmt->execute();

    // 항목 저장
    for ($i = 0; $i < count($items); $i++) {
        if (trim($items[$i]) === '' || trim($prices[$i]) === '') continue;
        $stmt_item = $db->prepare('INSERT INTO receipt_items (receipt_no, item_name, price) VALUES (:receipt_no, :item_name, :price)');
        $stmt_item->bindValue(':receipt_no', $receipt_no, SQLITE3_TEXT);
        $stmt_item->bindValue(':item_name', $items[$i], SQLITE3_TEXT);
        $stmt_item->bindValue(':price', $prices[$i], SQLITE3_INTEGER);
        $stmt_item->execute();
    }
    echo "<script>alert('저장되었습니다.\\n영수증번호: {$receipt_no}');location.href='admin.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- 0619 반응형 viewport 추가 -->
    <title>케이넷소프트 - 영수증 데이터 입력</title>
    <style>
        body {
            background: #f7f7f7;
            font-family: 'Pretendard', 'Malgun Gothic', Arial, sans-serif;
            margin: 0;
        }

        .container {
            width: 40%;
            max-width: 600px;
            margin: 12px auto 48px auto; /* 위쪽 여백 줄이고, 아래쪽 여백 늘림 */
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 32px 28px;
            font-size: 18px;
        }
        

        h2 {
            text-align: center;
            margin-bottom: 24px;
        }

        label {
            display: block;
            margin-top: 14px;
            font-weight: 500; 
        }

        input, select {
            width: 90%;
            padding: 8px;
            margin-top: 4px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }

        .items-table {
            width: 100%;
            margin-top: 18px;
            border-collapse: collapse;
        }

        .items-table th, .items-table td {
            padding: 6px;
            border-bottom: 1px solid #eee;
        }

        .add-btn {
            margin-top: 10px;
            padding: 6px 16px;
            background: #2d6a4f;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .add-btn:hover {
            background: #40916c;
        }

        .submit-btn {
            margin-top: 24px;
            width: 100%;
            padding: 12px;
            background: #2d6a4f;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            cursor: pointer;
        }

        .submit-btn:hover {
            background: #40916c;
        }

        .remove-btn {
            color: #c00;
            cursor: pointer;
            font-size: 1.1em;
        }

        .readonly {
            background: #f1f3f5;
            color: #888;
        }

        /* 0619 미디어 쿼리 맨 아래로 위치 이동 */
        /* 태블릿 (화면 너비 48em 이하, 약 768px) */
        @media (max-width: 60vw) {
            .container {
                width: 98%;
                font-size: 18px;
                margin-bottom: 64px; /* 모바일/태블릿에서 아래쪽 여백 더 늘림 */
            }
        }

        /* 모바일 (화면 너비 30em 이하, 약 480px) */
        @media (max-width: 35vw) {
            .container {
                width: 120%;
                max-width: 150vw;
                font-size: 22px; /* 모바일에서 폰트 크기 확대 */
                margin-bottom: 80px; /* 모바일에서 아래쪽 여백 더 늘림 */
                margin-top: 4px;    /* 모바일에서 위쪽 여백 더 줄임 */
                padding: 18px 4vw;
            }
        }
    </style>
    <script>
        function addRow() {
            const table = document.getElementById('items-body');
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="text" name="item_name[]" required></td>
                <td><input type="number" name="price[]" required min="0"></td>
                <td><span class="remove-btn" onclick="removeRow(this)">삭제</span></td>
            `;
            table.appendChild(row);
        }
        function removeRow(btn) {
            btn.parentElement.parentElement.remove();
        }
        window.onload = function() {
            // 날짜/시간 필드 자동 입력
            document.getElementById('date').value = new Date().toISOString().slice(0,16);
        }
    </script>
</head>
<body>
    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <h2 style="margin-bottom:0;">영수증 데이터 입력</h2>
            <a href="?logout=1" style="background:#eee;color:#333;padding:8px 18px;border-radius:6px;text-decoration:none;font-size:1rem;font-weight:500;">로그아웃</a>
        </div>
        <hr style="margin-bottom:24px;">
        <form method="post">
            <label>영수증 번호 (자동생성)</label>
            <input type="text" name="receipt_no" value="자동생성" class="readonly" readonly>

            <label>날짜/시간 (자동입력)</label>
            <input type="datetime-local" name="date" id="date" class="readonly" readonly>

            <div><strong>담당자:</strong> aaa</div>
            <div><strong>연락처:</strong> 010-1234-1234</div>
            <div><strong>이메일:</strong> stone@aaa.kr</div>

            <label>고객 이름</label>
            <input type="text" name="customer_name" required>

            <label>고객 전화번호</label>
            <input type="text" name="customer_phone" required>

            <label>결제수단</label>
            <select name="payment_method">
                <option value="카드">카드</option>
                <option value="현금">현금</option>
                <option value="기타">기타</option>
            </select>

            <label>카드번호</label>
            <input type="text" name="card_no">

            <label>승인번호</label>
            <input type="text" name="approval_no">

            <label>영수증 항목</label>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>상품명</th>
                        <th>금액</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="items-body">
                    <tr>
                        <td><input type="text" name="item_name[]" required></td>
                        <td><input type="number" name="price[]" required min="0"></td>
                        <td><span class="remove-btn" onclick="removeRow(this)">삭제</span></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="add-btn" onclick="addRow()">+ 항목 추가</button>
            <button type="submit" class="submit-btn">저장</button>
        </form>
    </div>
</body>
</html>
