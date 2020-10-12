<?php

$name = "";
$password = "";
$sex = "";
$country = "";
$birth_year = "";
$today_year = date("Y");
$email = "";
$about_yourself = "";

require_once (__DIR__ . '/config/config.php');
require_once (__DIR__ . '/config/error_msg.php');









/*
// config/form_contact_validation.php

return = array(
        'name' => array(
                'filter' => FILTER_VALIDATE_REGEXP,
                'options' => ["options" => ["regexp" => "/^\w{5,20}$/"]]
        ),
        'name' => array(
                'filter' => FILTER_VALIDATE_REGEXP,
                'options' => ["options" => ["regexp" => "/^\w{5,20}$/"]]
        ),
);
*/

//$form_contact_validation = require (dirname(__FILE__) . '/../config/form_contact_validation.php');


//типа запрос из БД на существующих пользователей
$existing_users = ["admin", "superuser", "guest", "animal"];

    //параметр $options для filter_var(), который устанавливает диапазон допустимых возрастов
$birth_options = ["options" => ["min_range" => 1930, "max_range" => $today_year]];
    //параметр $options для filter_var(), который устанавливает правила пароля
$password_options = ["options" => ["regexp" => "/^\w{10,30}$/"]];
    //параметр $options для filter_var(), которые устанавливает допустимые символы имени
$name_options = ["options" => ["regexp" => "/^\w{5,20}$/"]];
    //параметр $options для filter_var(), которые устанавливает допустимые символы в строке "О себе"
$about_yourself_options = ["options" => ["regexp" => "/^(\s|\w){10,200}$/"]];

//сюда будут записываться ошибки
$validation_error = "";

//функция первичной обработки данных, полученных из $_POST
function sanitizer($string, $max_length) {
    $string = trim($string);
    $string = htmlspecialchars($string);
    $string = mb_substr($string, 0, $max_length);
    return $string;
}

//функция извлечения $_POST
function postReaper($inputs_properties) {
    $ip = $inputs_properties;
    foreach ($ip as $key => $value) {
        if (isset($ip[$key]["max_length"])) {
            @$sanitized_post[$key] = mb_substr($_POST[$key], 0, $ip[$key]["max_length"]);
        } else {
            @$sanitized_post[$key] = $_POST[$key];
        }
        $sanitized_post[$key] = htmlspecialchars(trim($sanitized_post[$key]));
    }
    return $sanitized_post;
}


//обработчик ошибок
function error_handler() {}


//сверка с базой данной
function dbCheck($sanitized_post, $inputs_properties, &$validation_error, $error_msg, $db_check) {
    $ip = $inputs_properties;
    $sp = $sanitized_post;
    foreach ($sp as $key => $value) {
        echo "$key => $value<br>";
        //valid - если данные должны быть в БД
        if (isset($ip[$key]["db_check"])) {
            if (in_array($sp[$key], $db_check[$key])) {
                $validated_post_db[$key] = $sp[$key];
            } else {
                $validation_error .= $error_msg[$key]["db_check"];
            }
        }
        //valid - если данные НЕ должны быть в БД
        if (isset($ip[$key]["db_check_reverse"])) {
            if (!in_array($sp[$key], $db_check[$key])) {
                $validated_post_db[$key] = $sp[$key];
            } else {
                $validation_error .= $error_msg[$key]["db_check"];
            }
        }
    }
    return @$validated_post_db;
}

