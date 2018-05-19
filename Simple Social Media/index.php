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

    function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);
    
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;
    
        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }
    
        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    function writer_name($id){
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT username FROM users WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $query = $stmt->get_result();
        $result=$query->fetch_assoc();
        return $result["username"];
    }
    function last5(){
        $conn=db_connect();
        $sql = "SELECT * FROM articles ORDER BY id DESC LIMIT 5";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
        // output data of each row
            $y=0;
            while($row = $result->fetch_assoc()) {
                $body=strip_tags($row["body"]);
                if($y%2 == 0){
                    echo '

                    <a style="color:black;text-decoration:none;" href="article.php?id='.$row["id"].'">

                    <div class="rightcnt">

                        <div style="position:relative;">
                            <img class="rightcntimg" src="'.$row["img"].'"/>
                            <div class="articlebtn">
                                <h3>READ MORE</h3>

                            </div>
                            
                        </div>
                        <div>
                            <h3 style="color:black;">'.$row["title"].'</h3>
                            <p class="par" style="color:black;">'.$body.'</p>
                            </a>
                            <div class="type1"><h5><span class="iconb">Rating: </span><span class="iconc">'.$row["rating"].'</span><img alt="Views" class="icona" src="./assest/img/eye.png"><span class="iconc">'.$row["views"].'</span><img alt="Comments" class="icona" src="./assest/img/comment.png"><span class="iconc">'.$row["comments"].'</span><span class="author1"><a style="text-decoration:none;" href="./profile.php?id='.$row["uid"].'"><img class="icona" alt="Author" src="./assest/img/account.png"><span class="iconc">'.writer_name($row["uid"]).'</span></a><img class="icona" src="./assest/img/clock.png"><span class="iconc">'.time_elapsed_string($row["up_time"]).'</span></span></h5></div>
                            <br><div class="author2"><h5 style="font-style:normal;"><img class="icona" src="./assest/img/clock.png"><span class="iconc">'.time_elapsed_string($row["up_time"]).'</span></h5><a style="text-decoration:none;" href="./profile.php?id='.$row["uid"].'"><h5><img class="icona" src="./assest/img/account.png"><span class="iconc">'.writer_name($row["uid"]).'</span></h5></a></div>

                        </div>
                    </div>                    
                    ';
                    if(!($y==4)){
                        echo('<hr class="hr1">');
                    }
                    $y=$y+1;
                }else{
                    echo '
                    <a style="color:black;text-decoration:none;" href="article.php?id='.$row["id"].'">
                    <div class="leftcnt">
                        <div style="position:relative;">
                            <img class="leftcntimg" src="'.$row["img"].'"/>
                            <div class="articlebtn2">
                                <h3>READ MORE</h3>
                            </div>
                        </div>
                        <div>
                            <h3 style="color:black;">'.$row["title"].'</h3>
                            <p class="par" style="color:black;">'.$body.'</p>
                            </a>
                            <div class="type2"><h5><span class="author1"><img class="iconaa" src="./assest/img/clock.png"><span class="iconac">'.time_elapsed_string($row["up_time"]).'</span><a style="text-decoration:none;" href="./profile.php?id='.$row["uid"].'"><img class="iconaa" alt="Author" src="./assest/img/account.png"><span class="iconac">'.writer_name($row["uid"]).'</span></a></span><img alt="Comments" class="iconaa" src="./assest/img/comment.png"><span class="iconac">'.$row["comments"].'</span><img alt="Views" class="iconaa" src="./assest/img/eye.png"><span class="iconac">'.$row["views"].'</span><span class="iconab">Rating: </span><span class="iconac">'.$row["rating"].'</span></h5></div>
                            <br><div class="author3"><h5 style="font-style:normal;"><img class="iconaa" src="./assest/img/clock.png"><span class="iconac">'.time_elapsed_string($row["up_time"]).'</span><a style="text-decoration:none;" href="./profile.php?id='.$row["uid"].'"><img class="iconaa" src="./assest/img/account.png"><span class="iconac">'.writer_name($row["uid"]).'</span></h5></a></div>
                            </div>
                    </div>
                    <hr class="hr1">
                    ';
                    $y=$y+1;
                }
                    
            }
        } else {
            echo "0 results";
        }
    }
    $cookie=cookie_control();
    if($cookie==True){
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT id,deleted_at FROM users WHERE username=?");
        $stmt->bind_param("s",$_SESSION["username"]);
        $stmt->execute();
        $query = $stmt->get_result();
        $result=$query->fetch_assoc();
        $id=$result["id"];
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
        <title>Homepage - Kaan ARI</title>
        <link rel="stylesheet" href="./assest/styles/styles_main.css">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    
    <body>
        <div class="wrapper">
            <header>
                <div  class="header"></div>
            </header>
            <div class="navbar">
                <ul>
                    <li><a class="active" href="./index.php">Home</a></li>
                    <li><a href="./archive.php">Archive</a></li>
                    <li><a href="./about.php">About</a></li>
                    <?php
                        if((cookie_control())){
                            echo'
                                <li><a href="./logout.php" style="cursor:pointer; float: right;">Logout</a></li> 
                                <li><a href="./profile.php?id='.$id.'" style="float:right;">Profile</a></li>
                            ';
                        }else{
                            echo'
                                <li><a href="./signup.php" style="cursor:pointer; float: right;">Sign Up</a></li> 
                                <li><a href="./login.php" style="float:right;">Login</a></li>
                            ';
                        }

                    ?>
                </ul>
            </div>


            <div class="content">

                <?php last5();?>
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