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

    function cookie_set($name)
    {
        $cookie=random_key();
        $_COOKIE["auth"]=$cookie;
        $_SESSION["auth"] = $cookie;
        $_SESSION["username"]=$name;
        setcookie("auth","$cookie", 0);    
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
        $name = preg_replace ("/ +/", "", $name);
        $r3='/[!@#$%^&*()\-_=+{};:,<.>ıüğşçö]/';
        if(preg_match_all($r3,$name, $o)>0) return FALSE;
        if(strlen($name)<5) return FALSE;
        return TRUE;
    }
    function valid_name($name)
    {
        $r3='/[!@#$%^&*()\-_=+{};:,<.>]/';
        if(preg_match_all($r3,$name, $o)>0) return FALSE;
        if(strlen($name)<1) return FALSE;
        return TRUE;
    }
    function valid_surname($name)
    {
        $name = preg_replace ("/ +/", "", $name);
        $r3='/[!@#$%^&*()\-_=+{};:,<.>]/';
        if(preg_match_all($r3,$name, $o)>0) return FALSE;
        if(strlen($name)<1) return FALSE;
        return TRUE;
    }
    function db_connect(){
        $servername = "localhost";
        $usrname = "root";
        $passwd = "";
        $dbname="deneme";
        $conn = new mysqli ($servername, $usrname, $passwd,$dbname);
        if(! empty( $mysqli->error ) ){
            echo $mysqli->error;  // <- this is not a function call error()
         }
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }else{
            return $conn;
        }
    }

    function image(){
        global $id;
        global $ppimg;
        if ($_FILES['ppimg']['size'] != 0 && $_FILES['ppimg']['error'] == 0){
            $tmp_name=$_FILES['ppimg']["tmp_name"];
            $info = getimagesize($tmp_name);
            $extension = image_type_to_extension($info[2]);;
            $name=$id.$extension;
            $uploads_dir="./usrimg";
            if($info != false) {
                if(move_uploaded_file($tmp_name,"$uploads_dir/$name")){
                    $ppimg="$uploads_dir/$name";
                    return True;
                }else{
                    return False;
                }
            } else {
                return False;
            }
        }
    }
    
    function cookie_control()
    {
        if(!(empty($_COOKIE["auth"]))){
            $cookie=$_COOKIE["auth"];
            if($_SESSION["auth"] == $cookie){
                return True;
            }else{
                setcookie("auth", "", time() + 3600);
                session_destroy();
                return False;
            }
        }else{
            return False;
        }
    }
    function signin(){
        global $id,$usernamestatus,$passwordstatus,$repasswordstatus,$mailstatus,$namestatus,$surnamestatus,$bdatestatus,$genderstatus,$ppimg;
        $username=$_POST["username"];
        $password=$_POST["pwd"];
        $repassword=$_POST["repwd"];
        $mail=$_POST["mail"];
        $name=$_POST["name"];
        $surname=$_POST["surname"];
        $gender=$_POST["gender"];
        $bdate=$_POST["bdate"];
        $usrtel=$_POST["usrtel"];
        $country=$_POST["country"];

        if(!(empty($username))){
            if(!(empty($password))){
                if(!(empty($repassword))){
                    if(!(empty($mail))){
                        if(!(empty($name))){
                            if(!(empty($surname))){
                                if(!(empty($bdate))){
                                    if(!(empty($gender))){
                                        if(valid_username($username)){
                                            if(valid_pass($password) && valid_pass($repassword)){
                                                if($password==$repassword){
                                                    if (filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                                                        if(valid_name($name)){
                                                            if(valid_surname($surname)){
                                                                $conn=db_connect();
                                                                $stmt=$conn->prepare("SELECT id FROM users WHERE email=?");
                                                                $stmt->bind_param("s",$mail);
                                                                $stmt->execute();
                                                                $query = $stmt->get_result();
                                                                $result1=$query->fetch_assoc();
                                                                $conn=db_connect();
                                                                $stmt=$conn->prepare("SELECT id FROM users WHERE username=?");
                                                                $stmt->bind_param("s",$username);
                                                                $stmt->execute();
                                                                $query = $stmt->get_result();
                                                                $result2=$query->fetch_assoc();
                                                                if(empty($result1)){
                                                                    if(empty($result2)){
                                                                        $conn=db_connect();
                                                                        $hashed_pwd=password_hash($password, PASSWORD_DEFAULT);
                                                                        $stmt = $conn->prepare("INSERT INTO users (username, pwd, email, usr_name, usr_surname, gender, bdate, usr_phone, country) VALUES(?,?,?,?,?,?,?,?,?)");
                                                                        $stmt->bind_param("sssssssss", $username,$hashed_pwd,$mail,$name,$surname,$gender,$bdate,$usrtel,$country);
                                                                        if ($stmt->execute()) {
                                                                            $id=$conn->insert_id;
                                                                            if(image()){
                                                                                $newppimg=$ppimg;
                                                                            }else{
                                                                                $newppimg="./assest/img/profile_default.png";
                                                                            }
                                                                            $stmt = $conn->prepare("UPDATE users SET pimg=? WHERE id=?");
                                                                            $stmt->bind_param("si",$newppimg,$id);
                                                                            $stmt->execute();
                                                                        } else {
                                                                            echo "Error: ".$stmt->error;
                                                                            die();
                                                                        }
                                                                        cookie_set($username);
                                                                        header("Location: ./index.php");
                                                                        die();
                
                                                                    }else{
                                                                        $usernamestatus="Username already exist.";
                                                                    }
                                                                }else{
                                                                    $mailstatus="E-mail already exist.";
                                                                }
                                                            }else{
                                                                $surnamestatus="Surname is not valid.";
                                                            }
                                                        }else{
                                                            $namestatus="Name is not valid.";
                                                        }
                                                    }else{
                                                        $mailstatus="E-mail is not valid.";
                                                    }
                                                }else{
                                                    $passwordstatus="Different from Retype Password.";
                                                    $repasswordstatus="Different from Password."; 
                                                }
                                            }else{
                                                $passwordstatus="Password is not valid.";
                                                $repasswordstatus="Password is not valid.";
                                            }
                                        }else{
                                            $usernamestatus="Username is not valid.";
                                        }
                                    }else{
                                        $genderstatus="Gender can not be empty.";
                                    }
                                }else{
                                    $bdatestatus="Date of birth can not be empty.";
                                }
                            }else{
                                $surnamestatus="Surname can not be empty.";
                            }
                        }else{
                            $namestatus="Name can not be empty.";
                        }
                    }else{
                        $mailstatus="E-mail can not be empty.";

                    }
                }else{
                    $repasswordstatus="Retype Password can not be empty.";
                }
            }else{
                $passwordstatus="Password can not be empty.";
            }
        }else{
            $usernamestatus="Username can not be empty.";
        }
    }
    $usernamestatus="Enter Username";
    $passwordstatus="Enter Password";
    $repasswordstatus="Enter Password";
    $mailstatus="Enter E-mail";
    $namestatus="Enter Name";
    $surnamestatus="Enter Surname";
    $bdatestatus="Enter Date of Birth";
    if($_POST){
        signin();
    }else{
        $cookie=cookie_control();
        if($cookie==True){
            $username=$_SESSION["username"];
            $conn=db_connect();
            header("Location: ./index.php");
            die();
        }
    }
