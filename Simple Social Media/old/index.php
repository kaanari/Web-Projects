<html>

    <head>
        <title>Ana Sayfa</title>
        <link rel="stylesheet" href="style.css">
        <meta charset="utf-8">
    </head>
    <?php
        $baglanti = @mysql_connect('localhost', 'kaanari1_kaan', 'hwworld');
        $veritabani = @mysql_select_db('kaanari1_hw1');


        function cookie()
        {
            $cookie=$_COOKIE["auth"];
            $read = mysql_query("SELECT userid FROM cookie WHERE auth='".$cookie."'");
            $list = mysql_fetch_array($read); 
            if(empty($list)){
                setcookie("auth","", time()-3600);          
            }else{
                header("Location: ./profile.php?profile=$list[0]");
            }
        }
    
        function kayit()
        {
            if(empty($_POST["username"]) or empty($_POST["pwd"]) or empty($_POST["name"]) or empty($_POST["surname"]) or empty($_POST["email"]) or (strpos($_POST["username"], ' ') !== false) or (strpos($_POST["pwd"], ' ') !== false) or (strpos($_POST["email"], ' ') !== false)){
                echo ("<script type='text/javascript'>  alert('Please fill all the blanks'); </script>"); 
            }else{
                $username=$_POST["username"];
                $pwd=password_hash($_POST["pwd"], PASSWORD_DEFAULT);
                $name=$_POST["name"];
                $surname=$_POST["surname"];
                $email=$_POST["email"];
                $read = mysql_query("SELECT id FROM users WHERE username='".$username."'");
                $list = mysql_fetch_array($read);
                if(empty($list))
                {
                    mysql_query("INSERT INTO users (username, pwd,names,surname,email) VALUES ('$username', '$pwd', '$name', '$surname', '$email')");
                    $auth=random_key();
                    $read = mysql_query("SELECT id FROM users WHERE username='".$username."'");
                    $list = mysql_fetch_array($read);
                    $userid=$list[0];
                    mysql_query("INSERT INTO cookie (userid, auth) VALUES ('$userid', '$auth')");
                    setcookie("auth","$auth", time()+3600);
                    header("Location: ./profile.php?profile=$userid");
                }else{
                    echo ("<script type='text/javascript'>  alert('$username is already exist.'); </script>"); 
                }
            }
        }

        function giris()
        {
            if(empty($_POST["username"]) or empty($_POST["pwd"])){
                echo("<script type='text/javascript'>  alert('Enter Username and Password'); </script>"); 
            }else{
                $read = mysql_query("select id,pwd from users where username='".$_POST["username"]."'");
                $list = mysql_fetch_array($read);
                $userid2=$list[0];
                if(empty($list)){
                    echo ("<script type='text/javascript'>  alert('Wrong Username or Password'); </script>"); 
                }else{
                    if(password_verify($_POST["pwd"], $list[1])){
                        $read2 = mysql_query("select id,userid,auth from cookie where userid='".$userid2."'");
                        $list2 = mysql_fetch_array($read2); 
                        if(empty($list2)){
                            $auth=random_key();
                            mysql_query("INSERT INTO cookie (userid, auth) VALUES ('$userid2', '$auth')");
                            setcookie("auth","$auth", time()+3600);
                            header("Refresh: 0;");
                        }else{
                            $auth=random_key();
                            mysql_query("UPDATE cookie SET auth='$auth' WHERE userid=$userid2");
                            setcookie("auth","$auth", time()+3600);
                            header("Refresh: 0;");
                        }
                    }else{
                        echo ("<script type='text/javascript'>  alert('Wrong Username or Password'); </script>"); 
                    }
                }
            }
        }

        function random_key($str_length = 24)
        {
            $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $bytes = openssl_random_pseudo_bytes(3*$str_length/4+1);
            $repl = unpack('C2', $bytes);
            $first  = $chars[$repl[1]%62];
            $second = $chars[$repl[2]%62];
    
            return strtr(substr(base64_encode($bytes), 0, $str_length), '+/', "$first$second");
        }
        $status=$_POST["type"];
        if(empty($_COOKIE["auth"])){
            if(empty($status)){

        
            } else{
                if($status=="Login"){ //Üye girişi varsa
                    giris();
                }
                if($status=="Signup"){ //Kayıt varsa

                    kayit();
                } else{
                    http_response_code(400);
                }
            }
        }else{
            cookie();
        }
    ?> 


    <body>
        <div class="nav">
            <header>
                <nav>
                    <ul>
                        <li><a href="./index.php">Home</a></li>
                        <li><a href="./profile.php">Profile</a></li>
                    </ul>    
                </nav>
            </header>
            
        </div>
        <center><h2 class="text">Login or Signup</h2></center>
        <div style="margin:auto;width:80%;margin-top:5%;">
            <div class="box" style="margin-top:35px;float:left;padding-top:7%; height:32.5%;min-height:150px;margin-right:35%;">
                <form action="" method="post">
                    <center>
                        <label>Username</label><br>
                        <input class="textbox" type="text" name="username"><br>
                        <label>Password</label><br>
                        <input class="textbox" type="password" name="pwd"><br>
                        <input class="button" type="submit" name ="type" value="Login">
                    </center>
                </form>
            </div>
            <div class="box" style="float:right;">
                    <form action="" method="post">
                        <center>
                            <?php
                                if($_POST["type"]==Signup){
                                    $username=$_POST["username"];
                                    $name=$_POST["name"];
                                    $surname=$_POST["surname"];
                                    $email=$_POST["email"];
                                }
                                echo("<label>Name</label><br>");
                                echo("<input class='textbox' type='text' name='name' value='$name'><br>");
                                echo("<label>Surname</label><br>");
                                echo("<input class='textbox' type='text' name='surname' value='$surname'><br>");
                                echo("<label>E-Mail</label><br>");
                                echo("<input class='textbox' type='text' name='email' value='$email'><br>");
                                echo("<label>Username</label><br>");
                                echo("<input class='textbox' type='text' name='username' value='$username'><br>");
                                echo("<label>Password</label><br>");
                                echo("<input class='textbox' type='password' name='pwd'><br>");
                                echo("<input class='button' type='submit' name='type' value='Signup'><br>");
                            ?>
                        </center>
                    </form>
                </div>
        </div>

    </body>
</html>
