
<?php
    session_start();
    
    function valid_title($name)
    {
        $r3='/[!@#$%^&*()\-_=+{};:,<.>]/';
        if(preg_match_all($r3,$name, $o)>0) return FALSE;
        if(strlen($name)<1) return FALSE;
        if(strlen($name)>40) return FALSE;
        return TRUE;
    }
    function comm_control($comm){
        global $sendbuttonstatus;
        $r3='/[!@#$%^()\-_=+,.&|"]/';  // whatever you mean by 'special char'
        if(!(strlen($comm)>140)){
            if(!(preg_match_all($r3,$comm,$o)>0)){
                $sendbuttonstatus="Sended.";
                return TRUE;
            }
            else{
                $sendbuttonstatus="Unvalid Characters in your comment.";
                return FALSE;
            }
        }else{
            $sendbuttonstatus="Your comment is longer then 140 character.";
            return FALSE;
        }
    }
    function vote_control($name){
        $r4='/[0-9]/';  //numbers
        if(preg_match_all($r4,$name, $o)==strlen($name)){
            if(strlen($name)<1) return FALSE;
            if(strlen($name)>1) return FALSE;
            if($name<=5 && $name>=0) return TRUE;
        }else{
            return FALSE;
        }
        
    }
    function edit_comm($id){       
        if(!(empty($_POST["comment"])) && comm_control($_POST["comment"]) && (!(empty($_POST["vote"])) || $_POST["vote"]=="0") && vote_control($_POST["vote"])){
            $conn=db_connect();
            $stmt = $conn->prepare("UPDATE comments SET comment=?, vote=? WHERE id=?");
            $stmt->bind_param("sii",$_POST["comment"],$_POST["vote"],$id);
            $stmt->execute();
            $buttonstatus="SUCCESS";
        }else{
            $buttonstatus="Something is not valid.";
        }
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
        if(!(empty($_COOKIE["auth"]))&&!(empty($_SESSION))){
            $cookie=$_COOKIE["auth"];
                if($_SESSION["auth"] == $cookie){
                    $conn=db_connect();
                    $stmt=$conn->prepare("SELECT uid FROM admin WHERE username=?");
                    $stmt->bind_param("s",$_SESSION["username"]);
                    $stmt->execute();
                    $query = $stmt->get_result();
                    $result2=$query->fetch_assoc();
                    if(!(empty($result2))){
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
                    <div class="cominfo"><h5 style="float:left;height:30px;display:block;margin-left:20px;margin-bottom:5px;padding-top:5px;"><a href="./commentedit.php?id='.$row["id"].'" style="text-decoration:none;color:gray;"><img class="iconaab" src="./assest/img/edit.png"><a href="./commentdel.php?id='.$row["id"].'&artid='.$_GET["id"].'" style="text-decoration:none;color:gray;"><img alt="Delete" class="iconaab" src="./assest/img/delete-empty.png"></a></a></h5><h5 style="float:right;height:30px;display:block;margin-bottom:5px;margin-right:20px;"><a href="./useredit.php?id='.$row["uid"].'" style="text-decoration:none;color:gray;"><img class="iconaab" src="./assest/img/account-edit.png"></a><a href="./profile.php?id='.$row["uid"].'" style="text-decoration:none;color:gray;"><img class="iconaab" src="./assest/img/account.png"><span class="iconaac">'.writer_name($row["uid"]).'</span></h5></a></div>
                    </div>
                ';  
            }
        } else {
            echo'<span style="color:rgba(201, 200, 200, 1);"><h5>No Comment</h5></span>';
        }
    }
    function arttitle(){
        global $artid;
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT title FROM articles WHERE id=?");
        $stmt->bind_param("i",$artid);
        $stmt->execute();
        $query = $stmt->get_result();
        $result=$query->fetch_assoc();
        return $result["title"];
    }
    function comment($id){
        global $artid;
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT * FROM comments WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $query = $stmt->get_result();
        $result=$query->fetch_assoc();
        $artid=$result["artid"];
        return $result;
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
    $sendbuttonstatus="Enter Comment Here\n(140 Character)";
    $buttonstatus="SAVE";
    $cookie=cookie_control();
    if($cookie==True){
        $conn=db_connect();
        $id=$_GET["id"];
        $comment=comment($id);
        $writer=writer_name($comment["uid"]);
        if($_POST){
            edit_comm($id);
            $comment=comment($id);
        }
    }else{
        header("Location:./index.php");
    }

?>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo($writer);?>'s Comment</title>
        <link rel="stylesheet" href="./assest/styles/styles_articleedit.css">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cloud.tinymce.com/stable/tinymce.min.js"></script>
        <script>tinymce.init({ selector:'textarea' });</script>
    </head>
    
    <body>
        <div class="wrapper">
            <header>
                <div  class="header"></div>
            </header>
            <div class="navbar">
                    <ul>
                    <li><a href="./panel.php">Home</a></li>
                    <li><a href="./articles.php">Articles</a></li>
                    <li><a href="./users.php">Users</a></li>
                    <li><a href="./logout.php" style="float:right;">Logout</a>
                    <li><a href="../index.php" style="cursor:pointer; float: right;">Web Page</a></li> 
     
                    </li>
                </ul>
            </div>

            <div class="content" style="padding-top:30px;">
                <form action="#" method="POST" id="form5">
                <center><label style="color:darkred;"><b>This Comment is in This Article</b> </label></center>
                <center><input class="inp2" type="text" value="<?php echo(arttitle());?>"required></center>
                <center><?php echo'<h5 class="time"><img class="icontime" src="./assest/img/clock.png"><span class="iconac">'.time_elapsed_string($comment["up_time"]).'</span></h5>';?></center>
                <div class="article">

                    <p>
                        <center>
                        <textarea name="comment" form="form5" style="border-radius:10px;min-height:500px;"><?php echo($comment["comment"]);?></textarea>
                        </center>
                    </p>
                <center><div class="infoart"><h5><span class="iconb">Vote: </span><span class="iconc"><input class="inp5" type="text" name="vote" value="<?php echo($comment["vote"])?>" required></span><span class="stars2"><a style="text-decoration:none;" href="./profile.php?id=<?php echo($comment["uid"])?>"><img class="icona" alt="Author" src="./assest/img/account.png"><span class="iconc"><?php echo($writer)?></span></a></span></h5></div>
                    </center>
                    <input class="sgninbtn" type="submit" value="<?php echo($buttonstatus);?>" onfocus="(this.value='SAVE')">
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