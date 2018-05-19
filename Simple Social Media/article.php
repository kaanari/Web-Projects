
<?php
    session_start();
    function comm_control($comm){
        global $sendbuttonstatus;
        $r1='/[A-Z]/';  //Uppercase
        $r2='/[a-z]/';  //lowercase
        $r3='/[$|&"]/';  // whatever you mean by 'special char'
        $r4='/[0-9]/';  //numbers
        $letter=preg_match_all($r1,$comm,$o)+preg_match_all($r2,$comm,$o)+preg_match_all($r3,$comm,$o)+preg_match_all($r4,$comm
        ,$o);
        if(!(strlen($comm)>140)){
            if(!(preg_match_all($r3,$comm,$o)>0)){
                $sendbuttonstatus="Sended.";
                return TRUE;
            }
            else{
                $sendbuttonstatus="Invalid Characters in your comment.";
                return FALSE;
            }
        }else{
            $sendbuttonstatus="Your comment is longer then 140 character.";
            return FALSE;
        }
    }
    function counter($views,$artcid){
        $conn=db_connect();
        $views=$views+1;
        $stmt = $conn->prepare("UPDATE articles SET views=? WHERE id=?");
        $stmt->bind_param("ii",$views,$artcid);
        $stmt->execute();

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
    function comment(){
        global $id;
        if(comm_control($_POST["comment"])){
            $conn=db_connect();
            $sql = "SELECT * FROM comments WHERE artid=".$_GET["id"]."";
            $stmt = $conn->query($sql);
            $comments=$stmt->num_rows;
            $comments=$comments+1;
            $stmt = $conn->prepare("INSERT INTO comments (artid, uid, comment, up_time) VALUES(?,?,?,NOW())");
            $stmt->bind_param("iis", $_GET["id"],$id,$_POST["comment"]);
            if ($stmt->execute()) {
                #echo("Success");
                $stmt = $conn->prepare("UPDATE articles SET comments=? WHERE id=?");
                $stmt->bind_param("ii", $comments,$_GET["id"]);
                $stmt->execute();
            } else {
                echo "Error: ".$stmt->error;
                die();
            }
            return TRUE;
        }else{
            return FALSE;
        }
    }
    function comments(){
        global $id;
        global $num_comments,$sendbuttonstatus;
        $conn=db_connect();
        $sql = "SELECT * FROM comments WHERE artid=".$_GET["id"]."";
        $stmt = $conn->query($sql);
        $num_comments=$stmt->num_rows;
        #$stmt->bind_param("i",$_GET["id"]);
        if ($stmt->num_rows > 0) {
        // output data of each row

            $y=0;
            while($row = $stmt->fetch_assoc()) {
                echo'
                    <div class="allcomm">
                    <div class="commentbox">'.$row["comment"].'</div>
                    <div class="cominfo"><h5><a href="./profile.php?id='.$row["uid"].'" style="text-decoration:none;color:gray;"><img class="iconaab" src="./assest/img/account.png"><span class="iconaac">'.writer_name($row["uid"]).'</span></h5></a></div>
                    </div>
                ';  
            }
        } else {
            echo'<span style="color:rgba(201, 200, 200, 1);"><h5>No Comment</h5></span>';
        }
        echo '<hr style="background-color:#333; border-color:#333;margin-top:10px;display:block;">';
        if(cookie_control()==True){
            echo'
                <div style="height:auto;">
                    <div style="padding-right:10px;height:50px;">
                        <form action="./article.php?id='.$_GET["id"].'" id="usrform" method="POST">
                        <div style="float:left;"><a style="text-decoration:none;color:black;" href="./profile.php?id='.$id.'"><img class="icona" src="./assest/img/account.png"><h5 style="float:right;margin-top:5px;margin-left:7px;">'.$_SESSION["username"].'</h5></a></div>
                        <div style="float:right;margin-right:20px;"><input class="sgninbtn" type="submit"></div>
                    </form>
                    </div>
                    
                    <div style="height:80px;widht:300px;margin:auto;margin-top:-5px;width: 100%;">
                    <textarea rows="3" cols="60" name="comment" form="usrform" class="combox" placeholder="'.$sendbuttonstatus.'"></textarea>
                    </div>
                </div>
            ';
        }else{
            echo'<span style="margin-bottom:20px;color:rgba(201, 200, 200, 1);"><h5>Please Login to comment</h5></span>';
        }
    }

    function article($id){
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT * FROM articles WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $query = $stmt->get_result();
        $result=$query->fetch_assoc();
        counter($result["views"],$id);
        return $result;
    }
    function writer_name($id){
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT username,deleted_at FROM users WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $query = $stmt->get_result();
        $result=$query->fetch_assoc();
        if(is_null($result["deleted_at"])){
            return $result["username"];
        }else{
            return "<del style='color:red;'><span style='color:red;vertical-align:bottom;line-height:30px;'>".$result["username"]."</span></del>";
        }
    }
    $sendbuttonstatus="Enter Comment Here\n(140 Character)";
    $article=article($_GET["id"]);
    $writer=writer_name($article["uid"]);
    $cookie=cookie_control();
    if($cookie){
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
        if($_POST){
            if(comment()){
                header("Location:#");
            }
        }
    }

?>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo($article["title"])?> - Kaan Arı</title>
        <link rel="stylesheet" href="./assest/styles/styles_article.css">
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
                <div class="image" style="position:relative;background-image:url(<?php echo($article["img"]) ?>);">
                    <h3 class="title"><?php echo($article["title"])?></h3>
                </div>
                <center><?php echo'<h5 class="time"><img class="icontime" src="./assest/img/clock.png"><span class="iconac">'.time_elapsed_string($article["up_time"]).'</span></h5>';?>
                    </center>
                <div class="article">

                    <p><?php echo($article["body"])?></p>
                </div>
                <center><div class="infoart"><h5><span class="iconb">Rating: </span><span class="iconc"><?php echo($article["rating"])?></span><img alt="Views" class="icona" src="./assest/img/eye.png"><span class="iconc"><?php echo($article["views"])?></span><img alt="Comments" class="icona" src="./assest/img/comment.png"><span class="iconc"><?php echo($article["comments"])?></span><span class="stars2"><a style="text-decoration:none;" href="./profile.php?id=<?php echo($article["uid"])?>"><img class="icona" alt="Author" src="./assest/img/account.png"><span class="iconc"><?php echo($writer)?></span></a></span></h5></div>
                    </center>
                <center><div class="author"><a style="text-decoration:none;" href="./profile.php?id=<?php echo($article["uid"])?>"><h5><img class="icona" src="./assest/img/account.png"><span class="iconc"><?php echo($writer)?></span></h5></a></div></center>
                <hr>
                <div class="comment">
                    <h3 style="vertical-align:middle;color:lightgrey;" >Comments</h3>
                    <hr style="background-color:#333; border-color:#333;margin-bottom:10px;">
                    <?php comments();  ?>

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