<?php

//список доступных input с формы
//"название_параметра"      =>  ["его свойства"]
$inputs_properties = [
           "name"           =>  ["min_length" => 5, "max_length" => 20, "filter" => FILTER_VALIDATE_REGEXP, "filter_var_options" => ["options" => ["regexp" => "/^\w{5,20}$/"] ], "db_check_reverse" => TRUE],
           "password"       =>  ["min_length" => 10, "max_length" => 30, "filter" => FILTER_VALIDATE_REGEXP, "filter_var_options" => ["options" => ["regexp" => "/^\w{10,30}$/"] ] ],
           "sex"            =>  ["variants" => ["male", "female"], "html_hook" => "sex_input", "db_check" => TRUE],
           "birth_year"     =>  ["max_length" => 4, "filter" => FILTER_VALIDATE_INT, "filter_var_options" => ["options" => ["min_range" => 1920, "max_range" => 2020]]],
           "email"          =>  ["max_length" => 50, "filter" => FILTER_VALIDATE_EMAIL, "db_check_reverse" => TRUE],
           "about_yourself" =>  ["min_length" => 10, "max_length" => 200, "filter" => FILTER_VALIDATE_REGEXP, "filter_var_options" => ["options" => ["regexp" => "/^(\s|\w){10,200}$/"] ] ],
];


//сверка с базой данных
$db_check = [
           "name"           => ["admin", "superuser", "guest", "animal"],
           "sex"            => ["male", "female"],
           "email"          => ["admin@mail.com", "superuser@mail.com", "guest@mail.com", "animal@mail.com"]
];



