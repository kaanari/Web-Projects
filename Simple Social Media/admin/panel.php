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

    function last5article(){
        $conn=db_connect();
        $sql = "SELECT * FROM articles ORDER BY id DESC LIMIT 5";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
        // output data of each row
            $y=0;
            while($row = $result->fetch_assoc()) {
                if($y%2 == 0){
                    echo '
                    <tr>
                    <td class="tg-c3ow"><a href="articleedit.php?id='.$row["id"].'"><img class="icona" src="./assest/img/edit.png"></a></td>
                    <td class="tg-c3ow"><a href="articledel.php?id='.$row["id"].'"><img class="icona" src="./assest/img/delete.png"></a></td>
                    <td class="tg-c3ow">'.$row["title"].'</td>
                    <td class="tg-c3ow">'.writer_name($row["uid"]).'</td>
                    <td class="tg-c3ow">'.time_elapsed_string($row["up_time"]).'</td>
                    </tr>
                    ';
                    $y=$y+1;
                }else{
                    echo '
                    <tr>
                    <td class="tg-mxle"><a href="articleedit.php?id='.$row["id"].'"><img class="icona" src="./assest/img/edit.png"></a></td>
                    <td class="tg-mxle"><a href="articledel.php?id='.$row["id"].'"><img class="icona" src="./assest/img/delete.png"></a></td>
                    <td class="tg-mxle">'.$row["title"].'</td>
                    <td class="tg-mxle">'.writer_name($row["uid"]).'</td>
                    <td class="tg-mxle">'.time_elapsed_string($row["up_time"]).'</td>
                    </tr>
                    ';
                    $y=$y+1;
                }
                    
            }
        } else {
            echo "0 results";
        }
    }
    function last5comm(){
        $conn=db_connect();
        $sql = "SELECT * FROM comments ORDER BY id DESC LIMIT 5";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
        // output data of each row
            $y=0;
            while($row = $result->fetch_assoc()) {
                if($y%2 == 0){
                    echo '
                    <tr>
                    <td class="tg-c3ow"><a href="commentedit.php?id='.$row["id"].'"><img class="icona" src="./assest/img/edit.png"></a></td>
                    <td class="tg-c3ow"><a href="commentdel.php?id='.$row["id"].'"><img class="icona" src="./assest/img/delete.png"></a></td>
                    <td class="tg-c3ow">'.writer_name($row["uid"]).'</td>
                    <td class="tg-c3ow">'.time_elapsed_string($row["up_time"]).'</td>
                    </tr>
                    ';
                    $y=$y+1;
                }else{
                    echo '
                    <tr>
                    <td class="tg-mxle"><a href="commentedit.php?id='.$row["id"].'"><img class="icona" src="./assest/img/edit.png"></a></td>
                    <td class="tg-mxle"><a href="commentdel.php?id='.$row["id"].'"><img class="icona" src="./assest/img/delete.png"></a></td>
                    <td class="tg-mxle">'.writer_name($row["uid"]).'</td>
                    <td class="tg-mxle">'.time_elapsed_string($row["up_time"]).'</td>
                    </tr>
                    ';
                    $y=$y+1;
                }
                    
            }
        } else {
            echo "0 results";
        }
    }
    function last5user(){
        $conn=db_connect();
        $sql = "SELECT * FROM users WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 5";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
        // output data of each row
            $y=0;
            while($row = $result->fetch_assoc()) {
                if($y%2 == 0){
                    if(writer_name($row["id"])==$_SESSION["username"]){
                        echo '
                        <tr>
                        <td class="tg-c3ow">-</td>
                        <td class="tg-c3ow">-</td>
                        <td class="tg-c3ow">'.writer_name($row["id"]).'</td>
                        </tr>
                        ';
                    }else{
                        echo '
                        <tr>
                        <td class="tg-c3ow"><a href="useredit.php?id='.$row["id"].'"><img class="icona" src="./assest/img/edit.png"></a></td>
                        <td class="tg-c3ow"><a href="userdel.php?id='.$row["id"].'"><img class="icona" src="./assest/img/delete.png"></a></td>
                        <td class="tg-c3ow">'.writer_name($row["id"]).'</td>
                        </tr>
                        ';
                    }
                    $y=$y+1;
                }else{
                    if(writer_name($row["id"])==$_SESSION["username"]){
                        echo '
                        <tr>
                        <td class="tg-mxle"><a href="useredit.php?id='.$row["id"].'"><img class="icona" src="./assest/img/edit.png"></a></td>
                        <td class="tg-mxle">-</td>
                        <td class="tg-mxle">'.writer_name($row["id"]).'</td>
                        </tr>
                        ';
                    }else{
                        echo '
                        <tr>
                        <td class="tg-mxle"><a href="useredit.php?id='.$row["id"].'"><img class="icona" src="./assest/img/edit.png"></a></td>
                        <td class="tg-mxle"><a href="userdel.php?id='.$row["id"].'"><img class="icona" src="./assest/img/delete.png"></a></td>
                        <td class="tg-mxle">'.writer_name($row["id"]).'</td>
                        </tr>
                        ';
                    }
                    
                    $y=$y+1;
                }
            }
        } else {
            echo "0 results";
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
        die();
    }
