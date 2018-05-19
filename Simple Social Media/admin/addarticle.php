
<?php
    session_start();
    function image(){
        global $id;
        global $ppimg;
        if ($_FILES['ppimg']['size'] != 0 && $_FILES['ppimg']['error'] == 0){
            $tmp_name=$_FILES['ppimg']["tmp_name"];
            $info = getimagesize($tmp_name);
            $extension = image_type_to_extension($info[2]);;
            $name=$id.$extension;
            $uploads_dir="./artimg";
            if($info != false) {
                if(move_uploaded_file($tmp_name,".$uploads_dir/$name")){
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
        $r1='/[A-Z]/';  //Uppercase
        $r2='/[a-z]/';  //lowercase
        $r3='/[şçüğıö!@#$%^()\-_=+;:,. "]/';  // whatever you mean by 'special char'
        $r4='/[0-9]/';  //numbers
        $letter=preg_match_all($r1,$comm,$o)+preg_match_all($r2,$comm,$o)+preg_match_all($r3,$comm,$o)+preg_match_all($r4,$comm
        ,$o);
        if(!(strlen($comm)>140)){
            if($letter==strlen($comm)){
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
    function add_art($id){
        global $ppimg,$id;
        if(!(empty($_POST["title"]))&&!(empty($_POST["body"]))&&valid_title($_POST["title"])){
            $conn=db_connect();
            $comment=0;
            $view=0;
            $rating=0;
            $stmt = $conn->prepare("INSERT INTO articles (title, body, comments, views, rating, uid, up_time) VALUES(?,?,?,?,?,?,NOW())");
            $stmt->bind_param("ssiiii", $_POST["title"],$_POST["body"],$comment,$view,$rating,$id);
            $stmt->execute();
            $id=$conn->insert_id;
            if(image()){
                $newppimg=$ppimg;
            }else{
                $newppimg="./assest/img/header.jpg";
            }
            $stmt = $conn->prepare("UPDATE articles SET img=? WHERE id=?");
            $stmt->bind_param("si",$newppimg,$id);
            $stmt->execute();
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

    function article($id){
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT * FROM articles WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $query = $stmt->get_result();
        $result=$query->fetch_assoc();
        return $result;
    }
    function writer_id($id){
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT id FROM users WHERE username=?");
        $stmt->bind_param("s",$id);
        $stmt->execute();
        $query = $stmt->get_result();
        $result=$query->fetch_assoc();
        return $result["id"];
    }
    $sendbuttonstatus="Enter Comment Here\n(140 Character)";
    $buttonstatus="SAVE";
    $cookie=cookie_control();
    $id=writer_id($_SESSION["username"]);
    if($cookie){
        $conn=db_connect();
        if($_POST){
            add_art($id);
            $article=article($id);
        }
    }else{
        header("Location:./index.php");
    }

?>
<html>
    <head>
        <meta charset="utf-8">
        <title>New Article</title>
        <link rel="stylesheet" href="./assest/styles/styles_articleedit.css">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cloud.tinymce.com/stable/tinymce.min.js"></script>
        <script>tinymce.init({ selector:'textarea' });</script>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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

            <div class="content">
                <form action="#" method="POST" id="form5" enctype="multipart/form-data">
                <div class="image" style="position:relative;background-image:url(./assest/img/article.jpg);">
                </div>
                <center><label class="uploadbtn" for="ppimg">Browse Article Photo...</label></center>
                        <input style="z-index:-1; position:absolute; opacity:0;" type="file" name="ppimg" id="ppimg" accept=".jpg, .jpeg, .png">
                <br>
                <center><label style="color:darkred;"><b>Title</b> </label></center>
                <center><input class="inp" type="text" name="title" placeholder="Enter Title" required></center>
                <div class="article">
                    <p>
                        <center>
                        <textarea name="body" form="form5" style="border-radius:10px;min-height:500px;width:90%">Enter Article Text</textarea>
                        </center>
                    </p>
                    <input class="sgninbtn" type="submit" value="<?php echo($buttonstatus);?>" onfocus="(this.value='SAVE')">
                </div>
                </form>
                <center><div class="infoart"><h5><span class="stars2"><a style="text-decoration:none;" href="./profile.php?id=<?php echo($id)?>"><img class="icona" alt="Author" src="./assest/img/account.png"><span class="iconc"><?php echo($_SESSION["username"])?></span></a></span></h5></div>
                    </center>
                <center><div class="author"><a style="text-decoration:none;" href="./profile.php?id=<?php echo($article["uid"])?>"><h5><img class="icona" src="./assest/img/account.png"><span class="iconc"><?php echo($writer)?></span></h5></a></div></center>

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