//валидатор на основе filter_var()
function validator($sanitized_post, $inputs_properties, $error_msg, $validated_post_db, &$validation_error) {
    $ip = $inputs_properties;
    $sp = $sanitized_post;
    foreach ($sp as $key => $value) {
        if (isset($ip[$key]["filter"])) {
            if (isset($ip[$key]["filter_var_options"])) {
                if (filter_var($sp[$key], $ip[$key]["filter"], $ip[$key]["filter_var_options"])) {
                    $validated_post[$key] = $sp[$key];
                } else {
                    $validation_error .= $error_msg[$key]["validator"];
                }
            } else {
                if (filter_var($sp[$key], $ip[$key]["filter"])) {
                    $validated_post[$key] = $sp[$key];
                } else {
                    $validation_error .= $error_msg[$key]["validator"];
                }
            }
        }
    }
    return $validated_post;
}
$sanitized_post = postReaper($inputs_properties);
$validated_post_db = dbCheck(postReaper($inputs_properties), $inputs_properties,$validation_error, $error_msg, $db_check);
$validated_post = validator(postReaper($inputs_properties), $inputs_properties, $error_msg, $validated_post_db, $validation_error);


//foreach ($validated_post_db as $key => $value) {
    /*if (isset($inputs_properties[$key]["filter"]) && (isset($inputs_properties[$key]["db_check"]) || isset($inputs_properties[$key]["db_check_reverse"]))) {
        echo "Hi!";
        //$correct_post[$key] = $validated_post_db[$key];
        //$correct_post[$key] = $validated_post[$key];
    }*/
    //echo "$key => $value<br>";
//}


#################################
##### блок валидации данных #####
#################################
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $raw_name = sanitizer($_POST["name"], 20);
    $raw_password = sanitizer($_POST["password"], 30);
    $raw_sex = $_POST["sex"];
    $raw_email = sanitizer($_POST["email"], 50);
    $raw_birth_year = sanitizer($_POST["birth_year"], 4);
    $raw_about_yourself = sanitizer($_POST["about_yourself"], 200);

    //валидация имени
    if (filter_var($raw_name, FILTER_VALIDATE_REGEXP, $name_options)) {
        if (in_array($raw_name, $existing_users)) {
            $validation_error .= "* Пользователь с таким именем уже есть. <br>";
        } else {
            $name = $raw_name;
        }
    } else {
        $validation_error .= "* Имя должно состоять из _, латиницы или цифр (от 5 до 20 символов). <br>";
    }

    //валидация пароля
    if (filter_var($raw_password, FILTER_VALIDATE_REGEXP, $password_options)) {
        $password = $raw_password;
    } else {
        $validation_error .= "* Пароль должен состоять из латинских букв, цифр (от 10 до 30 символов). <br>";
    }

    //валидация пола
    if (in_array($raw_sex, ["male", "female"])) {
        $sex = $raw_sex;
    } else {
        $validation_error .= "* Неверно указан пол. <br>";
    }

    //валидация email
    if (filter_var($raw_email, FILTER_VALIDATE_EMAIL)) {
        $email = $raw_email;
    } else {
        $validation_error .= "* Неверно указан email. <br>";
    }

    //валидация даты рождения
    if (filter_var($raw_birth_year, FILTER_VALIDATE_INT, $birth_options)) {
        $birth_year = $raw_birth_year;
    } else {
        $validation_error .= "* Неверно указан возраст. <br>";
    }

    //валидация строки "О себе"
    if (filter_var($raw_about_yourself, FILTER_VALIDATE_REGEXP, $about_yourself_options)) {
        $about_yourself = $raw_about_yourself;
    } else {
        $validation_error .= "* Строка \"О себе\" должна состоять из латинских букв, цифр, допускаются пробелы (от 10 до 200 символов). <br>";
    }
}
########################################
##### конец блока валидации данных #####
########################################
?>





<?php
//запрещаем кешировать страницу и устанавливаем кодировку
header("Cache-Control: no-cache");
header("Content-Type: text/html; charset=utf-8");
?>

