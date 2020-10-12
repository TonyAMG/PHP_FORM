<?php

require_once (__DIR__ . '/config/config.php');
require_once (__DIR__ . '/config/error_msg.php');
require_once (__DIR__ . '/functions.php');

if (@$_POST["button"]) {
    //извлекаем и дезинфицируем $_POST
    $sanitized_post = postReaper($inputs_properties);
    //сверяем с базой данных
    $validated_post_db = dbCheck($sanitized_post, $inputs_properties,$validation_error, $error_msg, $db_check);
    //валидируем данные
    $validated_post = validator($sanitized_post, $inputs_properties, $error_msg, $validated_post_db, $validation_error);
}

//запрещаем кешировать страницу и устанавливаем кодировку
header("Cache-Control: no-cache");
header("Content-Type: text/html; charset=utf-8");

//выводим HTML-форму
html_render('/templates/registration_form.html', "", @$sanitized_post, @$validation_error);

//блок предпросмотра введённой информации
//появляется только после отправки формы
//сюда попадают только те данные, которые прошли валидацию
if (@$_POST["button"]) {
    //рассчитываем правильные ответы
    if (isset($validated_post_db["name"]) && isset($validated_post["name"]) ) $correct_post["name"] = $validated_post_db["name"];
    if (isset($validated_post_db["email"]) && isset($validated_post["email"]) ) $correct_post["email"] = $validated_post_db["email"];
    if (isset($validated_post_db["sex"]))  $correct_post["sex"] = $validated_post_db["sex"];
    if (isset($validated_post["password"])) $correct_post["password"] = $validated_post["password"];
    if (isset($validated_post["birth_year"])) $correct_post["birth_year"] = $validated_post["birth_year"];
    if (isset($validated_post["about_yourself"])) $correct_post["about_yourself"] = $validated_post["about_yourself"];
    //выводим концовку HTML-страницы
    html_render('/templates/preview.html', $correct_post, $sanitized_post, $validation_error);

    if (count($correct_post) === count($inputs_properties)) {
        //выводим сообщение об успешном заполнении ВСЕХ данных
        html_render('/templates/success.html');
    }
}
//выводим низ страницы
html_render('/templates/footer.html');

