<?php

//неймсмпейс для использования PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
        //записываем дезинфицированные данные в сессию
        $_SESSION[$key] = $sanitized_post[$key];
    }
    return $sanitized_post;
}

//загрузка файла
function fileHandler($upload_file, $error_msg, &$file_correct, &$validation_error) {
    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_file)) {
        $file_correct = TRUE;
    } else {
        $validation_error .= $error_msg["file"]["file_handler"];
    }
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
function htmlRender($html_file, $correct_post="", $sanitized_post="", $validation_error="", $file_correct="", $call_type=""){
    ob_start();
    require __DIR__ . $html_file;
    $template = ob_get_contents();
    ob_end_clean();
    if ($call_type === "return") {
        return $template;
    } else {
        echo $template;
    }
}

//функция отправки формы на email
function phpMailer($form, $email) {
    // Instantiation and passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = '2005test2005test';                     // SMTP username
        $mail->Password   = 'password';                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = 465;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        //Recipients
        $mail->setFrom('2005test2005test@gmail.com', 'Mailer');
        //$mail->addAddress('ellen@example.com', 'Tony');     // Add a recipient
        $mail->addAddress($email);               // Name is optional
        $mail->addReplyTo('2005test2005test@gmail.com', 'Information');
        //$mail->addCC('cc@example.com');
        //$mail->addBCC('bcc@example.com');

        // Attachments
        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        $mail->addAttachment(__DIR__.'/upload/Photo.jpg', 'Photo.jpg');    // Optional name

        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'You have successfully registered!';
        //$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
        $mail->Body    = $form;
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        echo 'Письмо было успешно отправлено!';
    } catch (Exception $e) {
        echo "Письмо не отправлено. Ошибка отправки: {$mail->ErrorInfo}";
    }
}

//генератор пробелов ф html-форме
function spaceGen($num) {
    $space = '';
    for ($i = 0; $i < $num; $i++) {
        $space .= '&nbsp;';
    }
    return $space;
}

