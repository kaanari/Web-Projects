<?php
    session_start();
    function valid_pass($pwd) 
    {
        $r1='/[A-Z]/';  //Uppercase
        $r2='/[a-z]/';  //lowercase
        $r3='/[!@#$%^&()\-_=+{};:,<.>"|]/';  // whatever you mean by 'special char'
        $r4='/[0-9]/';  //numbers
        if(preg_match_all($r1,$pwd, $o)<1) return FALSE;
        if(preg_match_all($r2,$pwd, $o)<1) return FALSE;
        if(preg_match_all($r3,$pwd, $o)>0) return FALSE;
        if(preg_match_all($r4,$pwd, $o)<1) return FALSE;
        if(strlen($pwd)<8) return FALSE;
        return TRUE;
    }


    function valid_name($name)
    {
        $r3='/[!@#$%^&*()\-_=+{};:,<.>]/';
        if(preg_match_all($r3,$name, $o)>0) return FALSE;
        if(strlen($name)<1) return FALSE;
        return TRUE;
    }
    function valid_surname($name)
    {
        $name = preg_replace ("/ +/", "", $name);
        $r3='/[!@#$%^&*()\-_=+{};:,<.>]/';
        if(preg_match_all($r3,$name, $o)>0) return FALSE;
        if(strlen($name)<1) return FALSE;
        return TRUE;
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
    function last_id(){
        $conn=db_connect();

        $result = mysqli_insert_id($conn);
        return $result;
    }
    function valid_username($name)
    {
        $name = preg_replace ("/ +/", "", $name);
        $r3='/[!@#$%^&*()\-_=+{};:,<.>ıüğşçö]/';
        if(preg_match_all($r3,$name, $o)>0) return FALSE;
        if(strlen($name)<5) return FALSE;
        return TRUE;
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
        $stmt->close();
    }
    function image(){
        global $id;
        global $ppimg;
        if ($_FILES['ppimg']['size'] != 0 && $_FILES['ppimg']['error'] == 0){
            $tmp_name=$_FILES['ppimg']["tmp_name"];
            $info = getimagesize($tmp_name);
            $extension = image_type_to_extension($info[2]);;
            $name=$id.$extension;
            $uploads_dir="./usrimg";
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
    function update_userdata($id){
        global $passwordstatus,$newpwdstatus,$usr_info,$buttonstatus,$ppimg;
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT * FROM users WHERE id=?");
        $stmt->bind_param("s",$id);
        $stmt->execute();
        $query = $stmt->get_result();
        $result=$query->fetch_assoc();
        $stmt->close();
            
        if(!(empty($_POST["username"]) && valid_username($_POST["username"]))){
            $newusername=$_POST["username"];
        }else{
            $newusername=$usr_info["username"];
        }
        if (!(empty($_POST["email"])) && filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            $newmail=$_POST["email"];
        }else{
            $newmail=$usr_info["email"];
        }
            if(!(empty($_POST["name"]))){
                $name=$_POST["name"];
            }else{
                $name=$usr_info["usr_name"];
            }
            if(!(empty($_POST["surname"]))){
                $surname=$_POST["surname"];
            }else{
                $surname=$usr_info["usr_surname"];
            }
            if(!(empty($_POST["gender"]))){
                $gender=$_POST["gender"];
            }else{
                $gender=$usr_info["gender"];
            }
            if(!(empty($_POST["bdate"]))){
                $bdate=$_POST["bdate"];
            }else{
                $bdate=$usr_info["bdate"];
            }
            if(!(empty($_POST["usrtel"]))){
                $usrtel=$_POST["usrtel"];
            }else{
                $usrtel=$usr_info["usr_phone"];
            }
            if(!(empty($_POST["country"]))){
                $country=$_POST["country"];
            }else{
                $country=$usr_info["country"];
            }
            
            if($name!=$usr_info["usr_name"] && !(empty($name)) && valid_name($name)){
                $newname=$name;
            }else{
                $newname=$usr_info["usr_name"];
            }

            if($surname!=$usr_info["usr_surname"] && !(empty($surname)) && valid_name($surname)){
                $newsurname=$surname;
            }else{
                $newsurname=$usr_info["usr_surname"];
            }

            if($bdate!=$usr_info["bdate"] && !(empty($bdate))){
                $newbdate=$bdate;
            }else{
                $newbdate=$usr_info["bdate"];
            }

            if($gender!=$usr_info["gender"] && !(empty($gender))){
                $newgender=$gender;
            }else{
                $newgender=$usr_info["gender"];
            }

            if($country!=$usr_info["country"]){
                $newcountry=$country;
            }else{
                $newcountry=$usr_info["country"];
            }
            if($usrtel!=$usr_info["usr_phone"]){
                $newusrtel=$usrtel;
            }else{
                $newusrtel=$usr_info["usr_phone"];
            }


            if(!(empty($_POST["newpwd"])) || !(empty($_POST["renewpwd"]))){
                if(!(empty($_POST["newpwd"])) && !(empty($_POST["renewpwd"]))){
                    if($_POST["newpwd"]==$_POST["renewpwd"]){
                        if(valid_pass($_POST["newpwd"])){
                            $newpwd=password_hash($_POST["newpwd"], PASSWORD_DEFAULT);
                        }else{
                            $newpwdstatus="New Password is no valid.";
                        }
                    }else{
                        $newpwdstatus="Passwords are not same.";
                    }
                }else{
                    $newpwdstatus="This field can not be empty.";
                }
            }else{
                $newpwd=$usr_info["pwd"];
            }
            $conn=db_connect();
            if(image()){
                $newppimg=$ppimg;
                $stmt = $conn->prepare("UPDATE users SET username=?, email=?, pwd=?, usr_name=?, usr_surname=?, gender=?, bdate=?, usr_phone=?, country=?, pimg=? WHERE id=?");
            $stmt->bind_param("ssssssssssi",$newusername,$newmail,$newpwd,$newname,$newsurname,$newgender,$newbdate,$newusrtel,$newcountry,$newppimg,$id);
            }else{
                $stmt = $conn->prepare("UPDATE users SET username=?, email=?, pwd=?, usr_name=?, usr_surname=?, gender=?, bdate=?, usr_phone=?, country=? WHERE id=?");
                $stmt->bind_param("sssssssssi",$newusername,$newmail,$newpwd,$newname,$newsurname,$newgender,$newbdate,$newusrtel,$newcountry,$id);
            }
            
            
            if ($stmt->execute()) {
                $buttonstatus="SUCCES";
                $stmt->close();
            } else {
                echo "Error: ".$stmt->error;
                die();
            }
    }

    function pagenav($pages,$prevlink,$nextlink,$start,$end,$total){
        global $artpagenum;
        if(!(empty($_GET["comm"]))){
            $pagenum=$_GET["comm"];
        }else{
            $pagenum=1;
        }
        $lineone= '<center>
                <div class="paging">
                    <form action="useredit.php" method="GET" style="line-height:30px;vertical-align:bottom;">

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
                    $aa='<option value=./useredit.php?id='.$_GET["id"].'&comm='.$i.'&art='.$artpagenum.'>'.$i.'</option>';
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
        #echo '<div class="paging"><center><p style="height:30px;margin-left:70px;margin-right:70px;">'.$line1.$line2.'</p></center></div>';
    }
    function arttitle($id){
        $conn=db_connect();
        $stmt=$conn->prepare("SELECT title FROM articles WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $query = $stmt->get_result();
        $result=$query->fetch_assoc();
        return $result["title"];
    }
    function paginator($pagenum){
        try{
            global $artpagenum;
            $conn=db_connect();
            $total = $conn->query('SELECT * FROM comments WHERE uid="'.$_GET["id"].'"')->num_rows;
            $limit = 5;
            $pages = ceil($total / $limit);
            #$page = min($pages, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default'   => 1,'min_range' => 1,),)));
            $page=$pagenum;
            $offset = ($page - 1)  * $limit;
            $start = $offset + 1;
            $end = min(($offset + $limit), $total);
            $prevlink = ($page > 1) ? '<a href="?id='.$_GET["id"].'&comm=1&art='.$artpagenum.'" title="First page" style="text-decoration:none;"><img class="pagenavicon" src="./assest/img/double_left.png"></a> <a href="?id='.$_GET["id"].'&comm=' . ($page - 1) . '&art='.$artpagenum.'" title="Previous page" style="text-decoration:none;"><img class="pagenavicon" src="./assest/img/left.png"></a>' : '<span class="disabled"><img class="pagenavicon" src="./assest/img/double_left.png"></span> <span class="disabled"><img class="pagenavicon" src="./assest/img/left.png"></span>';
            $nextlink = ($page < $pages) ? '<a href="?id='.$_GET["id"].'&comm=' . ($page + 1) . '&art='.$artpagenum.'" title="Next page" style="text-decoration:none;"><img class="pagenavicon" src="./assest/img/right.png"></a> <a href="?id='.$_GET["id"].'&comm=' . $pages . '&art='.$artpagenum.'" title="Last page" style="text-decoration:none;"><img class="pagenavicon" src="./assest/img/double_right.png"></a>' : '<span class="disabled">&rsaquo;</span> <span class="disabled">&raquo;</span>';
            $stmt = $conn->query('SELECT * FROM comments WHERE uid="'.$_GET["id"].'" ORDER BY id desc LIMIT '.$limit.' OFFSET '.$offset.'');
            
            if(!(empty($page))){
                if ($stmt->num_rows > 0) {
                    $iterator = new IteratorIterator($stmt);
                    $y=0;
                    echo'<center><table class="tg" style="undefined;table-layout: fixed; width: 551px">
                    <colgroup>
                        <col style="width: 59px">
                        <col style="width: 220px">
                        <col style="width: 228px">
                        <col style="width: 44px">
                        <col style="width: 50px">
                        <col style="width: 50px">
                    </colgroup>
                    <tr>
                        <th class="tg-amwm">Article ID</th>
                        <th class="tg-amwm">Article Title</th>
                        <th class="tg-amwm">Comment</th>
                        <th class="tg-amwm">Vote</th>
                        <th class="tg-amwm">Edit</th>
                        <th class="tg-amwm">Delete</th>
                    </tr>
                    ';
                    foreach ($iterator as $row) {
                        echo'
                        <tr>
                            <td ><center>'.$row["artid"].'</center></td>
                            <td ><center>'.arttitle($row["artid"]).'</center></td>
                            <td><center>'.$row["comment"].'</center></td>
                            <td><center>'.$row["vote"].'</center></td>
                            <td><center><a href="commentedit.php?id='.$row["id"].'"><img class="icona" src="./assest/img/edit.png"></a></center></td>
                            <td><center><a href="commentdel.php?id='.$row["id"].'"><img class="icona" src="./assest/img/delete.png"></a></center></td>
                        </tr>
                        ';
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

    function pagenav2($pages,$prevlink,$nextlink,$start,$end,$total){
        global $pagenum;
        if(!(empty($_GET["art"]))){
            $artpagenum=$_GET["art"];
        }else{
            $artpagenum=1;
        }
        $lineone= '<center>
                <div class="paging">
                    <form action="useredit.php" method="GET" style="line-height:30px;vertical-align:bottom;">

                    <p><span class="navhide">'.$prevlink.'</span><span class="pagetext"> Page ';
        if($pages>1){
            $ab="";
            $linetwo='
            <select class="pageselect" onchange="if (this.value) window.location.href=this.value">
            ';
            for($i = 1; $i <= $pages; $i++){
                if($i==$artpagenum){
                    $aa='<option style="user-select:none;" value='.$i.' selected>'.$i.'</option>';
                }else{
                    $aa='<option value=./useredit.php?id='.$_GET["id"].'&comm='.$pagenum.'&art='.$i.'>'.$i.'</option>';
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
        #echo '<div class="paging"><center><p style="height:30px;margin-left:70px;margin-right:70px;">'.$line1.$line2.'</p></center></div>';
    }
    function paginator2($artpagenum){
        try{
            global $pagenum;
            $conn=db_connect();
            $total = $conn->query('SELECT * FROM articles WHERE uid="'.$_GET["id"].'"')->num_rows;
            $limit = 5;
            $pages = ceil($total / $limit);
            #$page = min($pages, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default'   => 1,'min_range' => 1,),)));
            $page=$artpagenum;
            $offset = ($page - 1)  * $limit;
            $start = $offset + 1;
            $end = min(($offset + $limit), $total);
            $prevlink = ($page > 1) ? '<a href="?id='.$_GET["id"].'&comm='.$pagenum.'&art=1" title="First page" style="text-decoration:none;"><img class="pagenavicon" src="./assest/img/double_left.png"></a> <a href="?id='.$_GET["id"].'&comm='.$pagenum.'&art=' . ($page - 1) . '" title="Previous page" style="text-decoration:none;"><img class="pagenavicon" src="./assest/img/left.png"></a>' : '<span class="disabled"><img class="pagenavicon" src="./assest/img/double_left.png"></span> <span class="disabled"><img class="pagenavicon" src="./assest/img/left.png"></span>';
            $nextlink = ($page < $pages) ? '<a href="?id='.$_GET["id"].'&comm='.$pagenum.'&art=' . ($page + 1) . '" title="Next page" style="text-decoration:none;"><img class="pagenavicon" src="./assest/img/right.png"></a> <a href="?id='.$_GET["id"].'&comm='.$pagenum.'&art=' . $pages . '" title="Last page" style="text-decoration:none;"><img class="pagenavicon" src="./assest/img/double_right.png"></a>' : '<span class="disabled">&rsaquo;</span> <span class="disabled">&raquo;</span>';
            $stmt = $conn->query('SELECT * FROM articles WHERE uid="'.$_GET["id"].'" ORDER BY id desc LIMIT '.$limit.' OFFSET '.$offset.'');
            
            if(!(empty($page))){
                if ($stmt->num_rows > 0) {
                    $iterator = new IteratorIterator($stmt);
                    $y=0;
                    echo'<center><table class="tg" style="undefined;table-layout: fixed; width: 722px">
                    <colgroup>
                    <col style="width: 30px">
                    <col style="width: 161px">
                    <col style="width: 56px">
                    <col style="width: 86px">
                    <col style="width: 56px">
                    <col style="width: 50px">
                    <col style="width: 50px">
                    </colgroup>
                      <tr>
                        <th class="tg-amwm">ID</th>
                        <th class="tg-amwm">Article Title</th>
                        <th class="tg-amwm">Views</th>
                        <th class="tg-amwm">Comments</th>
                        <th class="tg-amwm">Rating</th>
                        <th class="tg-amwm">Edit</th>
                        <th class="tg-amwm">Delete</th>
                      </tr>
                    ';
                    foreach ($iterator as $row) {
                        echo'
                        <tr>
                            <td ><center>'.$row["id"].'</center></td>
                            <td ><center>'.$row["title"].'</center></td>
                            <td><center>'.$row["views"].'</center></td>
                            <td><center>'.$row["comments"].'</center></td>
                            <td><center>'.$row["rating"].'</center></td>
                            <td><center><a href="articleedit.php?id='.$row["id"].'"><img class="icona" src="./assest/img/edit.png"></a></center></td>
                            <td><center><a href="articledel.php?id='.$row["id"].'"><img class="icona" src="./assest/img/delete.png"></a></center></td>
                        </tr>
                        ';
                    }
                    echo'</table></center>';
                    pagenav2($pages,$prevlink,$nextlink,$start,$end,$total);
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

    $buttonstatus="SAVE";
    $passwordstatus="Enter Password";
    $last_article=last_id();
    $cookie=cookie_control();
    if($cookie){
        $conn=db_connect();
        $id=$_GET["id"];
        getinfo($id);
        if($_POST){
            update_userdata($id);
            getinfo($id);
        }
        if(!(empty($_GET["comm"]))){
            $pagenum=$_GET["comm"];
        }else{
            $pagenum=1;
        }
        if(!(empty($_GET["art"]))){
            $artpagenum=$_GET["art"];
        }
        else{
            $artpagenum=1;
        }
    }else{
        header("Location: ./index.php");                                                                
        die();  
    }
?>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo($usr_info["username"]);?>'s Profile</title>
        <link rel="stylesheet" href="./assest/styles/styles_profilepanel.css">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    
    <body>
        <div class="wrapper">
            <header>
                <div  class="header"></div>
            </header>
            <div class="navbar">
                <ul>
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
            <form action="./useredit.php?id=<?php echo($id);?>" method="POST"  enctype="multipart/form-data">
                <div class="form1" style="user-select:none;">
                        <label><center><b style="color:darkred;">Account Information:</b></center></label><br>
                        <label><b>Username </b> </label>
                        <input class="inp" type="text" name="username" value="<?php echo($usr_info["username"]);?>">
                        <label><b>New Password </b></label>
                        <input class="inp" type="password" name="newpwd" placeholder="Enter New Password">
                        <label><b>Retype New Password </span></b></label>
                        <input class="inp" type="password" name="renewpwd" placeholder="Enter New Password">
                        <label><b>E-mail </b></label>
                        <input class="inp" type="email" name="email" value="<?php echo($usr_info["email"]);?>">
                        <br>
                        
                        
                </div>
                <hr class="a">
                <div class="form1">
                        <label><center><b style="color:darkred;">Personal Information:</b></center></label><br>
                        <label><b>Name </b></label>
                        <input class="inp" type="text" name="name" value="<?php echo($usr_info["usr_name"]);?>" required>
                        <label><b>Surname </b></label>
                        <input class="inp" type="text" name="surname" value="<?php echo($usr_info["usr_surname"]);?>" required>
                        <label><b>Gender </span></b></label><br>
                        <?php
                            if($usr_info["gender"]=="Male"){
                                echo'
                                    <span class="gender">
                                    <input type="radio" name="gender" value="Male" checked> Male
                                    <input type="radio" name="gender" value="Female"> Female  
                                    <input type="radio" name="gender" value="Other"> Other
                                    </span>
                                ';
                            }
                            if($usr_info["gender"]=="Female"){
                                echo'
                                    <span class="gender">
                                    <input type="radio" name="gender" value="Male"> Male
                                    <input type="radio" name="gender" value="Female" checked> Female  
                                    <input type="radio" name="gender" value="Other"> Other
                                    </span>
                                ';
                            }
                            if($usr_info["gender"]=="Other"){
                                echo'
                                <span class="gender">
                                <input type="radio" name="gender" value="Male"> Male
                                <input type="radio" name="gender" value="Female"> Female  
                                <input type="radio" name="gender" value="Other" checked> Other
                                </span>
                            ';
                            }
                        
                        ?>
                        <br>
                        <label><b>Birthday </b></label>
                        <input class="inp" type="text" value="<?php echo($usr_info["bdate"]);?>" onfocus="(this.type='date')" onblur="(this.type='text')" id="date" name="bdate" required>
                        <label><b>Tel Number <span style="color:darkred;"></span></b></label>
                        <input class="inp" type="text" name="usrtel" placeholder="Enter Phone Number" value="<?php echo($usr_info["usr_phone"]);?>">
                        <label><b>Country</b></label>
                        <input class="inp" type="text" placeholder="Enter Country" name="country" value="<?php echo($usr_info["country"]);?>">                      
                        <br>
                        
                        
                </div>
                <hr class="a">
                <div class="form1">
                        <label><center><b style="color:darkred;">Additional Information:</b></center></label><br>
                        <label><b>Profile Photo</b></label>
                        <center><div style="background-image:url(.<?php echo($usr_info["pimg"]) ?>);border-radius:10px;margin-top:10px;height:175px;width:150px;overflow:hidden;background-position:center;background-repeat:no-repeat;background-size:cover;"></div></center><br>
                        <center><label class="uploadbtn" for="ppimg">Browse...</label></center>
                        <input style="z-index:-1; position:absolute; opacity:0;" type="file" name="ppimg" id="ppimg" accept=".jpg, .jpeg, .png">
                        <input class="sgninbtn" type="submit" value="<?php echo($buttonstatus);?>" onfocus="(this.value='SAVE')">
                        <br>
                        
                </div>
                </form>
                
            </div>
            <div class="content3">
                <center>
                <h4>Articles written by <span style="color:darkred;"><?php echo($usr_info["username"]); ?></span></h4>
                </center>
                <?php paginator2($artpagenum);?>
            </div>
            <div class="content2">
                <center>
                <h4>Comments written by <span style="color:darkred;"><?php echo($usr_info["username"]); ?></span></h4>
                </center>
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