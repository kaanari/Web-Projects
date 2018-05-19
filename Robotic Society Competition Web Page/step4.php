<?php

    function db_connect(){
        $servername = "localhost";
        $usrname = "root";
        $passwd = "";
        $dbname="hunrobotx";
        $conn = mysqli_connect($servername, $usrname, $passwd,$dbname);
        mysqli_set_charset($conn,"utf8");
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }else{
            return $conn;
        }
    }

    function get_soru(){
        global $result;
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT * FROM sorular WHERE hard=3 ORDER BY RAND() LIMIT 1");
        $stmt->execute();
        $query = $stmt->get_result();
        $result=$query->fetch_assoc();
    }

    function cookie_control(){
        if(isset($_COOKIE["check"])&&isset($_COOKIE["id"])){
            if($_COOKIE["check"]=="step3"){
                return True;
            }else if($_COOKIE["check"]=="step2"){
                header("Location: ./step3.php");
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

    function getpoint($time){
        global $result;
        if($_POST["q_ans"]==$_POST["sended"]){
            $point=$time*30;
            return $point;
            echo($point);
        }else{
            return 0;
        }
    }

    function answer($time){
        global $result;
        $point=getpoint(30-$time);
        $conn=db_connect();
        $stmt = $conn->prepare("UPDATE competitor SET q3_id=?, q3_ans=?, q3_time=?, q3_point=? WHERE id=?");
        $stmt->bind_param("isiii", $_POST["q_id"],$_POST["sended"],$time,$point,$_COOKIE["id"]);
        $stmt->execute();
    }

    if(cookie_control()){
        if($_POST){
            $datetime2=date("h:i:sa");
            $datetime2= new DateTime($datetime2); 
            $datetime1=$_POST["time"];
            $datetime1= new DateTime($datetime1); 
            $interval = $datetime1->diff($datetime2);
            $time=$interval->s;
            answer($time);
            setcookie("check","finished",time()+3600);

            header("Location: ./error.php");
            die();
        }
    }
    get_soru();

    


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
<script type="text/javascript">
    var timeleft = 30;
    var downloadTimer = setInterval(function(){
    timeleft--;
    document.getElementById("countdowntimer").textContent = timeleft;
    if(timeleft <= 0){
        clearInterval(timeleft);
        document.cookie = "check=finished";
        window.location = './error.php';    
    }
    },1000);
</script>


           <div class="nav">
            <center>
                <ul>
                    <li><a class="active" href="./index.php"><b>HUNROBOTX</b></a></li>
                </ul>
            </center>
            </div>
            <center>
            <div style="color:darkred;font-size:50px;margin-top:20px;margin-bottom:0">
                <?php $abc="<script>document.write(timeleft)</script>"; ?><b><span id="countdowntimer">30 </span></b>

        
            </div></center>
            <div class="content">
                <img class="logo" src="./assest/img/hunrobotx.png">
                <center>
                    <h3><span style="color:darkred;">Soru 3</span></h3>
                    <h4><?php echo($result["soru"]); ?></h4>
                </center>
                <form action="" method="POST">

                    <h4 style="margin-left:15%;">                  
                    <input type="submit" class="cevap" value="A" name="sended"><input type="submit" class="cevap2" value="<?php echo($result["a"]); ?>" name="sended2"><br>
                    <input type="submit" class="cevap" value="B" name="sended"><input type="submit" class="cevap2" value="<?php echo($result["b"]); ?>" name="sended2"><br>
                    <input type="submit" class="cevap" value="C" name="sended"><input type="submit" class="cevap2" value="<?php echo($result["c"]); ?>" name="sended2"><br>
                    <input type="submit" class="cevap" value="D" name="sended"><input type="submit" class="cevap2" value="<?php echo($result["d"]); ?>" name="sended2"><br>
                    <input type="hidden" name="time" value="<?php echo date("h:i:sa"); ?>">
                    <input type="hidden" name="q_id" value="<?php echo($result["id"]); ?>">
                    <input type="hidden" name="q_ans" value="<?php echo($result["ans"]); ?>">
                    </h4>
                </form>

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