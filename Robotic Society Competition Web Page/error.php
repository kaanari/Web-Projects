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
                header("Location: ./step2.php");
                die();
            }else if($_COOKIE["check"]=="step2"){
                header("Location: ./step3.php");
                die();
            }else if($_COOKIE["check"]=="step3"){
                header("Location: ./step4.php");
                die();
            }else if($_COOKIE["check"]=="finished"){
                return True;
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
        global $result;
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT * FROM competitor WHERE id=?");
        $stmt->bind_param("i",$_COOKIE["id"]);
        $stmt->execute();
        $query = $stmt->get_result();
        $result=$query->fetch_assoc();
        return $result["name"];
    }

    function startagain(){
        setcookie("auth","",time()-3600);
        setcookie("id","",time()-3600);
        header("Location: ./index.php");
        die();
    }
    function total_point(){
        global $result;
        $conn=db_connect();
        $total=$result["q1_point"]+$result["q2_point"]+$result["q3_point"];
        $stmt = $conn->prepare("UPDATE competitor SET total_point=? WHERE id=?");
        $stmt->bind_param("ii", $total,$_COOKIE["id"]);
        $stmt->execute();
    }


    if(cookie_control()){
        name();
        total_point();
        if(isset($_POST["pass"])){
            if($_POST["pass"]=="hunrobotx"){
                startagain();
            }
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
                    <h4><span style="color:darkred;"><?php echo(name());?></span>, yarışmanız bitmiştir.</h4>
                    <h4>Sonuçlar <span style="color:darkred;">10 DAKİKA</span> içinde açıklanacaktır.</h4>
                </center>
                <center>
                    <form action="" method="POST">
                        <input type="password" class="inp1" name="pass"><br>
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