?>
<html>
    <head>
        <meta charset="utf-8">
        <title>Ana Sayfa - Kaan ARI</title>
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
                    <li><a class="active" href="./panel.php">Home</a></li>
                    <li><a href="./articles.php">Articles</a></li>
                    <li><a href="./users.php">Users</a></li>
                    <li><a href="./logout.php" style="float:right;">Logout</a>
                    <li><a href="../index.php" style="cursor:pointer; float: right;">Web Page</a></li> 
     
                    </li>
                </ul>
            </div>
            <div class="content">
                <center>
                <table class="tg1" style="undefined;table-layout: fixed; width: 465px">
                    <colgroup>
                        <col style="width:50px;">
                        <col style="width:50px;">
                        <col style="width: 153px">
                        <col style="width: 167px">
                        <col style="width: 145px">
                    </colgroup>
                    <tr>
                        <th class="tg-s0jm" colspan="5">Last 5 Articles</th>
                    </tr>
                    <tr>
                        <td class="tg-ezme"></td>
                        <td class="tg-ezme"></td>
                        <td class="tg-ezme">Title</td>
                        <td class="tg-ezme">Author</td>
                        <td class="tg-ezme">Time</td>
                    </tr>
                    <?php last5article();?>
                </table>
                <hr class="hr1" style="margin-top:20px;margin-bottom:20px;">
                <table class="tg2" style="undefined;table-layout: fixed; width: 363px;float:left;margin-left:20px;">
                    <colgroup>
                        <col style="width: 51px">
                        <col style="width: 51px">
                        <col style="width: 167px">
                        <col style="width: 145px">
                    </colgroup>
                    <tr>
                        <th class="tg-s0jm" colspan="4">Last 5 Comments</th>
                    </tr>
                    <tr>
                        <td class="tg-ezme"></td>
                        <td class="tg-ezme"></td>
                        <td class="tg-ezme">Author</td>
                        <td class="tg-ezme">Time</td>
                    </tr>
                    <?php last5comm(); ?>
                </table>
                <table class="tg1" style="undefined;table-layout: fixed; width: 253px;float:right;margin-right:20px;">
                    <colgroup>
                        <col style="width:50px;">
                        <col style="width:50px;">
                        <col style="width: 153px">
                    </colgroup>
                    <tr>
                        <th class="tg-s0jm" colspan="3">Last 5 Signup</th>
                    </tr>
                    <tr>
                        <td class="tg-ezme"></td>
                        <td class="tg-ezme"></td>
                        <td class="tg-ezme">Username</td>
                    </tr>
                    <?php last5user();?>
                </table>


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