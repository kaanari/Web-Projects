<?php


    function cookie_set()
    {
        session_start();
        session_unset();
        session_destroy();
        setcookie("auth","", time()-3600);
        header("Location: ./index.php");                                                                
        die();  
    }

    cookie_set();
?>
