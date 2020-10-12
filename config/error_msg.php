<?php

$error_msg = ["name"             =>   ["db_check"  => "*Пользователь с таким именем уже есть. <br>",
                                       "validator" => "*Имя должно состоять из _, латиницы или цифр (от 5 до 20 символов). <br>"],
              "password"         =>   ["validator" => "*Пароль должен состоять из латинских букв, цифр (от 10 до 30 символов). <br>"],
              "sex"              =>   ["db_check"  => "*Неверно указан пол. <br>"],
              "birth_year"       =>   ["validator" => "*Неверно указан год рождения. <br>"],
              "email"            =>   ["db_check"  => "*Пользователь с таким email уже зарегистрирован. <br>",
                                       "validator" => "*Неверно указан email. <br>"],
              "about_yourself"   =>   ["validator" => "*Строка \"О себе\" должна состоять из латинских букв, цифр, допускаются пробелы (от 10 до 200 символов). <br>"]
];


//пользователь с таким email уже зареган