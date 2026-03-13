<?php

    $conn = mysqli_init();

    //$conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
    $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
    $conn->ssl_set(NULL, NULL, 'root.crt', NULL, NULL);

    $conn->real_connect('rc1c-t7m21ff6nc878a8f.mdb.yandexcloud.net', 'mv_pravza_simpla', 'MVc5tS0699', 'pravza_simpla', 3306, NULL, MYSQLI_CLIENT_SSL);



    $q = $conn->query('SELECT version()');
    $result = $q->fetch_row();
    echo($result[0]);

    $q->close();
    $conn->close();


    $conn = mysqli_init();

    $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
    $conn->real_connect('rc1c-t7m21ff6nc878a8f.mdb.yandexcloud.net', 'mv_pravza_simpla', 'MVc5tS0699', 'pravza_simpla', 3306, NULL, NULL);

    $q = $conn->query('SELECT version()');
    $result = $q->fetch_row();
    echo($result[0]);

    $q->close();
    $conn->close();


    //mysql -h rc1c-t7m21ff6nc878a8f.mdb.yandexcloud.net \
    //  --user=mv_pravza_simpla \
    //  --password \
    //  --port=3306 \
    //  --line-numbers pravza_simpla  \
    //  < _live.pravza_simpla.2179744.sql