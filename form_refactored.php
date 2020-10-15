<?php

//подключаем конфиг и все необходимые файлы
require __DIR__ . '/config/config.php';

//стартуем сессию
session_start();

//сбрасываем данные сессии, если пользователь того захотел
isset($_GET['reset']) ? session_unset() : '';

//проверяем, отправлена ли форма кнопкой 'Отправить'
if ($_POST["button"]??'') {
    //извлекаем и дезинфицируем $_POST
    $sanitized_post = postReaper($inputs_properties);
    //сверяем с базой данных
    $validated_post_db = dbCheck($sanitized_post, $inputs_properties,$validation_error, $error_msg, $db_check);
    //валидируем данные
    $validated_post_filter = validator($sanitized_post, $inputs_properties, $error_msg, $validated_post_db, $validation_error);
    //обрабатываем загрузку файла
    fileHandler($upload_file, $error_msg, $file_correct, $validation_error);
}

//запрещаем кешировать страницу и устанавливаем кодировку
header("Cache-Control: no-cache");
header("Content-Type: text/html; charset=utf-8");

//выводим верх HTML-страницы
htmlRender('/templates/header.html');

//выводим HTML-форму
htmlRender('/templates/form.html', "", @$sanitized_post, @$validation_error);

//выводим ошибки, если есть
($validation_error??'') ? htmlRender('/templates/errors.html',"", "", $validation_error):'';

//блок предпросмотра введённой информации
//появляется только после отправки формы
//сюда попадают только те данные, которые прошли валидацию
if ($_POST["button"]??'') {
    //рассчитываем правильные ответы
    foreach ($inputs_properties as $key => $value) {
        if ($inputs_properties[$key]["validation_type"] === "db") {
            if (isset($validated_post_db[$key])) $correct_post[$key] = $validated_post_db[$key];
        }
        if ($inputs_properties[$key]["validation_type"] === "filter") {
            if (isset($validated_post_filter[$key])) $correct_post[$key] = $validated_post_filter[$key];
        }
        if ($inputs_properties[$key]["validation_type"] === "db_filter") {
            if (isset($validated_post_db[$key]) && isset($validated_post_filter[$key])) $correct_post[$key] = $validated_post_db[$key];
        }
    }

    //выводим предпросмотр ответов
    $preview = htmlRender('/templates/preview.html', @$correct_post, $sanitized_post, $validation_error, $file_correct, "return");
    echo $preview;

    //проверяем, ВСЕ ли данные введены корректно
    if ((@count($correct_post) === count($inputs_properties)) && $file_correct === TRUE) {
        //выводим сообщение об успешном заполнении ВСЕХ данных
        htmlRender('/templates/success.html');
        //и отправляем заполненную форму на указаный email
        phpMailer($preview, $correct_post["email"]);
    }
}

//выводим низ страницы
htmlRender('/templates/footer.html');

