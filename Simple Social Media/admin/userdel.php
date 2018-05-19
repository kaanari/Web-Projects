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
    function writer_name($id){
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT username FROM users WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $query = $stmt->get_result();
        $result=$query->fetch_assoc();
        return $result["username"];
    }
    function article($id){
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT * FROM articles WHERE uid=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $stmt->store_result();
        $query = $stmt->get_result();
        $result=$stmt->num_rows;
        return $result;
    }
    function delall(){
        $conn=db_connect();
        $stmt=$conn->prepare("DELETE FROM articles WHERE uid=?");
        $stmt->bind_param("i",$_GET["id"]);
        $stmt->execute();
        $stmt=$conn->prepare("DELETE FROM comments WHERE uid=?");
        $stmt->bind_param("i",$_GET["id"]);
        $stmt->execute();
    }

    function comment($id){
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT * FROM comments WHERE uid=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $stmt->store_result();
        $query = $stmt->get_result();
        $result=$stmt->num_rows;
        return $result;
    }
    function changeall(){

        $conn=db_connect();
        $stmt = $conn->prepare("UPDATE users SET deleted_at=NOW() WHERE id=?");
        $stmt->bind_param("i",$_GET["id"]);
        $stmt->execute();
    }
    function del(){
        $conn=db_connect();
        $stmt=$conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i",$_GET["id"]);
        $stmt->execute();
    }

    if(cookie_control()){
        if($_SERVER["HTTP_REFERER"]!="http://localhost/deneme/admin/userdel.php?id={$_GET["id"]}"){
            $url=$_SERVER["HTTP_REFERER"];
            $_SESSION["url"]=$url;
        }
        if(isset($_POST["del"])){
            if($_POST["del"]=="YES"){
                if(isset($_POST["delall"])){
                    del();                    
                    delall();
                }else{
                    changeall();
                }
                header("Location: ".$_SESSION["url"]);
                unset($_SESSION["url"]);
                die();
            }else if($_POST["del"]=="NO"){
                header("Location: ".$_SESSION["url"]);
                unset($_SESSION["url"]);
                die();
            }
        }
    }else{
        header("Location: ./index.php");
        die();
    }
?>
<html>
    <head>
        <meta charset="utf-8">
        <title>WARNING !</title>
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
                    <li><a href="./panel.php">Home</a></li>
                    <li><a href="./articles.php">Articles</a></li>
                    <li><a href="./users.php">Users</a></li>
                    <li><a href="./logout.php" style="float:right;">Logout</a>
                    <li><a href="../index.php" style="cursor:pointer; float: right;">Web Page</a></li> 
                </ul>
            </div>


            <div class="content">
                <form action="#" method="POST">
                <center>
                <div style="color:black;margin-top:30px;">
                <span style="color:red"><b>!! WARNING !!</b></span><br><br>
                <h4>Are you really want to delete user?<br><br><span style="color:red"><?php echo(writer_name($_GET["id"])); ?></span></h4>
                <h4>  <input type="checkbox" name="delall" style="display:inline-block;height:20px;width:20px;margin-right:20px;background-color:#333;">Do you want to delete <?php echo(writer_name($_GET["id"]));?>'s all comments[<span style="color:red;"><?php echo(comment($_GET["id"])); ?></span>] and articles[<span style="color:red;"><?php echo(article($_GET["id"])); ?></span>]?</h4>
                <input class="sgninbtn" name="del" type="submit" value="NO" onfocus="(this.value='NO')"> <input class="sgninbtn2" name="del" type="submit" value="YES" onfocus="(this.value='YES')">
                </div>
                </center>
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