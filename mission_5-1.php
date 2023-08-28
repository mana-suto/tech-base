<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>投稿フォーム</title>
</head>
<body>
    <?php
    $dsn = 'mysql:dbname=データベース名;host=localhost';
    $user = 'ユーザー名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

    $sql = 'CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_number INT NOT NULL,
        name VARCHAR(32) NOT NULL,
        comment TEXT NOT NULL,
        post_date DATETIME NOT NULL,
        post_password VARCHAR(255) NOT NULL
    )';
    $pdo->exec($sql);
    ?>

    <?php
    $edit_number = '';
    $edit_mode = false;

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST["delete_number"]) && isset($_POST["delete_password"])) {
            // 削除フォームの処理
            $delete_number = $_POST["delete_number"];
            $delete_password = $_POST["delete_password"];

            $sql = "SELECT * FROM posts WHERE post_number = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$delete_number]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($post && $delete_password === $post['post_password']) {
                $sql = "DELETE FROM posts WHERE post_number = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$delete_number]);
            }
        } elseif (isset($_POST["name"]) && isset($_POST["comment"]) && isset($_POST["post_password"])) {
            // 新規投稿フォームの処理
            $name = $_POST["name"];
            $comment = $_POST["comment"];
            $post_password = $_POST["post_password"];

            $post_date = date("Y-m-d H:i:s");

            $sql = "SELECT MAX(post_number) FROM posts";
            $stmt = $pdo->query($sql);
            $max_post_number = $stmt->fetchColumn();
            $post_number = $max_post_number + 1;

            $sql = "INSERT INTO posts (post_number, name, comment, post_date, post_password) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$post_number, $name, $comment, $post_date, $post_password]);
        } elseif (isset($_POST["edit_number"]) && isset($_POST["edit_password"])) {
            // 編集フォームの処理
            $edit_number = $_POST["edit_number"];
            $edit_password = $_POST["edit_password"];

            $sql = "SELECT * FROM posts WHERE post_number = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$edit_number]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($post && $edit_password === $post['post_password']) {
                $name = $post['name'];
                $comment = $post['comment'];
                $edit_mode = true;
            }
        }
    }
    ?>

    <h2>投稿フォーム</h2>
    <form action="" method="post">
        <?php if ($edit_mode) : ?>
            <input type="hidden" name="edit_number" value="<?php echo $edit_number; ?>">
            編集対象番号：<?php echo $edit_number; ?><br>
        <?php endif; ?>
        名前：<input type="text" name="name" value="<?php echo isset($name) ? $name : ''; ?>"><br>
        コメント：<textarea name="comment"><?php echo isset($comment) ? $comment : ''; ?></textarea><br>
        パスワード：<input type="password" name="post_password"><br>
        <input type="submit" value="<?php echo $edit_mode ? '編集' : '送信'; ?>">
    </form>

    <h2>削除フォーム</h2>
    <form action="" method="post">
        削除対象番号：<input type="number" name="delete_number"><br>
        パスワード：<input type="password" name="delete_password"><br>
        <input type="submit" value="削除">
    </form>

    <h2>編集フォーム</h2>
    <form action="" method="post">
        編集対象番号：<input type="number" name="edit_number"><br>
        パスワード：<input type="password" name="edit_password"><br>
        <input type="submit" value="編集番号">
    </form>

    <h2>投稿一覧</h2>
    <?php
    $sql = "SELECT * FROM posts";
    $stmt = $pdo->query($sql);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($posts as $display_post) {
        echo "投稿番号：{$display_post['post_number']}, 投稿日時：{$display_post['post_date']}, 名前：{$display_post['name']}, コメント：{$display_post['comment']}<br>";
    }
    ?>

</body>
</html>
