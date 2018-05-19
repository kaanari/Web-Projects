<?php
    session_start();

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
    function last_id(){
        $conn=db_connect();

        $result = mysqli_insert_id($conn);
        return $result;
    }
    function article_puller($article){
        $query="SELECT title,article,authorid,imgurl FROM articles WHERE id LIKE '" . mysqli_escape_string($article) . "'";
        $result = mysqli_query($conn, $query);

    }
    function getinfo($id){
        global $usr_info;
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT * FROM users WHERE id=?");
        $stmt->bind_param("s",$id);
        $stmt->execute();
        $query = $stmt->get_result();
        $usr_info=$query->fetch_assoc();
        if(empty($usr_info) || !(is_null($usr_info["deleted_at"]))){
            header("Location: ./index.php");                                                                
            die();
        }
    }
    $last_article=last_id();
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
        getinfo($id);
    }else{
        header("Location: ./login.php");                                                                
        die();  
    }
?>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo($usr_info["username"]);?>'s Profile</title>
        <link rel="stylesheet" href="./assest/styles/styles_profile.css">
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
                            if($userid==$id){
                                echo'
                                <li><a href="./logout.php" style="cursor:pointer; float: right;">Logout</a></li> 
                                <li><a class="active" href="./profile.php?id='.$userid.'" style="float:right;">Profile</a>
                                ';
                            }else{
                                echo'
                                <li><a href="./logout.php" style="cursor:pointer; float: right;">Logout</a></li> 
                                <li><a href="./profile.php?id='.$userid.'" style="float:right;">Profile</a>
                                ';
                            }
                            
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
                <div class="form1">
                        <label><center><b style="color:darkred;">Account Information:</b></center></label><br><br>
                        <label><b>Username : <?php echo($usr_info["username"]);?></b> </label><br><br>
                        <label><b>E-mail : <?php echo($usr_info["email"]);?></b></label>
                </div>
                <hr class="a">
                <div class="form1">
                        <label><center><b style="color:darkred;">Personal Information:</b></center></label><br><br>
                        <label><b>Name : <?php echo($usr_info["usr_name"]);?></b></label><br><br>
                        <label><b>Surname : <?php echo($usr_info["usr_surname"]);?></b></label><br><br>
                        <label><b>Gender : <?php echo($usr_info["gender"]);?></b></label><br><br>
                        <label><b>Birthday : <?php echo($usr_info["bdate"]);?></b></label><br><br>
                        <label><b>Tel Number : <?php echo($usr_info["usr_phone"]);?></b></label><br><br>
                        <label><b>Country : <?php echo($usr_info["country"]);?></b></label>
                        <br>
                        
                        
                </div>
                <hr class="a">
                <div class="form1">
                    <center><div style="background-image:url(<?php echo($usr_info["pimg"]) ?>);border-radius:10px;margin-top:10px;height:175px;width:150px;overflow:hidden;background-position:center;background-repeat:no-repeat;background-size:cover;"></div></center><br>
                    <?php
                    if($userid==$id){
                        echo'
                        <a style="text-decoration:none;" href="./profilepanel.php?id='.$userid.'">  
                        <div class="sgninbtn">
                        <center><span id="btnn">EDIT</span></center>
                        </div>
                        </a>
                        ';
                    }
                    
                    ?>
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