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
                    <form action="articles.php" method="GET" style="line-height:30px;vertical-align:bottom;">

                    <p><span class="navhide">'.$prevlink.'</span><span class="pagetext"> Page ';
        if($pages>1){
            $ab="";
            $linetwo='
            <select class="pageselect" onchange="if (this.value) window.location.href=this.value">
            ';
            for($i = 1; $i <= $pages; $i++){
                if($i==$pagenum){
                    $aa='<option style="user-select:none;" value='.$i.' selected>'.$i.'</option>';
                }else{
                    $aa='<option value=./articles.php?page='.$i.'>'.$i.'</option>';
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
        echo '<div class="paging"><center><p style="height:30px;margin-left:70px;margin-right:70px;">'.$line1.$line2.'</p></center></div>';
    }
    function paginator($pagenum){
        try{
            global $id;
            $conn=db_connect();
            $total = $conn->query('SELECT * FROM users WHERE deleted_at')->num_rows;
            $limit = 10;
            $pages = ceil($total / $limit);
            #$page = min($pages, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default'   => 1,'min_range' => 1,),)));
            $page=$pagenum;
            $offset = ($page - 1)  * $limit;
            $start = $offset + 1;
            $end = min(($offset + $limit), $total);
            $prevlink = ($page > 1) ? '<a href="?page=1" title="First page" style="text-decoration:none;"><img class="pagenavicon" src="./assest/img/double_left.png"></a> <a href="?page=' . ($page - 1) . '" title="Previous page" style="text-decoration:none;"><img class="pagenavicon" src="./assest/img/left.png"></a>' : '<span class="disabled"><img class="pagenavicon" src="./assest/img/double_left.png"></span> <span class="disabled"><img class="pagenavicon" src="./assest/img/left.png"></span>';
            $nextlink = ($page < $pages) ? '<a href="?page=' . ($page + 1) . '" title="Next page" style="text-decoration:none;"><img class="pagenavicon" src="./assest/img/right.png"></a> <a href="?page=' . $pages . '" title="Last page" style="text-decoration:none;"><img class="pagenavicon" src="./assest/img/double_right.png"></a>' : '<span class="disabled">&rsaquo;</span> <span class="disabled">&raquo;</span>';
            $stmt = $conn->query('SELECT * FROM users ORDER BY id desc LIMIT '.$limit.' OFFSET '.$offset.'');
            echo'<center><h4 style="margin-bottom:20px;">Users Panel</h4></center>';
            if(!(empty($page))){
                if ($stmt->num_rows > 0) {
                    $iterator = new IteratorIterator($stmt);
                    $y=0;
                    echo'
                    <center>
                        <table class="tg" style="undefined;table-layout: fixed; width: 722px">
                            <colgroup>
                            <col style="width: 30px">
                            <col style="width: 100px">
                            <col style="width: 80px">
                            <col style="width: 86px">
                            <col style="width: 161px">
                            <col style="width: 70px">
                            <col style="width: 40px">
                            <col style="width: 45px">
                            </colgroup>
                            <tr>
                                <th class="tg-amwm">ID</th>
                                <th class="tg-amwm">Username</th>
                                <th class="tg-amwm">Name</th>
                                <th class="tg-amwm">Surname</th>
                                <th class="tg-amwm">E-mail</th>
                                <th class="tg-amwm">Comments</th>
                                <th class="tg-amwm">Edit</th>
                                <th class="tg-amwm">Delete</th>
                            </tr>
                    ';
                    foreach ($iterator as $row) {
                        if(is_null($row["deleted_at"])){
                            if($id==$row["id"]){
                                echo'
                                    <tr>
                                        <td ><center>'.$row["id"].'</center></td>
                                        <td ><center>'.$row["username"].'</center></td>
                                        <td><center>'.$row["usr_name"].'</center></td>
                                        <td><center>'.$row["usr_surname"].'</center></td>
                                        <td><center>'.$row["email"].'</center></td>
                                        <td><center>5</center></td>
                                        <td><center><a href="useredit.php?id='.$row["id"].'"><img class="icona" src="./assest/img/edit.png"></a></center></td>
                                        <td><center> - </center></td>
                                    </tr>
                                ';
                            }else{
                                echo'
                                    <tr>
                                        <td ><center>'.$row["id"].'</center></td>
                                        <td ><center>'.$row["username"].'</center></td>
                                        <td><center>'.$row["usr_name"].'</center></td>
                                        <td><center>'.$row["usr_surname"].'</center></td>
                                        <td><center>'.$row["email"].'</center></td>
                                        <td><center>5</center></td>
                                        <td><center><a href="useredit.php?id='.$row["id"].'"><img class="icona" src="./assest/img/edit.png"></a></center></td>
                                        <td><center><a href="userdel.php?id='.$row["id"].'"><img class="icona" src="./assest/img/delete.png"></a></center></td>
                                    </tr>
                                ';
                            }
                        }else{
                            echo'
                                <tr>
                                    <td><center>'.$row["id"].'</center></td>
                                    <td><center><span style="color:red;"><del>'.$row["username"].'</del></span></center></td>
                                    <td><center>'.$row["usr_name"].'</center></td>
                                    <td><center>'.$row["usr_surname"].'</center></td>
                                    <td><center>'.$row["email"].'</center></td>
                                    <td><center>5</center></td>
                                    <td><center><a href="useredit.php?id='.$row["id"].'"><img class="icona" src="./assest/img/edit.png"></a></center></td>
                                    <td><center><a href="userundel.php?id='.$row["id"].'"><img class="icona" src="./assest/img/restore.png"></a></center></td>
                                </tr>
                            ';
                        }
                    }
                    echo'</table></center>';
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
    if($cookie){
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT id FROM users WHERE username=?");
        $stmt->bind_param("s",$_SESSION["username"]);
        $stmt->execute();
        $query = $stmt->get_result();
        $result=$query->fetch_assoc();
        $id=$result["id"];
    }else{
        header("Location: ./index.php");
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
        <title>Users - ADMIN PANEL</title>
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
                    <li><a href="./panel.php">Home</a></li>
                    <li><a href="./articles.php">Articles</a></li>
                    <li><a class="active" href="./users.php">Users</a></li>
                    <li><a href="./logout.php" style="float:right;">Logout</a>
                    <li><a href="../index.php" style="cursor:pointer; float: right;">Web Page</a></li> 
     
                    </li>
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