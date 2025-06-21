<?php
// 케이넷소프트 영수증 조회 시스템
// Copyright ⓒ 2025, knetsoft All rights reserved.

$receipt_no = isset($_GET['receipt_no']) ? trim($_GET['receipt_no']) : '';
$customer_phone = isset($_GET['customer_phone']) ? trim($_GET['customer_phone']) : '';
$customer_name = isset($_GET['customer_name']) ? trim($_GET['customer_name']) : '';

if (!$receipt_no && !$customer_phone) {
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- 추가 -->
    <title>케이넷소프트 - 영수증 조회</title>
    <style>
        body {
            background: #f7f7f7;
            font-family: 'Pretendard', 'Malgun Gothic', Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .input-box {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            width: 40%; /* 기존 350px에서 40%로 변경 */
            min-width: 260px; /* 최소 너비 지정 */
            max-width: 400px; /* 최대 너비 지정 */
            padding: 32px 24px;
            border: 1px dashed #d1d5db;
            text-align: center;
        }
        .input-box input[type="text"] {
            width: 80%;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            margin-bottom: 16px;
        }
        .input-box button {
            padding: 10px 24px;
            font-size: 1rem;
            background: #2d6a4f;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .input-box button:hover {
            background: #40916c;
        }

        /* 기본 스타일 */
        .container {
            width: 40%;
            margin: 0 auto;
            font-size: 18px;
        }

        /* 태블릿 (화면 너비 60vw 이하) */
        @media (max-width: 60vw) {
            .container {
                width: 96vw;
                font-size: 16px;
            }
        }

        /* 모바일 (화면 너비 35vw 이하) */
        @media (max-width: 35vw) {
            .container {
                width: 98vw;
                max-width: none;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <form class="input-box" method="get" action="">
        <div style="font-size:1.2rem;font-weight:bold;margin-bottom:18px;">영수증 조회</div>
        <input type="text" name="customer_name" placeholder="이름 (선택)">
        <input type="text" name="customer_phone" placeholder="전화번호 ('-' 없이 입력)">
        <input type="text" name="receipt_no" placeholder="영수증 번호">
        <br>
        <button type="submit">조회</button>
        <div style="font-size:0.9em;color:#888;margin-top:10px;">* 전화번호 또는 영수증번호 중 하나만 입력해도 조회됩니다.</div>
    </form>
</body>
</html>
<?php
    exit;
}

// DB 연결
$db = new SQLite3('receipt.db');

// 영수증 정보 조회 쿼리
$query = "SELECT * FROM receipts WHERE 1=1";
$params = [];
if ($receipt_no) {
    $query .= " AND receipt_no = :receipt_no";
    $params[':receipt_no'] = $receipt_no;
}
if ($customer_phone) {
    $query .= " AND customer_phone = :customer_phone";
    $params[':customer_phone'] = $customer_phone;
}
if ($customer_name) {
    $query .= " AND customer_name = :customer_name";
    $params[':customer_name'] = $customer_name;
}

$stmt = $db->prepare($query);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val, SQLITE3_TEXT);
}
$result = $stmt->execute();

$receipts = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $receipts[] = $row;
}

