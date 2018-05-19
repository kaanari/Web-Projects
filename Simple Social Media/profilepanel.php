<?php
    session_start();
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
        $conn = mysqli_connect($servername, $usrname, $passwd,$dbname);
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }else{
            return $conn;
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

    function image(){
        global $userid;
        global $ppimg;
        if ($_FILES['ppimg']['size'] != 0 && $_FILES['ppimg']['error'] == 0){
            $tmp_name=$_FILES['ppimg']["tmp_name"];
            $info = getimagesize($tmp_name);
            $extension = image_type_to_extension($info[2]);;
            $name=$userid.$extension;
            $uploads_dir="usrimg";
            if($info != false) {
                if(move_uploaded_file($tmp_name,"$uploads_dir/$name")){
                    $ppimg="./$uploads_dir/$name";
                    return True;
                }else{
                    return False;
                }
            } else {
                return False;
            }
        }
    }

    function getinfo($id){
        global $usr_info;
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT * FROM users WHERE id=?");
        $stmt->bind_param("s",$id);
        $stmt->execute();
        $query = $stmt->get_result();
        $usr_info=$query->fetch_assoc();
        $stmt->close();
    }
    function update_userdata($id){
        global $ppimg,$passwordstatus,$newpwdstatus,$usr_info,$buttonstatus;
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT * FROM users WHERE id=?");
        $stmt->bind_param("s",$id);
        $stmt->execute();
        $query = $stmt->get_result();
        $result=$query->fetch_assoc();
        $stmt->close();
        if(password_verify($_POST["pwd"],$result["pwd"])){
           
            if(!(empty($_POST["name"]))){
                $name=$_POST["name"];
            }else{
                $name=$usr_info["usr_name"];
            }

            if(!(empty($_POST["surname"]))){
                $surname=$_POST["surname"];
            }else{
                $surname=$usr_info["usr_surname"];
            }
            if(!(empty($_POST["gender"]))){
                $gender=$_POST["gender"];
            }else{
                $gender=$usr_info["gender"];
            }
            if(!(empty($_POST["bdate"]))){
                $bdate=$_POST["bdate"];
            }else{
                $bdate=$usr_info["bdate"];
            }
            if(!(empty($_POST["usrtel"]))){
                $usrtel=$_POST["usrtel"];
            }else{
                $usrtel=$usr_info["usr_phone"];
            }
            if(!(empty($_POST["country"]))){
                $country=$_POST["country"];
            }else{
                $country=$usr_info["country"];
            }
            
            if($name!=$usr_info["usr_name"] && !(empty($name)) && valid_name($name)){
                $newname=$name;
            }else{
                $newname=$usr_info["usr_name"];
            }

            if($surname!=$usr_info["usr_surname"] && !(empty($surname)) && valid_name($surname)){
                $newsurname=$surname;
            }else{
                $newsurname=$usr_info["usr_surname"];
            }

            if($bdate!=$usr_info["bdate"] && !(empty($bdate))){
                $newbdate=$bdate;
            }else{
                $newbdate=$usr_info["bdate"];
            }

            if($gender!=$usr_info["gender"] && !(empty($gender))){
                $newgender=$gender;
            }else{
                $newgender=$usr_info["gender"];
            }

            if($country!=$usr_info["country"]){
                $newcountry=$country;
            }else{
                $newcountry=$usr_info["country"];
            }
            if($usrtel!=$usr_info["usr_phone"]){
                $newusrtel=$usrtel;
            }else{
                $newusrtel=$usr_info["usr_phone"];
            }


            if(!(empty($_POST["newpwd"])) || !(empty($_POST["renewpwd"]))){
                if(!(empty($_POST["newpwd"])) && !(empty($_POST["renewpwd"]))){
                    if($_POST["newpwd"]==$_POST["renewpwd"]){
                        if(valid_pass($_POST["newpwd"])){
                            $newpwd=password_hash($_POST["newpwd"], PASSWORD_DEFAULT);
                        }else{
                            $newpwdstatus="New Password is no valid.";
                        }
                    }else{
                        $newpwdstatus="Passwords are not same.";
                    }
                }else{
                    $newpwdstatus="This field can not be empty.";
                }
            }else{
                $newpwd=$usr_info["pwd"];
            }
            $conn=db_connect();
            if(image()){
                $newppimg=$ppimg;
                $stmt = $conn->prepare("UPDATE users SET pwd=?, usr_name=?, usr_surname=?, gender=?, bdate=?, usr_phone=?, country=?, pimg=? WHERE id=?");
                $stmt->bind_param("ssssssssi", $newpwd,$newname,$newsurname,$newgender,$newbdate,$newusrtel,$newcountry,$ppimg,$id);
            }else{
                $stmt = $conn->prepare("UPDATE users SET pwd=?, usr_name=?, usr_surname=?, gender=?, bdate=?, usr_phone=?, country=? WHERE id=?");
                $stmt->bind_param("sssssssi", $newpwd,$newname,$newsurname,$newgender,$newbdate,$newusrtel,$newcountry,$id);
            }
            
            if ($stmt->execute()) {
                $buttonstatus="SUCCES";
                $stmt->close();
            } else {
                echo "Error: ".$stmt->error;
                die();
            }
        }else{
            $passwordstatus="Wrong Password";
        }
    }
    $buttonstatus="SAVE";
    $passwordstatus="Enter Password";
    $cookie=cookie_control();
    if($cookie){
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT id,deleted_at FROM users WHERE username=?");
        $stmt->bind_param("s",$_SESSION["username"]);
        $stmt->execute();
        $query = $stmt->get_result();
        $result=$query->fetch_assoc();
        $userid=$result["id"];
        $id=$_GET["id"];
        if(!(is_null($result["deleted_at"]))){
            setcookie("auth", "", time() - 3600);
            session_unset();
            session_destroy();
            header("Location: ./index.php");
            die();
        }
        if($userid==$id){
            getinfo($id);
            if($_POST){
                update_userdata($userid);
                getinfo($id);
            }
        }else{
            header("Location: ./profile.php?id=$id");                                                                
            die();  
        }
    }else{
        header("Location: ./index.php");                                                                
        die();  
    }
?>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo($usr_info["username"]);?>'s Profile</title>
        <link rel="stylesheet" href="./assest/styles/styles_profilepanel.css">
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
                    <?php
                        if((cookie_control())){
                            echo'
                                <li><a href="./logout.php" style="cursor:pointer; float: right;">Logout</a></li> 
                                <li><a href="./profile.php?id='.$userid.'" style="float:right;">Profile</a>
                            
                            ';
                        }else{
                            echo'
                                <li><a href="./signup.php" style="cursor:pointer; float: right;">Sign Up</a></li> 
                                <li><a href="./login.php" style="float:right;">Login</a>         
                            ';
                        }

                    ?>
                    </li>
                </ul>
            </div>


            <div class="content">
            <form action="./profilepanel.php?id=<?php echo($userid);?>" method="POST" enctype="multipart/form-data">
                <div class="form1" style="user-select:none;">
                        <label><center><b style="color:darkred;">Account Information:</b></center></label><br>
                        <label><b>Username </b> </label>
                        <input class="inp2" type="text" name="username" value="<?php echo($usr_info["username"]);?>" disabled>
                        <label><b>Password <span style="color:darkred;">(*)</span></b></label>
                        <input class="inp" type="password" placeholder="<?php echo($passwordstatus);?>" name="pwd" required>
                        <label><b>New Password </b></label>
                        <input class="inp" type="password" name="newpwd" placeholder="Enter New Password">
                        <label><b>Retype New Password </span></b></label>
                        <input class="inp" type="password" name="renewpwd" placeholder="Enter New Password">
                        <label><b>E-mail </b></label>
                        <input class="inp2" type="email" name="mail" value="<?php echo($usr_info["email"]);?>" disabled>
                        <br>
                        
                        
                </div>
                <hr class="a">
                <div class="form1">
                        <label><center><b style="color:darkred;">Personal Information:</b></center></label><br>
                        <label><b>Name </b></label>
                        <input class="inp" type="text" name="name" value="<?php echo($usr_info["usr_name"]);?>" required>
                        <label><b>Surname </b></label>
                        <input class="inp" type="text" name="surname" value="<?php echo($usr_info["usr_surname"]);?>" required>
                        <label><b>Gender </span></b></label><br>
                        <?php
                            if($usr_info["gender"]=="Male"){
                                echo'
                                    <span class="gender">
                                    <input type="radio" name="gender" value="Male" checked> Male
                                    <input type="radio" name="gender" value="Female"> Female  
                                    <input type="radio" name="gender" value="Other"> Other
                                    </span>
                                ';
                            }
                            if($usr_info["gender"]=="Female"){
                                echo'
                                    <span class="gender">
                                    <input type="radio" name="gender" value="Male"> Male
                                    <input type="radio" name="gender" value="Female" checked> Female  
                                    <input type="radio" name="gender" value="Other"> Other
                                    </span>
                                ';
                            }
                            if($usr_info["gender"]=="Other"){
                                echo'
                                <span class="gender">
                                <input type="radio" name="gender" value="Male"> Male
                                <input type="radio" name="gender" value="Female"> Female  
                                <input type="radio" name="gender" value="Other" checked> Other
                                </span>
                            ';
                            }
                        
                        ?>
                        <br>
                        <label><b>Birthday </b></label>
                        <input class="inp" type="text" value="<?php echo($usr_info["bdate"]);?>" onfocus="(this.type='date')" onblur="(this.type='text')" id="date" name="bdate" required>
                        <label><b>Tel Number <span style="color:darkred;"></span></b></label>
                        <input class="inp" type="text" name="usrtel" placeholder="Enter Phone Number" value="<?php echo($usr_info["usr_phone"]);?>">
                        <label><b>Country</b></label>
                        <input class="inp" type="text" placeholder="Enter Country" name="country" value="<?php echo($usr_info["country"]);?>">                      
                        <br>
                        
                        
                </div>
                <hr class="a">
                <div class="form1">
                        <label><center><b style="color:darkred;">Additional Information:</b></center></label><br>
                        <label><b>Profile Photo</b></label>
                        <center><div style="background-image:url(<?php echo($usr_info["pimg"])?>);border-radius:10px;margin-top:10px;height:175px;width:150px;overflow:hidden;background-position:center;background-repeat:no-repeat;background-size:cover;"></div></center><br>
                        <center><label class="uploadbtn" for="ppimg">Browse...</label></center>
                        <input style="z-index:-1; position:absolute; opacity:0;" type="file" name="ppimg" id="ppimg" accept=".jpg, .jpeg, .png">
                        <input class="sgninbtn" type="submit" value="<?php echo($buttonstatus);?>" onfocus="(this.value='SAVE')">
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