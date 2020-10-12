<?php

//функция извлечения и дезинфекции $_POST
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

//сверка с базой данной
function dbCheck($sanitized_post, $inputs_properties, &$validation_error, $error_msg, $db_check) {
    $ip = $inputs_properties;
    $sp = $sanitized_post;
    foreach ($sp as $key => $value) {
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
    return @$validated_post;
}

//функция вывода HTML-шаблона
function html_render($html_file, $correct_post="", $sanitized_post="", $validation_error=""){
    ob_start();
    require(dirname(__FILE__) . $html_file);
    $template = ob_get_contents();
    ob_end_clean();
    echo $template;
}

?>