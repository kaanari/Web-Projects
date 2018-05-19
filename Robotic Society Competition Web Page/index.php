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
        if(isset($_COOKIE["check"]) && isset($_COOKIE["id"])){
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
                header("Location: ./error.php");
                die();
            }else{
                setcookie("check","",time()-3600);
                setcookie("id","",time()-3600);
            }
        }
    }

    function cookie_set($id){
        setcookie("check", "step1", time() + 3600);
        setcookie("id","$id", time()+3600);
    }

    function control(){
        global $status;
        if(!(empty($_POST["name"]))){
            $name=$_POST["name"];
            $q1_id=0;
            $q1_ans=0;
            $q1_time=0;
            $q1_point=0;
            $q2_id=0;
            $q2_ans=0;
            $q2_time=0;
            $q2_point=0;
            $q3_id=0;
            $q3_ans=0;
            $q3_time=0;
            $q3_point=0;
            $total_point=0;
            $conn=db_connect();
            $stmt = $conn->prepare("INSERT INTO competitor (name, q1_id, q1_ans, q1_time, q1_point, q2_id, q2_ans, q2_time, q2_point, q3_id, q3_ans, q3_time, q3_point, total_point) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sisiiisiiisiii", $name,$q1_id,$q1_ans,$q1_time,$q1_point,$q2_id,$q2_ans,$q2_time,$q2_point,$q3_id,$q3_ans,$q3_time,$q3_point,$total_point);
            $stmt->execute();
            $id=$conn->insert_id;
            cookie_set($id);
            header("Location: ./step2.php");
        }else{
            $status="LÜTFEN İSİM GİRİNİZ";
        }
    }

    cookie_control();
    $status="DEVAM ET";
    if(isset($_POST["sended"])){
        control();
    }
?>
<html>
    <head>
        <meta charset="utf-8">
        <title>Hacettepe Robot Topluluğu - WORKSHOP - [ Şehit Mutlu Can Kılıç Ortaokulu ]</title>
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
                    <h3 style="color:darkred;">Hacettepe Üniversitesi Robot Topluluğu</h3>
                    <h4>Şehit Mutlu Can Kılıç Ortaokulu</h4>
                    <h4>Bilgi Yarışması</h4>
                    <form action="" method="POST">
                        <input type="text" class="inp1" placeholder="İsminizi Giriniz" name="name"><br>
                        <input type="submit" class="btn" value="<?php echo($status);?>" name="sended">
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