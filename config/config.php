<?php

require __DIR__ . '/../config/error_msg.php';
require __DIR__ . '/../functions.php';

//подключаем PHPMailer
require __DIR__ . '/../../PHPMailer/src/Exception.php';
require __DIR__ . '/../../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../../PHPMailer/src/SMTP.php';

//конфиг загрузки файла
$upload_dir = __DIR__ . '/../upload/';
//$upload_file = $upload_dir . basename(@$_FILES['profile_pic']['name']);
$upload_file = $upload_dir . 'Photo.jpg';

//список доступных input с формы
//"название_параметра"      =>  ["его свойства"]
$inputs_properties = [
           "name"           =>  ["min_length" => 5, "max_length" => 20, "filter" => FILTER_VALIDATE_REGEXP, "filter_var_options" => ["options" => ["regexp" => "/^\w{5,20}$/"] ], "db_check_reverse" => true, "validation_type" => "db_filter"],
           "password"       =>  ["min_length" => 10, "max_length" => 30, "filter" => FILTER_VALIDATE_REGEXP, "filter_var_options" => ["options" => ["regexp" => "/^\w{10,30}$/"] ], "validation_type" => "filter" ],
           "sex"            =>  ["html_hook" => "sex_input", "db_check" => true, "validation_type" => "db"],
           "birth_year"     =>  ["max_length" => 4, "filter" => FILTER_VALIDATE_INT, "filter_var_options" => ["options" => ["min_range" => 1920, "max_range" => 2020]], "validation_type" => "filter"],
           "email"          =>  ["max_length" => 50, "filter" => FILTER_VALIDATE_EMAIL, "db_check_reverse" => true, "validation_type" => "db_filter"],
           "about_yourself" =>  ["min_length" => 10, "max_length" => 200, "filter" => FILTER_VALIDATE_REGEXP, "filter_var_options" => ["options" => ["regexp" => "/^(\s|\w){10,200}$/"]], "validation_type" => "filter"],
           "send_email"     =>  ["db_check" => true, "validation_type" => "db"]
];


//сверка с базой данных
$db_check = [
           "name"           => ["admin", "superuser", "guest", "animal"],
           "sex"            => ["male", "female"],
           "email"          => ["admin@mail.com", "superuser@mail.com", "guest@mail.com", "animal@mail.com"],
           "send_email"     => ["yes"]
];



