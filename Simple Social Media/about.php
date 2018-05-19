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

    $last_article=last_id();
    $cookie=cookie_control();
    if($cookie==True){
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT id,deleted_at FROM users WHERE username=?");
        $stmt->bind_param("s",$_SESSION["username"]);
        $stmt->execute();
        $query = $stmt->get_result();
        $result=$query->fetch_assoc();
        $userid=$result["id"];
        if(!(is_null($result["deleted_at"]))){
            setcookie("auth", "", time() - 3600);
            session_unset();
            session_destroy();
            header("Location: ./index.php");
            die();
        }
    }
?>
<html>
    <head>
        <meta charset="utf-8">
        <title>About - Kaan ARI</title>
        <link rel="stylesheet" href="./assest/styles/styles_about.css">
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
                    <li><a class="active" href="./about.php">About</a></li>
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
                <center>
                <div style="color:black;margin-top:30px;">
                <span>ME !</span><br><br>
                <img src="./assest/img/about.jpg" style="height:200px;witdh:120px"><br><br>
                <p>
                Hi my name is Kaan ARI. <br>I'm student at Hacettepe University.<br> 
                My department is Electrical and Electronic Engineering. Also i love writing code. <br>
                
                
                
                Best Wishes</p>
                </div>
                </center>
                
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