?>
<html>
    <head>
        <meta charset="utf-8">
        <title>Login - Kaan ARI</title>
        <link rel="stylesheet" href="./assest/styles/styles_signup.css">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    
    <body>
        <div class="wrapper">
            <header>
                <div  class="header"></div>
            </header>
            <div class="navbar">
                <ul>
                    <li><a href="./index.php">Home</a></li>
                    <li><a href="./archive.php">Archive</a></li>
                    <li><a href="./about.php">About</a></li>
                    <li><a class="active" href="./signup.php" style="cursor:pointer; float: right;">Sign Up</a></li> 
                    <li><a href="./login.php" style="float:right;">Login</a></li>
                </ul>
            </div>

            <div class="content">
                <form style="" action="./signup.php" method="POST" enctype="multipart/form-data">
                <div class="form1">
                        <label><center><b style="color:darkred;">Account Information:</b></center></label><br>
                        <label><b>Username <span style="color:darkred;">(*)</span></b> </label>
                        <input <?php echo("placeholder='$usernamestatus'"); ?> class="inp" type="text" name="username" required>
                        <label><b>Password <span style="color:darkred;">(*)</span></b></label>
                        <input <?php echo("placeholder='$passwordstatus'"); ?> class="inp" type="password" name="pwd" required>
                        <label><b>Retype Password <span style="color:darkred;">(*)</span></b></label>
                        <input <?php echo("placeholder='$repasswordstatus'"); ?> class="inp" type="password" name="repwd" required>
                        <label><b>E-mail <span style="color:darkred;">(*)</span></b></label>
                        <input <?php echo("placeholder='$mailstatus'"); ?> class="inp" type="email" name="mail" required>
                        <br>
                        
                        
                </div>
                <hr class="a">
                <div class="form1">
                        <label><center><b style="color:darkred;">Personal Information:</b></center></label><br>
                        <label><b>Name <span style="color:darkred;">(*)</span></b></label>
                        <input <?php echo("placeholder='$namestatus'"); ?> class="inp" type="text" name="name" required>
                        <label><b>Surname <span style="color:darkred;">(*)</span></b></label>
                        <input <?php echo("placeholder='$surnamestatus'"); ?> class="inp" type="text" name="surname" required>
                        <label><b>Gender <span style="color:darkred;">(*)</span></b></label><br>
                        <span class="gender">
                        <input type="radio" name="gender" value="Male"> Male
                        <input type="radio" name="gender" value="Female"> Female  
                        <input type="radio" name="gender" value="Other" checked> Other
                        </span>
                        <br>
                        <label><b>Birthday <span style="color:darkred;">(*)</span></b></label>
                        <input <?php echo("placeholder='$bdatestatus'"); ?> class="inp" type="text" onfocus="(this.type='date')" onblur="(this.type='text')" id="date" name="bdate" required>
                        <label><b>Tel Number <span style="color:darkred;"></span></b></label>
                        <input class="inp" type="text" name="usrtel">
                        <label><b>Country</b></label>
                        <input class="inp" type="text" name="country">                      
                        <br>
                        
                        
                </div>
                <hr class="a">
                <div class="form1">
                        <label><center><b style="color:darkred;">Additional Information:</b></center></label><br>
                        <label><b>Profile Photo</b></label>
                        <center><div style="background-image:url(./assest/img/profile_default.png);border-radius:10px;margin-top:10px;height:175px;width:150px;overflow:hidden;background-position:center;background-repeat:no-repeat;background-size:cover;"></div></center><br>
                        <center><label class="uploadbtn" for="ppimg">Browse...</label></center>
                        <input style="z-index:-1; position:absolute; opacity:0;" type="file" name="ppimg" id="ppimg" accept=".jpg, .jpeg, .png">
                        <input class="sgninbtn" type="submit" value="SIGNUP">
                        <br>
                        
                </div>
                </form>
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