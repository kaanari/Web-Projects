<?php

    function db_connect(){
        $servername = "localhost";
        $usrname = "root";
        $passwd = "";
        $dbname="hunrobotx";
        $conn = mysqli_connect($servername, $usrname, $passwd,$dbname);
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }else{
            return $conn;
        }
    }

    function cookie_control(){
        if(isset($_COOKIE["check"])&&isset($_COOKIE["id"])){
            if($_COOKIE["check"]=="step1"){
                return True;
            }else if($_COOKIE["check"]=="step2"){
                header("Location: ./step3.php");
                die();
            }else if($_COOKIE["check"]=="step3"){
                header("Location: ./step4.php");
                die();
            }else if($_COOKIE["check"]=="finished"){
                header("Location: ./error.php");
                die();
            }else{
                header("Location: ./index.php");
                die();
            }
        }else{
            header("Location: ./index.php");
            die();
        }
    }

    function name(){
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT name FROM competitor WHERE id=?");
        $stmt->bind_param("i",$_COOKIE["id"]);
        $stmt->execute();
        $query = $stmt->get_result();
        $result=$query->fetch_assoc();
        echo($result["name"]);
    }

    function start(){
        header("Location: step2-2.php");
        die();
    }

    if(cookie_control()){
        if(isset($_POST["sended"])){
            start();
        }
    }



?>
<html>
    <head>
        <meta charset="utf-8">
        <title>Hacettepe Robot Topluluğu - WORKSHOP - [ Şehit Mutlucan Kılıç Ortaokulu ]</title>
        <link rel="stylesheet" href="./assest/styles/main.css">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="./assest/img/hunrobotx.png" sizes="32x32">
    </head>
    <body>
            <div class="nav">
            <center>
                <ul>
                    <li><a class="active" href="./index.php"><b>HUNROBOTX</b></a></li>
                </ul>
            </center>
            </div>
            <div class="content">
                <img class="logo" src="./assest/img/hunrobotx.png">
                <center>
                    <h4>Merhaba, <span style="color:darkred;"><?php name();?></span></h4>
                    <h4>Yarışmaya başlamak için <span style="color:rgb(28, 114, 196);">BAŞLA</span> butonuna basmalısın.</h4>
                    <h5 style="color:darkred;margin-bottom:5px;">KURALLAR : </h5>
                </center>
                    <h4 style="margin:auto;width:70%;margin-left:20%;">1) Her sorunun süresi zorluk seviyesine göre değişmektedir.<br>2) Sorular 4 şıktan oluşmaktadır.<br>3) Soruları erken çözmek daha fazla puan kazandırır.<br></h4>

                <center>
                    <form action="" method="POST">
                        <input type="submit" class="btn" value="BAŞLA" name="sended">
                    </form>
                </center>
            </div>
            <footer>
            <center>
                <ul>
                    <li><a class="active" href="./index.php"><b>HUNROBOTX</b></a></li>
                </ul>
            </center>
            </footer>
    </body>
</html>