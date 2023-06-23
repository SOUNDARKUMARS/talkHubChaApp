<?php

$succ=0;
$existuser=0;
if(isset($_POST['submit'])){
    
$hostname="localhost";
$username="root";
$password="";
$database="chatapp";
$hcon=mysqli_connect($hostname,$username,$password,$database);
if(!$hcon){
    die(mysqli_error($hcon));
}
    

$name=$_POST['username'];
$pwd=$_POST['password'];
$email=$_POST['email'];
$gender = $_POST['gender'];

function generate_id(){
    $rand="";
    // digit size
    $rand_count= rand(4,19);
    for($i=0; $i<$rand_count;$i++){
        $r=rand(0,9);
        // the actual random number 
        $rand .=$r;
    }
    return $rand;
}

$userid=generate_id();



$sel="SELECT * FROM `signup` WHERE username='$name'";
$result=mysqli_query($hcon,$sel);
if($result){
    $num=mysqli_num_rows($result);
    if($num>0){
            echo("user exists");
            $existuser=1;
    }else{
        // $sql = "INSERT INTO `signup` (username,password,email) VALUES ('$name','$pwd','$email')";
        $sql = "INSERT INTO `signup` (username,password,email,gender,userid) VALUES ('$name','$pwd','$email','$gender','$userid')";
        $datacon=mysqli_query($hcon,$sql); 
        $succ=1; 
        echo "succeed";
    }
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to Chat</title>


<style>
        @import url('https://fonts.googleapis.com/css2?family=Signika+Negative&display=swap');


        body{
        background-color: #2056be;
        display: flex;
        justify-content: center;
        margin-top: 10%;
        font-family: 'Signika Negative', sans-serif;

        }
        .form {
        
            background-color: #fff;
            display: block;
            padding: 1rem;
            max-width: 350px;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding-right: 30px;
            box-shadow: 8px 8px  20px;
        }
        
        .form-title {
            font-size: 1.25rem;
            line-height: 1.75rem;
            font-weight: 600;
            text-align: center;
            color: #000;
        }
        
        .input-container {
            position: relative;
        }
        
        .input-container input, .form button {
            outline: none;
            border: 1px solid #e5e7eb;
            margin: 8px 0;
        }
        
        .input-container input {
            background-color: #fff;
            padding: 1rem;
            padding-right: 3rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
            width: 300px;
            border-radius: 0.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        
        .input-container span {
            display: grid;
            position: absolute;
            top: 0;
            bottom: 0;
            right: 0;
            padding-left: 1rem;
            padding-right: 1rem;
            place-content: center;
        }
        
        .input-container span svg {
            color: #9CA3AF;
            width: 1rem;
            height: 1rem;
        }
        
        .submit {
            display: block;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            padding-left: 1.25rem;
            padding-right: 1.25rem;
            background-color: #4F46E5;
            color: #ffffff;
            font-size: 0.875rem;
            line-height: 1.25rem;
            font-weight: 500;
            width: 100%;
            border-radius: 0.5rem;
            text-transform: uppercase;
        }
        
        .signup-link {
            color: #6B7280;
            font-size: 0.875rem;
            line-height: 1.25rem;
            text-align: center;
        }
        
        .signup-link a {
            text-decoration: underline;
        }
        .gender{
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            color: #757575;
            width: 346px;
            height: 50px;
            padding-left: 20px;
        }
        
        .gender input{
            margin: 2px;
            margin-top: 17px;

        }

        .chtapage_link{
            width: 326px;
            color: black;
            border: .3 solid white;
            transition: all ease .7s;

        }
        #signup_submit.submit:hover{
            background-color: white;
            color: #2056be;   
            border: 1.3px solid #2056be; 
        

        }




            #login_submit{
            width: 365px;
            } 
</style>

</head>
<body>
    <div class="wrapper">
      <form id="myform" class="form" method="post" action="signup.php">
        <p class="form-title">Create your new account</p>
        <div class="input-container">
          <input placeholder="Username here.." name="username" type="text">
        </div>

        <div class="gender_select">
            <label for="male">Male</label>
            <input type="radio" name="gender" value="male">
            
            <label for="female">Female</label>
            <input type="radio" name="gender" value="female">
        </div>

        <div class="input-container">
          <input placeholder="Enter email" name="email" type="email">
        </div>

        <div class="input-container">
          <input name="password"  placeholder="Create Password" type="password">
        </div>
        <input type="submit" id="login_submit" name="submit" class="submit" value="Sign-up" >
        <p> Back to <a  href="login.html">Login  page</a></p>

      </form>
    </div>   

</body>
</html>