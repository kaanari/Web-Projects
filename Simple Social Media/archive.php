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
    function pagenav($pages,$prevlink,$nextlink,$start,$end,$total){
        if(!(empty($_GET["page"]))){
            $pagenum=$_GET["page"];
        }else{
            $pagenum=1;
        }
        $lineone= '<center>
                <div class="paging">
                    <form action="archive.php" method="GET" style="line-height:30px;vertical-align:bottom;">

                    <p><span class="navhide">'.$prevlink.'</span><span class="pagetext"> Page ';
        if($pages>1){
            $ab="";
            $linetwo='
            <select class="pageselect" onchange="if (this.value) window.location.href=this.value">
            ';
            for($i = 1; $i <= $pages; $i++){
                if($i==$pagenum){
                    $aa='<option style="user-select:none;" value=./archive.php?page='.$i.' selected>'.$i.'</option>';
                }else{
                    $aa='<option value=./archive.php?page='.$i.'>'.$i.'</option>';
                }
                $ab=$ab.$aa;
            }
            $linethree='
            </select>
            ';
            $linetwo=$linetwo.$ab.$linethree;
        }else{
            $linetwo="1";
        }
        $linelast=' of '.$pages.' pages, displaying '.$start.'-'.$end.' of '.$total.' results </span><span class="navhide">'.$nextlink.' </span></p></form></div></center>';
        
        echo $lineone.$linetwo.$linelast;
        $line1='<span style="float:left;" class="navhide2">'.$prevlink.'</span>';
        $line2='<span style="float:right;" class="navhide2">'.$nextlink.'</span>';
        echo '<div class="paging"><center><p style="height:30px;margin-left:70px;margin-right:70px;margin-top:-10px;px;">'.$line1.$line2.'</p></center></div>';
    }
    function paginator($pagenum){
        try{
            $conn=db_connect();
            $total = $conn->query('SELECT * FROM articles')->num_rows;
            $limit = 10;
            $pages = ceil($total / $limit);
            #$page = min($pages, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default'   => 1,'min_range' => 1,),)));
            $page=$pagenum;
            $offset = ($page - 1)  * $limit;
            $start = $offset + 1;
            $end = min(($offset + $limit), $total);
            $prevlink = ($page > 1) ? '<a href="?page=1" title="First page" style="text-decoration:none;"><img class="pagenavicon" src="./assest/img/double_left.png"></a> <a href="?page=' . ($page - 1) . '" title="Previous page" style="text-decoration:none;"><img class="pagenavicon" src="./assest/img/left.png"></a>' : '<span class="disabled"><img class="pagenavicon" src="./assest/img/double_left.png"></span> <span class="disabled"><img class="pagenavicon" src="./assest/img/left.png"></span>';
            $nextlink = ($page < $pages) ? '<a href="?page=' . ($page + 1) . '" title="Next page" style="text-decoration:none;"><img class="pagenavicon" src="./assest/img/right.png"></a> <a href="?page=' . $pages . '" title="Last page" style="text-decoration:none;"><img class="pagenavicon" src="./assest/img/double_right.png"></a>' : '<span class="disabled">&rsaquo;</span> <span class="disabled">&raquo;</span>';
            $stmt = $conn->query('SELECT * FROM articles ORDER BY id desc LIMIT '.$limit.' OFFSET '.$offset.'');
            if(!(empty($page))){
                if ($stmt->num_rows > 0) {
                    $iterator = new IteratorIterator($stmt);
                    $y=0;
                    foreach ($iterator as $row) {
                        if($y%2 == 0){
                            echo '
                                <a style="text-decoration:none;" href="article.php?id='.$row["id"].'">
                                <div class="rightcnt">
                                <div style="position:relative;">
                                <img class="rightcntimg" src="'.$row["img"].'"/>
                                <div class="articlebtn">
                                    <h3>READ MORE</h3>
                                </div> 
                                </div>
                                <div>
                                <h3 style="color:black;">'.$row["title"].'</h3>
                                <p style="color:black;">'.strip_tags($row["body"]).'</p>
                                </a>
                                <div class="type1"><h5><span class="iconb">Rating: </span><span class="iconc">'.$row["rating"].'</span><img alt="Views" class="icona" src="./assest/img/eye.png"><span class="iconc">'.$row["views"].'</span><img alt="Comments" class="icona" src="./assest/img/comment.png"><span class="iconc">'.$row["comments"].'</span><span class="author1"><a style="text-decoration:none;" href="./profile.php?id='.$row["uid"].'"><img class="icona" alt="Author" src="./assest/img/account.png"><span class="iconc">'.writer_name($row["uid"]).'</span></a><img class="icona" src="./assest/img/clock.png"><span class="iconc">'.time_elapsed_string($row["up_time"]).'</span></span></h5></div>
                                <br><div class="author2"><h5><img class="icona" src="./assest/img/clock.png"><span class="iconc">'.time_elapsed_string($row["up_time"]).'</span></h5><a style="text-decoration:none;" href="./profile.php?id='.$row["uid"].'"><h5><img class="icona" src="./assest/img/account.png"><span class="iconc">'.writer_name($row["uid"]).'</span></h5></a></div>
                                </div>
                                </div>
                                <hr class="hr1">                    
                            ';
                            $y=$y+1;
                        }else{
                            echo '
                                <a style="text-decoration:none;" href="article.php?id='.$row["id"].'">
                                <div class="leftcnt">
                                <div style="position:relative;">
                                <img class="leftcntimg" src="'.$row["img"].'"/>
                                <div class="articlebtn2">
                                    <h3>READ MORE</h3>
                                </div>
                                </div>
                                <div>
                                <h3 style="color:black;">'.$row["title"].'</h3>
                                <p style="color:black;">'.strip_tags($row["body"]).'</p>
                                </a>
                                <div class="type2"><h5><span class="author1"><img class="iconaa" src="./assest/img/clock.png"><span class="iconac">'.time_elapsed_string($row["up_time"]).'</span><a style="text-decoration:none;" href="./profile.php?id='.$row["uid"].'"><img class="iconaa" alt="Author" src="./assest/img/account.png"><span class="iconac">'.writer_name($row["uid"]).'</span></a></span><img alt="Comments" class="iconaa" src="./assest/img/comment.png"><span class="iconac">'.$row["comments"].'</span><img alt="Views" class="iconaa" src="./assest/img/eye.png"><span class="iconac">'.$row["views"].'</span><span class="iconab">Rating: </span><span class="iconac">'.$row["rating"].'</span></h5></div>
                                <br><div class="author3"><h5><img class="iconaa" src="./assest/img/clock.png"><span class="iconac">'.time_elapsed_string($row["up_time"]).'</span><a style="text-decoration:none;" href="./profile.php?id='.$row["uid"].'"><img class="iconaa" src="./assest/img/account.png"><span class="iconac">'.writer_name($row["uid"]).'</span></h5></a></div>
                                </div>
                                </div>
                                <hr class="hr1">
                            ';
                            $y=$y+1;
                        }
                    }
                    pagenav($pages,$prevlink,$nextlink,$start,$end,$total);
                } else {
                    echo '<center><p style="padding-top:20px;">No results could be displayed.</p></center>';
                }
            } else {
                echo '<center><p style="padding-top:20px;">No results could be displayed.</p></center>';
            }
        } catch (Exception $e) {
            echo '<p>', $e->getMessage(), '</p>';
        }
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
    if(!(empty($_GET["page"]))){
        $pagenum=$_GET["page"];

    }else{
        $pagenum=1;

    }

?>
<html>
    <head>
        <meta charset="utf-8">
        <title>Homepage - Kaan ARI</title>
        <link rel="stylesheet" href="./assest/styles/styles_archive.css">
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
                    <li><a class="active" href="./archive.php">Archive</a></li>
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
                <?php paginator($pagenum);?>
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