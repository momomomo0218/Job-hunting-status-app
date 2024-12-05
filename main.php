



<?php
// データベース接続
$dsn = 'mysql:dbname=tb260401db;host=localhost';
$user = 'tb-260401';
$password = 'ENZK7DpTbd';
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));


// テーブルが存在しない場合に作成
$tableCheckQuery = "SHOW TABLES LIKE 'companies'";
$tableExists = $pdo->query($tableCheckQuery)->fetchColumn();

if (!$tableExists) {
    try {
        $createTableQuery = "
        CREATE TABLE companies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            industry VARCHAR(255),
            status VARCHAR(100),
            notes TEXT
        ) ENGINE=InnoDB;
        ";
        $pdo->exec($createTableQuery);
        echo 'テーブルが作成されました。';
    } catch (PDOException $e) {
        echo 'テーブル作成中にエラーが発生しました: ' . $e->getMessage();
    }
} 


// データの追加または編集処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id'])) {
        // 編集処理
        $stmt = $pdo->prepare("UPDATE companies SET name = ?, industry = ?, status = ?, notes = ? WHERE id = ?");
        $stmt->execute([
            $_POST['name'],
            $_POST['industry'],
            $_POST['status'],
            $_POST['notes'],
            $_POST['id']
        ]);
    } else {
        // 新規追加処理
        $stmt = $pdo->prepare("INSERT INTO companies (name, industry, status, notes) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['industry'],
            $_POST['status'],
            $_POST['notes']
        ]);
    }
    header("Location: " . $_SERVER['PHP_SELF']); // リロード
    exit;
}

// 削除処理
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM companies WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: " . $_SERVER['PHP_SELF']); // リロード
    exit;
}

// データの取得
$companies = $pdo->query("SELECT * FROM companies")->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html>
<head>
    <title>就活進捗管理アプリ</title>
</head>
<body>
    <h1>就活進捗管理アプリ</h1>

    <!-- データ入力フォーム -->
    <form method="POST">
        <input type="hidden" name="id" value="<?= isset($_GET['edit']) ? $_GET['edit'] : '' ?>">
        <label>企業名: <input type="text" name="name" value="<?= isset($_GET['edit']) ? htmlspecialchars($companies[array_search($_GET['edit'], array_column($companies, 'id'))]['name']) : '' ?>" required></label><br>
        <label>業界: <input type="text" name="industry" value="<?= isset($_GET['edit']) ? htmlspecialchars($companies[array_search($_GET['edit'], array_column($companies, 'id'))]['industry']) : '' ?>"></label><br>
        <label>応募状況: 
        <select name="status">
            <option value="エントリー済み" <?= isset($_GET['edit']) && $companies[array_search($_GET['edit'], array_column($companies, 'id'))]['status'] == 'エントリー済み' ? 'selected' : '' ?>>エントリー済み</option>
            <option value="書類選考中" <?= isset($_GET['edit']) && $companies[array_search($_GET['edit'], array_column($companies, 'id'))]['status'] == '書類選考中' ? 'selected' : '' ?>>書類選考中</option>
            <option value="書類選考通過" <?= isset($_GET['edit']) && $companies[array_search($_GET['edit'], array_column($companies, 'id'))]['status'] == '書類選考通過' ? 'selected' : '' ?>>書類選考通過</option>
            <option value="一次面接通過" <?= isset($_GET['edit']) && $companies[array_search($_GET['edit'], array_column($companies, 'id'))]['status'] == '一次面接通過' ? 'selected' : '' ?>>一次面接通過</option>
            <option value="二次面接通過" <?= isset($_GET['edit']) && $companies[array_search($_GET['edit'], array_column($companies, 'id'))]['status'] == '二次面接通過' ? 'selected' : '' ?>>二次面接通過</option>
            <option value="内定" <?= isset($_GET['edit']) && $companies[array_search($_GET['edit'], array_column($companies, 'id'))]['status'] == '内定' ? 'selected' : '' ?>>内定</option>
        </select>
        </label><br>
        <label>メモ: <textarea name="notes"><?= isset($_GET['edit']) ? htmlspecialchars($companies[array_search($_GET['edit'], array_column($companies, 'id'))]['notes']) : '' ?></textarea></label><br>
        <button type="submit"><?= isset($_GET['edit']) ? '更新' : '追加' ?></button>
    </form>

    <hr>

    <!-- 登録データの表示 -->
    <h2>登録済み企業一覧</h2>
    <table border="1">
        <tr>
            <th>企業名</th>
            <th>業界</th>
            <th>応募状況</th>
            <th>メモ</th>
            <th>操作</th>
        </tr>
        <?php foreach ($companies as $company): ?>
        <tr>
            <td><?= htmlspecialchars($company['name']) ?></td>
            <td><?= htmlspecialchars($company['industry']) ?></td>
            <td><?= htmlspecialchars($company['status']) ?></td>
            <td><?= htmlspecialchars($company['notes']) ?></td>
            <td>
                <a href="?edit=<?= $company['id'] ?>">編集</a>
                <a href="?delete=<?= $company['id'] ?>" onclick="return confirm('本当に削除しますか？')">削除</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>