<!DOCTYPE html>
<html lang="ru">
    <head>
        <title>Регистрация пользователя</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #b5b5b5;
            }
            .error {
                color: #e52222;
                font-size: 110%;
                font-family: serif;
            }
            .accepted {
                color: green;
                font-size: 110%;
                font-family: serif;
            }
            .description {
                color: #0075fd;
                font-size: 80%;
                font-family: serif;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <h1>Регистрация пользователя</h1>

        <!--############################# ФОРМА ВВОДА #############################-->
        <form method="post" action="" enctype="multipart/form-data">
            <p>
                <label>
                    Введи имя: <input type="text" maxlength="20" name="name" value="<?=$raw_name??'';?>">
                    <span class="description">( _, латиница, цифры; мин. 5 - макс. 20 символов)</span>
                </label>
            </p>
            <p>
                <label>
                    Придумай пароль: <input type="password" maxlength="30" name="password" value="<?=$raw_password??'';?>">
                    <span class="description">( _, латиница, цифры; мин. 10 - макс. 30 символов)</span>
                </label>
            </p>
            <p>
                <label>
                Выбери пол:
                <input id="sex_male" type="radio" name="sex" value="male" <?=($sex==='male' || empty($raw_sex))?'checked':'' ?>> <label for="sex_male">мужской</label>
                <input id="female" type="radio" name="sex" value="female" <?=                    ($sex==='female')?'checked':'' ?>> <label for="female">женский</label>
                </label>
            </p>
            <p>
                <label>
                    Введи email:
                    <input type="text" maxlength="50" name="email" value="<?=$raw_email??''?>">
                    <span class="description">(макс. 50 символов)</span>
                </label>
            </p>
            <p>
                <label>
                    Год рождения:
                    <input type="text" maxlength="10" name="birth_year" value="<?=$raw_birth_year??''?>">
                    <span class="description">(1920 - <?=$today_year?>)</span>
                </label>
            </p>

            <p>
                <label>
                О себе: <span class="description">(не более 200 символов)</span><br>
                <textarea name="about_yourself" rows="4" cols="50" wrap="hard"><?=$raw_about_yourself??''?></textarea>
                </label>
            </p>
            <p>
                <span class="error"><?=$validation_error??''?></span>
            </p>
            <p>
                <input type="submit" value="Отправить">
            </p>
        </form>


<?php
//блок предпросмотра введённой информации
//появляется только после отправки формы
//сюда попадают только те данные, которые прошли валидацию
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "<hr><h2>Предпросмотр:</h2>";
    if (!empty($name)) echo "<p><b>Имя:</b> <span class=\"accepted\">$name</span></p>";
    if (!empty($password)) echo "<p><b>Пароль:</b> <span class=\"accepted\">OK</span></p>";
    if (!empty($sex)) echo "<p><b>Пол:</b> <span class=\"accepted\">", ($sex==="male")?'мужской':'женский', "</span></p>";
    if (!empty($email)) echo "<p><b>Email:</b> <span class=\"accepted\">$email</span></p>";
    if (!empty($birth_year)) echo "<p><b>Возраст:</b> <span class=\"accepted\">", $today_year-$birth_year, "</span></p>";
    if (!empty($about_yourself)) echo "<p><b>О себе:</b> <span class=\"accepted\">$about_yourself</span></p>";

    if (!empty($name) && !empty($sex) && !empty($email) && !empty($birth_year) && !empty($about_yourself) && !empty($password)) {
        echo '<hr><p><b><span class="accepted">Поздравляю, ты верно всё заполнил!</b></p>';
    }


    foreach ($validated_post_db as $key => $value) {echo '<p>'.$key.'<span class="accepted">=> '.$value.'</span></p>';}
    echo "<hr>";
    foreach ($validated_post as $key => $value) {echo '<p>'.$key.'<span class="accepted">=> '.$value.'</span></p>';}
    echo "<hr><pre>", print_r($validated_post) ,"</pre>";
    //var_dump($correct_post);
    //echo "<hr><pre>", print_r($_REQUEST) ,"</pre>";
    //echo "<hr><pre>", print_r($_FILES) ,"</pre>";
    //echo mb_internal_encoding();
    echo "<hr><pre>", print_r(postReaper($inputs_properties)) ,"</pre>";
    //echo $inputs['name']['min_length'];
}

?>

    </body>
</html>
