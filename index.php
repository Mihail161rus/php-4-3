<?php
if (empty($_SESSION)) {
    echo '<a href="login.php">Войти на сайт</a>';
    exit;
}

$dsn = 'mysql:dbname=morlenko;host=localhost;charset=utf8';
$user = 'morlenko';
$password = 'neto1579';
$taskStatus = 0;
$description = '';
$infoText = '';
$editTaskDesc = '';
const TASK_IN_PROCESS = 1;
const TASK_IS_DONE = 2;

try {
	$db = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
    echo 'Подключение не удалось: ' . $e->getMessage();
}

/*Функция возвращает название статуса*/
function getStatusName($param)
{
    switch($param) {
        case TASK_IN_PROCESS:
            return '<span style="color:orange">в процессе</span>';
            break;

        case TASK_IS_DONE:
            return '<span style="color:green">выполнено</span>';
            break;

        default:
            return '';
            break;
    }
}

/*Добавляем задачу в список*/
if(!empty($_REQUEST['description']) && empty($_REQUEST['action'])) {
    $description = $_REQUEST['description'];

    $sqlAdd = "INSERT INTO tasks (description, is_done, date_added) VALUES (?, ?, NOW())";
    $statement = $db->prepare($sqlAdd);
    $statement->execute([$description, TASK_IN_PROCESS]);
} elseif(isset($_REQUEST['description']) && empty($_REQUEST['description'])) {
    $infoText = 'Вы не заполнили поле "Описание задачи". Задача не добавлена.';
}

/*Выполняем операции с задачами*/
if(!empty($_REQUEST['id']) && !empty($_REQUEST['action'])) {
    $taskID = $_REQUEST['id'];
    $action = $_REQUEST['action'];
    switch($action) {
        case 'done':
            $sqlUpdate = "UPDATE tasks SET is_done = ? WHERE id = ?";
            $statement = $db->prepare($sqlUpdate);
            $statement->execute([TASK_IS_DONE, $taskID]);
            header('Location: index.php');
            break;

        case 'delete':
            $sqlDelete = "DELETE FROM tasks WHERE id = ?";
            $statement = $db->prepare($sqlDelete);
            $statement->execute([$taskID]);
            header('Location: index.php');
            break;

        case 'edit':
            $sqlSelectDesc = "SELECT description FROM tasks WHERE id = ?";
            $statement = $db->prepare($sqlSelectDesc);
            $statement->execute([$taskID]);
            $taskArray = $statement->fetch();
            $editTaskDesc = $taskArray['description'];
            if(!empty($action) && !empty($_REQUEST['descEdit'])) {
                $updatedDesc = $_REQUEST['description'];
                $sqlUpdate = "UPDATE tasks SET description = ? WHERE id = ?";
                $statement = $db->prepare($sqlUpdate);
                $statement->execute([$updatedDesc, $taskID]);
                header('Location: index.php');
            }
    }
}

/*Выводим список добавленных дел в зависимости от сортировки*/
if(!empty($_REQUEST['sort']) && !empty($_REQUEST['sort_by'])) {
    $sortBy = $_REQUEST['sort_by'];
    switch($sortBy) {
        case 'date_added':
            $sqlSelect = "SELECT * FROM tasks ORDER BY date_added";
            break;
        case 'is_done':
            $sqlSelect = "SELECT * FROM tasks ORDER BY is_done";
            break;
        case 'description':
            $sqlSelect = "SELECT * FROM tasks ORDER BY description";
            break;
    }
} else {
    $sqlSelect = "SELECT * FROM tasks";
}
$statement = $db->prepare($sqlSelect);
$statement->execute();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<title>Домашнее задание к лекции 4.2</title>

    <style>
        table {
            border-collapse: collapse;
            border: 1px solid;
        }

        th {
            background-color: #eeeeee;
        }

        th, td {
            padding: 4px 10px;
            border: 1px solid;
        }
        
        form {
            display: inline-block;
            margin-right: 10px;
        }
    </style>
</head>
<body>
	<h1>Список дел</h1>

	<form method="POST">
		<input name="description" type="text" placeholder="Описание задачи" value="<?=$editTaskDesc?>">
		<input name="descEdit" type="submit" value="<?=(empty($editTaskDesc) ? 'Добавить' : 'Сохранить')?>">
	</form>

    <form method="POST">
        <label for="sort_by">Сортировать по: </label>
        <select name="sort_by" id="sort_by">
            <option <?= (!empty($_POST['sort_by']) && $_POST['sort_by'] === 'date_added') ? 'selected' : '' ?> value="date_added">дате добавления</option>
            <option <?= (!empty($_POST['sort_by']) && $_POST['sort_by'] === 'is_done') ? 'selected' : '' ?> value="is_done">статусу</option>
            <option <?= (!empty($_POST['sort_by']) && $_POST['sort_by'] === 'description') ? 'selected' : '' ?> value="description">описанию</option>
        </select>
        <input name="sort" type="submit" value="Отфильтровать">
    </form>

    <p style="color: red"><?=$infoText?></p>

	<table>
		<tr>
			<th>Описание задачи</th>
			<th>Дата добавления</th>
			<th>Статус</th>
			<th>Операции</th>
		</tr>
        <?php while($row = $statement->fetch()) : ?>
        <tr>
            <td><?=$row['description']?></td>
            <td><?=$row['date_added']?></td>
            <td><?= getStatusName($row['is_done']) ?></td>
            <td>
                <a href="index.php?id=<?=$row['id']?>&action=done">Выполнить</a>
                <a href="index.php?id=<?=$row['id']?>&action=edit">Изменить</a>
                <a href="index.php?id=<?=$row['id']?>&action=delete">Удалить</a>
            </td>
        </tr>
         <?php endwhile; ?>
	</table>
</body>
</html>