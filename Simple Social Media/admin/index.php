<?php
    session_start();
    function random_key($str_length = 24)
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $bytes = openssl_random_pseudo_bytes(3*$str_length/4+1);
        $repl = unpack('C2', $bytes);
        $first  = $chars[$repl[1]%62];
        $second = $chars[$repl[2]%62];    
        return strtr(substr(base64_encode($bytes), 0, $str_length), '+/', "$first$second");
    }

    function cookie_set($username)
    {
        session_start();
        $cookie=random_key();
        $_COOKIE["auth"]=$cookie;
        $_SESSION["auth"] = $cookie;
        $_SESSION["username"]=$username;
        setcookie("auth","$cookie", 0);    
    }
    function cookie_set2($username)
    {
        session_start();
        $cookie=random_key();
        $_COOKIE["auth"]=$cookie;
        $_SESSION["auth"] = $cookie;
        $_SESSION["username"]=$username;
        setcookie("auth","$cookie", time()+86400*30);    
    }


    function valid_pass($pwd) 
    {
        $r1='/[A-Z]/';  //Uppercase
        $r2='/[a-z]/';  //lowercase
        $r3='/[!@#$%^&()\-_=+{};:,<.>"|]/';  // whatever you mean by 'special char'
        $r4='/[0-9]/';  //numbers
        if(preg_match_all($r1,$pwd, $o)<1) return FALSE;
        if(preg_match_all($r2,$pwd, $o)<1) return FALSE;
        if(preg_match_all($r3,$pwd, $o)>0) return FALSE;
        if(preg_match_all($r4,$pwd, $o)<1) return FALSE;
        if(strlen($pwd)<8) return FALSE;
        return TRUE;
    }

    function valid_username($name)
    {
        $name = preg_replace ("/ +/", "", $name); # convert all multispaces to space
        $r3='/[!@#$%^&*()\-_=+{};:,<.>ıüğşçö]/';  // whatever you mean by 'special char'
        if(preg_match_all($r3,$name, $o)>0) return FALSE;
        if(strlen($name)<5) return FALSE;
        return TRUE;
    }
    function db_connect(){
        $servername = "localhost";
        $usrname = "root";
        $passwd = "";
        $dbname="deneme";
        $conn = mysqli_connect($servername, $usrname, $passwd,$dbname);
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }else{
            return $conn;
        }
    }

    function login(){
        global $usernamestatus,$passwordstatus;
        $username=$_POST["username"];
        $password=$_POST["pwd"];
        if(!(empty($username))){
            if(!(empty($password))){
                if(valid_username($username)){
                    if(valid_pass($password)){
                        $conn=db_connect();
                        $stmt=$conn->prepare("SELECT id,pwd,admin FROM users WHERE username=?");
                        $stmt->bind_param("s",$username);
                        $stmt->execute();
                        $query = $stmt->get_result();
                        $result=$query->fetch_assoc();
                        if($result["admin"]==1){
                            if(empty($result))
                            {
                                $passwordstatus="Password is not correct.";
                            }else{
                            if(password_verify($password,$result["pwd"]))
                            {
                                if(isset($_POST["loginin"]))
                                {
                                    cookie_set2($username);
                                    header("Location: ./panel.php");
                                    die();
                                }else{
                                    cookie_set($username);
                                    header("Location: ./panel.php");
                                    die();
                                }

                            }else
                            {
                                $passwordstatus="Password is not correct.";
                            }
                        }
                        }
                    }else{
                        $passwordstatus="Password is not valid.";
                    }
                }else {
                    $usernamestatus="Username is not valid.";
                }

            }else{
                $passwordstatus="Password can not be empty.";
            }
        }else{
            $usernamestatus="Username can not be empty.";
        }

    }
    function cookie_control()
    {
        if(!(empty($_COOKIE["auth"]))&&!(empty($_SESSION))){
            $cookie=$_COOKIE["auth"];
                if($_SESSION["auth"] == $cookie){
                    $conn=db_connect();
                    $stmt=$conn->prepare("SELECT admin FROM users WHERE username=?");
                    $stmt->bind_param("s",$_SESSION["username"]);
                    $stmt->execute();
                    $query = $stmt->get_result();
                    $result2=$query->fetch_assoc();
                    if($result2["admin"]==1){
                        return True;
                    }else{
                        return False;
                    }
                }else{
                    setcookie("auth", "", time() - 3600);
                    session_destroy();
                    return False;
                }
        }else{
            return False;
        } 
    }

    $usernamestatus="Enter Username";
    $passwordstatus="Enter Password";
    if($_POST){
        login();
    }else{
        $cookie=cookie_control();
        if($cookie==True){
            $username=$_SESSION["username"];
            $conn=db_connect();
            header("Location: ./panel.php");
            die();
        }
    }
?>
<html>
    <head>
        <meta charset="utf-8">
        <title>Login - Kaan ARI</title>
        <link rel="stylesheet" href="./assest/styles/styles_login.css">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    
    <body>
        <div class="wrapper">
            <div class="navbar">
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a class="active" href="./login.php" style="float:right;">Login</a></li>
                </ul>
            </div>

            <div class="content">
                <div class="loginform">
                    <form style="align-item:center;" action="./index.php" method="POST">
                        <label><b>Username</b></label>
                        <input class="inp" <?php echo("placeholder='$usernamestatus'"); ?> type="text" name="username">
                        <label><b>Password</b></label>
                        <input class="inp" <?php echo("placeholder='$passwordstatus'"); ?> type="password" name="pwd">
                        <br>                        
                        <input class="loginbtn" type="submit" value="LOGIN">
                    </form>
                </div>
            </div>
            <footer>
                <div  class="footer">
                    <center>
                        <ul>
                            <li><a id="in1" href="http://wwww.facebook.com/kaan.ari.tr"><img style="height:30px; width:30px;" src="./assest/img/face5.png"/></a></li>
                            <li><a id="in2" href="https://twitter.com/kaanaritr"><img style="height:30px; width:30px;" src="./assest/img/twitter6.png"/></a></li>
                            <li><a id="in3" href="https://www.tumblr.com/login?redirect_to=%2Fblog%2Fengineerofhctp"><img style="height:30px; width:30px;" src="./assest/img/tumblr5.png"/></a></li>
                        </ul>
                    </center>
                </div>
            </footer>
        </div>
    </body>
</html>