// 영수증이 여러 개일 때 처리
if (count($receipts) > 1 && !$receipt_no) {
    // 영수증번호 선택 폼 출력
    ?>
    <!DOCTYPE html>
    <html lang="ko">
    <head>
        <meta charset="UTF-8">
        <title>영수증 선택</title>
        <style>
            body { background: #f7f7f7; font-family: 'Pretendard', 'Malgun Gothic', Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; }
            .select-box { 
                background: #fff; 
                border-radius: 16px; 
                box-shadow: 0 4px 24px rgba(0,0,0,0.08); 
                width: 350px; 
                padding: 32px 24px; 
                border: 1px dashed #d1d5db; 
                text-align: center; 
            }
            select { width: 90%; padding: 10px; font-size: 1rem; border: 1px solid #d1d5db; border-radius: 6px; margin-bottom: 16px; }
            button { padding: 10px 24px; font-size: 1rem; background: #2d6a4f; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
            button:hover { background: #40916c; }

            /* 기본 스타일 */
            .container {
                width: 40%;
                margin: 0 auto;
                font-size: 18px;
            }

            /* 태블릿 (화면 너비 60vw 이하) */
            @media (max-width: 60vw) {
                .container {
                    width: 95vw;
                    font-size: 16px;
                }
            }

            /* 모바일 (화면 너비 35vw 이하) */
            @media (max-width: 35vw) {
                .container {
                    width: 98vw;
                    max-width: none;
                    font-size: 14px;
                }
                .select-box {
                    width: 20vw;
                    margin-left: 5vw;
                    margin-right: 5vw;
                    padding-left: 5vw;
                    padding-right: 5vw;
                }
            }
        </style>
    </head>
    <body>
        <form class="select-box" method="get" action="">
            <div style="font-size:1.1rem;font-weight:bold;margin-bottom:18px;">여러 건의 영수증이 있습니다.<br>영수증 번호를 선택하세요.</div>
            <input type="hidden" name="customer_phone" value="<?=htmlspecialchars($customer_phone)?>">
            <input type="hidden" name="customer_name" value="<?=htmlspecialchars($customer_name)?>">
            <select name="receipt_no" required>
                <option value="">영수증 번호 선택</option>
                <?php foreach ($receipts as $r): ?>
                    <option value="<?=htmlspecialchars($r['receipt_no'])?>">
                        <?=htmlspecialchars($r['receipt_no'])?> (<?=htmlspecialchars($r['date'])?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <br>
            <button type="submit">조회</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// 1건만 있을 때
$receipt = count($receipts) > 0 ? $receipts[0] : null;

if (!$receipt) {
    die('해당 영수증을 찾을 수 없습니다.');
}

// 영수증 항목 조회
$stmt_items = $db->prepare('SELECT * FROM receipt_items WHERE receipt_no = :receipt_no');
$stmt_items->bindValue(':receipt_no', $receipt['receipt_no'], SQLITE3_TEXT);
$items_result = $stmt_items->execute();

$items = [];
$total = 0;
while ($row = $items_result->fetchArray(SQLITE3_ASSOC)) {
    $items[] = $row;
    $total += $row['price'];
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>영수증</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: #f7f7f7;
            font-family: 'Pretendard', 'Malgun Gothic', Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0; /* 0619 추가 */
        } 
        .receipt {
            margin: 0 auto; /* 0619 추가 */
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            max-width: 400px; /* 0619 수정 */
            padding: 32px 24px;
            border: 1px dashed #d1d5db;
            box-sizing: border-box; /* 0619 추가 */
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 16px;
        }
        .receipt-title {
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 2px;
            color: #333;
        }
        .receipt-info {
            font-size: 0.75rem;
            color: #555;
            margin: 8px 0 16px 0;
            border-radius: 8px;
            padding: 10px 12px;
            text-align: left;
        }
        .receipt-info span {
            display: block;
            margin-bottom: 2px;
        }
        .payment-info {
            font-size: 0.85rem;
            color: #555;
            margin: 16px 0 12px 0;
            border-radius: 8px;
            background: #f1f3f5;
            padding: 10px 12px;
            text-align: left;
        }
        .payment-info span {
            display: block;
            margin-bottom: 2px;
        }
        .receipt-items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .receipt-items-table th, .receipt-items-table td {
            padding: 8px 4px;
            border-bottom: 1px dotted #e5e7eb;
            text-align: left;
            font-size: 0.97rem;
        }
        .receipt-items-table th {
            color: #888;
            font-weight: 600;
            background: #f8fafc;
        }
        .receipt-items-table td:last-child, .receipt-items-table th:last-child {
            text-align: right;
        }
        .receipt-items-table tr:last-child td {
            border-bottom: none;
        }
        .receipt-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.1rem;
            font-weight: bold;
            margin-top: 12px;
            color: #2d6a4f;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 28px;
            font-size: 0.95rem;
            color: #aaa;
        }
        .company-info {
            margin-top: 18px;
            font-size: 0.93rem;
            color: #888;
            text-align: center;
            border-top: 1px dashed #e5e7eb;
            padding-top: 10px;
        }

        /* 기본 스타일 */
        .container {
            width: 95%; /* 0619 수정 */
            margin: 0 auto;
            font-size: 18px;
        }

        /* 태블릿 (화면 너비 60vw 이하) */
        @media (max-width: 60vw) {
            .container {
                width: 95vw;
                font-size: 16px;
            }
        }

        /* 모바일 (화면 너비 35vw 이하) */
        @media (max-width: 35vw) {
            .container {
                width: 98vw;
                max-width: none;
                font-size: 14px;
            }

            .receipt {
                width: 90vw;
                max-width: 450px;
                height: auto;           /* 필요한 만큼 높이 */
                margin: 0 auto;              /* 공백 제거 */
                padding: 32px 16px;     /* 좌우 여백 최소화 */
                border-radius: 0;       /* 둥근 모서리 제거 (선택 사항) */
                box-shadow: none;       /* 그림자 제거 (선택 사항) */
            }
        }

        /* 다운로드 버튼 스타일 */
        .download-btn-wrap {
            text-align: center;
            margin-top: 38px;
            margin-bottom: 10px;
        }
        #download-btn {
            padding: 8px 18px;
            font-size: 1rem;
            background: #2d6a4f;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        #download-btn:hover {
            background: #40916c;
        }
    </style>
    <!-- html2canvas CDN 추가 -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="receipt" id="receipt-area">
            <div class="receipt-header">
                <div class="receipt-title">영수증</div>
            </div>
            <div class="receipt-info">
                <span>영수증 번호: <?=htmlspecialchars($receipt['receipt_no'])?></span>
                <span>시간: <?=htmlspecialchars($receipt['date'])?></span>
                <span>담당자: <?=htmlspecialchars($receipt['manager'])?></span>
                <span>연락처: <?=htmlspecialchars($receipt['phone'])?></span>
                <span>이메일: <?=htmlspecialchars($receipt['email'])?></span>
                <span>고객명: <?=htmlspecialchars($receipt['customer_name'])?></span>
                <span>고객전화: <?=htmlspecialchars($receipt['customer_phone'])?></span>
            </div>
            <table class="receipt-items-table">
                <thead>
                    <tr>
                        <th>상품명</th>
                        <th>금액</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?=htmlspecialchars($item['item_name'])?></td>
                        <td><?=number_format($item['price'])?> 원</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="receipt-total">
                <span>합계(부가세 포함)</span>
                <span><?=number_format($total)?> 원</span>
            </div>
            <div class="payment-info">
                <span>결제수단: <?=htmlspecialchars($receipt['payment_method'])?></span>
                <span>카드번호: <?=htmlspecialchars($receipt['card_no'])?></span>
                <span>승인번호: <?=htmlspecialchars($receipt['approval_no'])?></span>
                <span>결제금액: <?=number_format($total)?> 원</span>
            </div>
            <div class="receipt-footer">
                구매해주셔서 감사합니다!<br>
                <span style="font-size:0.9em;">문의: 0507-1374-6680</span>
            </div>
            <div class="company-info">
                <div>상호명: 케이넷소프트</div>
                <div>사업자등록번호: 228-04-06712</div>
                <div>대표자: 박건희</div>
                <div>주소: 경기도 고양시 덕양구 도래울로 16</div>
            </div>
        </div>
        <div class="download-btn-wrap">
            <button id="download-btn">
                이미지로 저장
            </button>
        </div>
    </div>
    <script>
        document.getElementById('download-btn').addEventListener('click', function() {
            html2canvas(document.getElementById('receipt-area')).then(function(canvas) {
                var link = document.createElement('a');
                link.download = 'receipt.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        });
    </script>
</body>
</html>