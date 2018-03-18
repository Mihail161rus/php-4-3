<?php
session_start();

$dsn = 'mysql:dbname=morlenko;host=localhost;charset=utf8';
$user = 'morlenko';
$password = 'neto1579';
$infoText = '';

try {
    $db = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
    echo 'Подключение не удалось: ' . $e->getMessage();
}

/*Регистрация пользователя*/
if(!empty($_POST['reg_submit'])) {
    $login = $_POST['login'];
    $password = md5($_POST['password']);

    $sqlAdd = "INSERT INTO user (login, password) VALUES (?, ?)";
    $statement = $db->prepare($sqlAdd);
    $statement->execute([$login, $password]);
    header('Location: index.php');
} elseif(!empty($_POST['reg_submit']) && (empty($_POST['login']) || empty($_POST['password']))) {
    $infoText = 'Вы ввели не все данные для регистрации';
}

/*Авторизация пользователя*/
if(!empty($_POST['auth_submit'])) {
    $login = $_POST['login'];
    $password = md5($_POST['password']);

    $sqlSelect = "SELECT * FROM user";
    $statement = $db->prepare($sqlSelect);
    $statement->execute();
    $usersArr = $statement->fetch(PDO::FETCH_ASSOC);
    echo '<pre>';
    print_r($usersArr);
    echo '</pre>';
}

?>


<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Панель авторизации</title>
</head>
<body>
    <h1>Панель авторизации</h1>

    <form method="post">
        <input name="login" type="text" placeholder="Логин">
        <input name="password" type="password" placeholder="Пароль">
        <input name="auth_submit" type="submit" value="Войти">
        <input name="reg_submit" type="submit" value="Регистрация">
    </form>
</body>
</html>