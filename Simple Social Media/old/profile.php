<html>
    <?php
        function quote(){
            $allqts = array
            ("The Black Knight Always Triumphs!", 
            "Monty Python",
            "I swear by my life and love of it that I will never live for the sake of<br>another man, nor ask another man to live for mine" ,
            "Atlas Shrugged",
            "It is clear that the individual who persecutes a man, his brother,<br> because he is not of the same opinion, is a monster",
            "Voltaire",
            "I agree that there is a natural aristocracy among men<br>The grounds of this are virtue and talents.",
            "Thomas Jefferson",
            "Liberty, when it begins to take root, is a plant of rapid growth.", 
            "George Washington",
            "Never argue with an idiot. <br>They drag you down to their level <br>then beat you with experience",
            "Dilbert",
            "The Answer is 42. What is the question?",
            "Hitchikers Guide to the Galaxy",
            "Anyone who has never made a mistake has never tried anything new",
            "Albert Einstein",
            "Progress doesn't come from early risers, progress is made<br>by lazy men looking for easier ways to do things.",
            "Lazarus Long <font size=-2>(Time Enough for Love by Robert A. Heinlein)</font>",
            "Throughout history, poverty is the normal condition of man. <br>Advances which permit this norm to be exceeded - here and there, now and then - <br>are the work of an extremely small minority, frequently despised,<br> often condemned, and almost always opposed by all right-thinking people. <br>Whenever this tiny minority is kept from creating, or (as sometimes happens)<br> is driven out of a society, the people then slip back into abject poverty.<br><br>This is known as 'bad luck.'",
            "Robert Heinlein",
            "A little learning is a dangerous thing; Drink deep, or taste not the<br>Pierian spring.  There shallow draughts intoxicate the brain, <br>and drinking largely sobers us again",
            "Alexander Pope",
            "The early bird gets the worm, but the second mouse gets the cheese",
            "Anonymous",
            "Subjugating the enemy's army without fighting is the true pinnacle of excellence",
            "Sun-tzu, The Art of War",
            "Work as though you were to live 100 years; pray as if you were to die tomorrow",
            "Benjamin Franklin",
            "The world is a stage, but the play is badly cast",
            "Oscar Wilde",
            "Truth is generally the best vindication against slander.",
            "Abraham Lincoln",
            "...mercy to the guilty is cruelty to the innocent...",
            "Adam Smith",
            "...I wish that I may never think the smiles of the great and powerful<br> a sufficient inducement to turn aside from the straight path<br> of honesty and the convictions of my own mind",
            "David Ricardo",
            "Democracy is the worst form of government except for all the others",
            "Winston Churchill",
            "You can only know the highest peaks if you have experianced the lowest valley's",
                "Richard Nixon",
            "They dress the wound of my people as though it were not serious. <br>'Peace, peace,' they say, when there is no peace.",
            "Jeremiah 6:14",
            "It is better to remain silent and be thought a fool<br> than to open your mouth and remove all doubt.",
            "Jonathan Swift",
            "The market system delivers the goods people want,<br>but those who make it work cannot readily explain why it is so.<br>The socialst or communist system does not deliver the goods, <br>but those who operate it can readily explain away its failure.",
            "F.A. Hayek, Law, Legislation and Liberty, Vol. II",
            "Never Stop Exploring<sup>tm</sup>",
            "The North Face"
            );
            $totalqts = (count($allqts)/2);
            $nmbr = (rand(0,($totalqts-1)));
            $nmbr = $nmbr*2;
            $quote = $allqts[$nmbr];
            $nmbr = $nmbr+1;
            $author = $allqts[$nmbr];
            $result=array($quote,$author);
            return $result;
        }

        if(empty($_GET)){
            echo ("<script type='text/javascript'>  alert('You dont have permission for this page.'); </script>"); 
            header("Refresh: 1; url=./");

        }else{
            if($_GET["state"]==1){
                setcookie("auth","", time()-3600);
                echo ("<script type='text/javascript'>  alert('Thank you. Goodbye.); </script>"); 
                header("Refresh: 0; url=./");
            }else{
                $auth=$_COOKIE["auth"];
                $id=$_GET["profile"];
                $baglanti = @mysql_connect('localhost', 'kaanari1_kaan', 'hwworld');
                $veritabani = @mysql_select_db('kaanari1_hw1');
                $read = mysql_query("select id,userid from cookie where auth='".$auth."'");
                $list = mysql_fetch_array($read);
                if(!(empty($list))){
                    if($list[1]==$id){
                        $read2 = mysql_query("select * from users where id='".$id."'");
                        $info = mysql_fetch_array($read2);
                        $id=$info[0];
                        $username=$info[1];
                        $name=$info[3];
                        $surname=$info[4];
                        $email=$info[5];
                        $quote=quote()[0];
                        $author=quote()[1];

                    echo "
                    <head>
                        <title>$username's Profile</title>
                        <link rel='stylesheet' href='style.css'>
                    </head>
                    <body>
                    <div class='nav2'>
                        <header>
                            <nav>
                                <ul>
                                    <li><a href='./index.php'>Home</a></li>
                                    <li><a href='#'>Profile</a></li>
                                    <li><a href='./profile.php?state=1'>Logout</a></li>
                                </ul>    
                            </nav>
                        </header>
                        
                    </div>
                    <div class='profile'>
                        <center id='profile'>
                            <h2>Hi $username</h2><br>
                            <h4>Your Name is = $name $surname</h4>
                            <h4>Your e-mail is = $email</h4>
                            <h4>Quote of the Day for you : </h4>
                            <pre>$quote</pre>
                            <h4>Author : $author</h4>
                            <button onclick=location=URL>New Quote</button>
                        </center>
                    </div>
                    ";
                    }else{
                        echo ("<script type='text/javascript'>  alert('You dont have permission for this page.'); </script>"); 
                        header("Refresh: 1; url=./");

                    }
                }else{
                    echo ("<script type='text/javascript'>  alert('You dont have permission for this page.'); </script>"); 
                    header("Refresh: 1; url=./");
                }
                
            }
        }
        
        
        
    ?> 
    </body>
</html>

