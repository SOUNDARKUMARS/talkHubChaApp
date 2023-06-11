<?php
session_start();
$DATA_RAW=file_get_contents("php://input");
$DATA_OBJ=json_decode($DATA_RAW);
$info=(object)[];
if(!isset($_SESSION['userid'])){
    if(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type !="login"  && $DATA_OBJ->data_type !="signup"){
        $info->logged_in=false;
        echo json_encode($info);
        die;
    }

}

$info=(object)[];
require_once("classes/autoload.php");
$DB=new Database();


// process the data

// signup
if(isset($DATA_OBJ->data_type)&& $DATA_OBJ->data_type=="signup"){
    $data=false;
    $data['userid']=$DB->generate_id(20);
    $data['username']=$DATA_OBJ->username;
    
    $data['gender']=isset($DATA_OBJ->gender) ? $DATA_OBJ->gender :null;  
    $data['email']=$DATA_OBJ->email;
    $data['password']=$DATA_OBJ->password;
    $data['date']=date("Y-m-d H:i:s");

    $query="insert into signup (userid,username,gender,password,email,date) values(:userid,:username,:gender,:password,:email,:date)";
    $result= $DB->write($query,$data);
    if($result){
        $info->message="profile created";
        $info->data_type="info";
        echo json_encode($info);
       
    }else{
        $info->message="something's wrong in creating profile";
        $info->data_type="error";
        echo json_encode($info);
    }



    // login page
}

// login
elseif(isset($DATA_OBJ->data_type)&& $DATA_OBJ->data_type=="login"){
    // login page
    $data=false;
    $data['email']=$DATA_OBJ->email;
    $query = "select * from signup where email = :email limit 1";
    $result= $DB->read($query,$data);
    // print_r($result);
    if(is_array($result)){
        
      
            $result=$result[0];
        
            if($result->password == $DATA_OBJ->password){
                
                $_SESSION['userid']=$result->userid;
                $info->message="Login Successful";
                $info->data_type="info";
                echo json_encode($info); 
            }
        else{
            $info->message="Wrong password";
            $info->data_type="error";
            echo json_encode($info); 
        }

    }else{
        $info->message="Wrong email";
        $info->data_type="error";
        echo json_encode($info);
    }
}

// logout
elseif(isset($DATA_OBJ->data_type)&& $DATA_OBJ->data_type=="logout"){
    if(isset($_SESSION['userid'])){
        unset($_SESSION['userid']);
    }
    $info->logged_in=false;
    echo json_encode($info);
}

// user_info 
elseif(isset($DATA_OBJ->data_type)&& $DATA_OBJ->data_type=="user_info"){
    $data=false;
    $data['userid']=$_SESSION['userid'];
    $query="select * from signup where userid = :userid limit 1";
    $result= $DB->read($query,$data);
    if(is_array($result)){
        $result=$result[0];
        $result->data_type="user_info";

        $image=($result->gender=="male")? "img/user_male.png":"img/user_female.png";
        if(file_exists($result->image)){
            $image=$result->image;
        }
        $result->image=$image;
        echo json_encode($result);

    }else{
        $info->message="Wrong email";
        $info->data_type="error";
        echo json_encode($info);
    }
}

// contacts
elseif(isset($DATA_OBJ->data_type)&& $DATA_OBJ->data_type=="contact"){
    $uid=$_SESSION['userid'];
    $sql="select * from signup where userid !='$uid' limit 10";
    $myusers=$DB->read($sql,[]); 
    $mydata='
    <style>
    #contacts img{
        object-fit: cover;
        border-radius: 50%;
        box-shadow:  0px 0px 30px rgba(41, 77, 90, 0.914);
    }
    @keyframes appear{

        0%{opacity:0;transform: translateY(60px);}
        100%{opacity: 1; transform: translateX(0px);}
      }
      #contacts{
        
      }
      #contacts:hover{
        transition: all   .2s cubic-bezier(.43,.56,.43,.69);
        transform: scale(1.05);
      }
      </style>
    <div style="text-align: center; animation:appear 2s ease">';

    if(is_array($myusers)){
        foreach($myusers as $row){
            $image=($row->gender=="male")? "img/user_male.png":"img/user_female.png";
            if(file_exists($row->image)){
                $image=$row->image;
            }
            $mydata .='<div id="contacts" userid='.$row->userid.' onclick="start_chat(event)">
                <img src='.$image.' ><br>
                '. $row->username.'
            </div>';
        }
    }
    $mydata .='</div>';
    $result=(object)[];
    $result->message=$mydata;
    $result->data_type="contact";
    echo json_encode($result);
    die;

    $info->message="No contacts found";
    $info->data_type="error";
    echo json_encode($info);
}

// chats
elseif(isset($DATA_OBJ->data_type)&& ($DATA_OBJ->data_type=="chats" ||  $DATA_OBJ->data_type=="chats_refresh")){
    // elseif(isset($DATA_OBJ->data_type)&& $DATA_OBJ->data_type=="chats"){
    $arr['userid']=null;
    if(isset($DATA_OBJ->find->userid)){
            $arr['userid']=$DATA_OBJ->find->userid;

    }
    $refresh=false;
    $seen=false;
    if($DATA_OBJ->data_type=="chats_refresh"){
        $refresh=true;
        $seen=$DATA_OBJ->find->seen;
    }
    $sql="select * from signup where userid = :userid limit 1";
    $result=$DB->read($sql,$arr); 
    
    if(is_array($result)){
        // user found
        $row=$result[0];
        
        $image=($row->gender=="male")? "uploads/user_male.png":"uploads/user_female.png";
        if(file_exists($row->image)){
            $image=$row->image;
        }
        $mydata='';
        if(!$refresh){
            $row->image=$image;
            $mydata ='<div id="active_contacts">
                <img src="'.$image.'"  >
                '. $row->username.'
            </div>';            
        }
        $messages ='';
        $new_message=false;

        if(!$refresh){
            $messages .='
            <div id="messages_holder_parent" onclick="set_seen(event)">
            <div id="messages_holder">';
    }
      
                        // read from DB
                        $a['sender']=$_SESSION['userid'];
                        $a['receiver']=$arr['userid'];

                        $sql="select * from messages where (sender= :sender && receiver= :receiver && deleted_sender=0) ||( receiver=:sender && sender=:receiver && deleted_receiver=0) ";
                        $result2=$DB->read($sql,$a); 
                       
                        if(is_array($result2)){
                            foreach($result2 as $data){
                                $myuser=$DB->get_user($data->sender);
                                if($data->receiver==$_SESSION['userid'] && $data->received==0){
                                    $new_message=true;
                                }

                                if($data->receiver==$_SESSION['userid'] && $data->received==1 && $seen){
                                    $DB->write("update messages set seen=1 where id='$data->id' limit 1");
                                }

                                if($data->receiver==$_SESSION['userid']){
                                    $DB->write("update messages set received=1 where id='$data->id' limit 1");
                                }

                                if($_SESSION['userid']==$data->sender){
                                    $messages.=message_right($data,$myuser);
                                }else{
                                    $messages.=message_left($data,$myuser);
            
                                }
                            }
                        }

        if(!$refresh){
        $messages.='
        </div>
        </div>
        <div id="send_message_button">
            <div class="input-container">
            <label id="paper_clip"for="message_file"> <img src="img/attach-file.png" style="opacity: 0.8; width: 30px; " alt=""> </label>

            <input placeholder="Message..." type="file" id="message_file" style="display: none;" onchange="send_image(this.files)"></input>

            <input placeholder="Message" onkeyup="enter_press(event)" id="message_text" type="text" class="input"  autocomplete="off">
            <span id="send_button"><input type="button" onclick="send_message(event)" value="Send"></span>
        </div>
        </div>
        
        
        ';
        }

        $info->user=$mydata;
        $info->messages=$messages;
        $info->data_type="chats";
        $info->new_message=$new_message;
        if($refresh){
                    $info->data_type="chats_refresh";
        }
        echo json_encode($info);
    }else{
        // user not found    
        $info->user="select contact to start chat with.";
        $info->data_type="chats";
        echo json_encode($info);
    }
}


// settings
elseif(isset($DATA_OBJ->data_type)&& $DATA_OBJ->data_type=="settings"){
    $sql="select * from signup where userid= :userid limit 1";
    $id=$_SESSION['userid'];
    $data=$DB->read($sql,['userid'=>$id]);
    $mydata=" ";
    $gender_male="";
    $gender_female="";
    if(is_array($data)){
        $data=$data[0];

        // check if image exist
        $image=($data->gender=="male")? "img/user_male.png":"img/user_female.png";
        if(file_exists($data->image)){
            $image=$data->image;
        }
        if($data->gender=='male'){
            $gender_male="checked";

        }else{
            $gender_female="checked";

        }
    $mydata='  
    <style>

  
   
    @keyframes appear{

        0%{opacity:0;transform: translateY(60px);}
        100%{opacity: 1; transform: translateX(0px);}
      }
    
  .input-container input, .form button {
    outline: none;
    border: 1px solid #e5e7eb;
    margin: 8px 0;
  }
  
  .input-container input {
    padding: 1rem;
    padding-right: 3rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    width: 300px;
    border-radius: 0.5rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  }

  

    .submit {
        display:inline-block;
        background-color: #4F46E5;
        color: #ffffff;
        font-size: 0.875rem;
        line-height: 1.25rem;
        font-weight: 500;
        width: 300px;
        border-radius: 0.5rem;
        text-transform: uppercase;
      }
      .gross_container{
        position: relative;
      }
      .sett_container{
        position: absolute;
        right: 30px;
        top: 50px;

      }
      .img_conatiner{
        position: absolute;
        left: 23px;
        top: 15px;

      }



     #save_settings_button,#change_image_button{
        margin: auto;
        display: block;
        width: 100%;
        letter-spacing: 1.5px;
        line-height: 30px;
        text-align: center;
        font-size: 18px;
        background-color:#2056be;
        border: 1px solid #051533; 
        margin-top: 8px;
        border-radius: 5px;
        color: white;
        transition: all ease .2s;
        cursor:pointer;
    }
    #save_settings_button:hover{
        color: #051533;
        background-color:#ffffff;
    }
    #save_settings_button:active{
        transform:translateY(-7px)
    }

    #change_image_button:hover{
        color: #051533;
        background-color:#ffffff;
    }
    #change_image_button:active{
        transform:translateY(-7px)
    }
    .dragging{
        border:  #21c1f2 dashed 1px;
        box-shadow:0px 0px 20px black;

    }
    .dargspan{
    font-size: 10px;
    
    }

    </style>

    <div class="gross_container" style="animation:appear 1.2s ease;transition: all ease .2s;
        "  >
        <div class="img_conatiner">
            <img  ondragover="handle_drag_and_drop(event)" ondrop="handle_drag_and_drop(event)" ondragleave="handle_drag_and_drop(event)" src='.$image.' alt="" height="200px" width="auto">

        <label for="change_image_input"  id="change_image_button" class="submit"> Change Profile
        
            </label>
            <span class="dargspan">Or Drag and Drop Here</span>
            <input type="file" onchange="upload_profile_image(this.files)" id="change_image_input" style="display:none;">

            </div>
                <form id="myform" class="form">

                <div class="sett_container"  >
                <p class="form-title">Edit Your Profile Here</p>

                <div class="input-container">
                <input placeholder="Change UserName" value='.$data->username.' name="username" type="text">
                </div>

                <div class="gender">
                <div class="inputs">
                    <label for="gender_male">Male</label>
                    <input value="male" type="radio" '.$gender_male.' name="gender">
                    <label for="gender_female">Female</label>
                    <input value="female" type="radio" '.$gender_female.' name="gender">
                </div>
                </div>
                
                <div class="input-container">
                <input placeholder="Change email" value='.$data->email.' name="email" type="email">
                </div>

                <div class="input-container">
                <input name="password"  placeholder="Change Password" value='.$data->password.'  type="password">
                </div>
                <input type="button" id="save_settings_button" onclick="collect_data(event)" class="submit"  value="Save Settings" >
                </form>

        </div>
    </div>';

    $result=(object)[];
    $result->message=$mydata;
    $result->data_type="contact";
    echo json_encode($result);
    }else{


    $info->message="No contacts found";
    $info->data_type="error";
    echo json_encode($info);
    }
}



// save settings
elseif(isset($DATA_OBJ->data_type)&& $DATA_OBJ->data_type=="save_settings"){
    $info=(object)[];
    $data=false;
    $data['userid']=$_SESSION['userid'];
    $data['username']=$DATA_OBJ->username;
    $data['gender']=isset($DATA_OBJ->gender) ? $DATA_OBJ->gender :null;  
    $data['email']=$DATA_OBJ->email;
    $data['password']=$DATA_OBJ->password;

    $query="update signup set username=:username ,gender=:gender ,password=:password ,email=:email where userid=:userid limit 1";
    $result= $DB->write($query,$data);
    if($result){
        $info->message="Changes Saved";
        $info->data_type="save_settings";
        echo json_encode($info);
       
    }else{
        $info->message="something's wrong in changing profile";
        $info->data_type="error";
        echo json_encode($info);
    }


}


elseif(isset($DATA_OBJ->data_type)&& $DATA_OBJ->data_type=="send_message"){
    $arr['userid']=null;
    if(isset($DATA_OBJ->find->userid)){
            $arr['userid']=$DATA_OBJ->find->userid;

    }
    $sql="select * from signup where userid = :userid limit 1";
    $result=$DB->read($sql,$arr); 
    
    if(is_array($result)){
        $arr['message']=$DATA_OBJ->find->message;
        $arr['date']=date("Y-m-d H:i:s");
        $arr['sender']=$_SESSION['userid'];
        $arr['msg_id']= get_random_string_max(60);


                $arr2['sender']=$_SESSION['userid'];
                $arr2['receiver']=$arr['userid'];

                $sql="select * from messages where (sender= :sender && receiver= :receiver) ||( receiver=:sender && sender=:receiver) limit 1";
                $result2=$DB->read($sql,$arr2); 
                
                if(is_array($result2)){
                    $arr['msg_id']=$result2[0]->msg_id;
                }
        $query="insert into messages (sender,receiver,message,date,msg_id) values (:sender,:userid,:message,:date,:msg_id)";
        $DB->write($query,$arr); 

        $row=$result[0];
        
        $image=($row->gender=="male")? "img/user_male.png":"img/user_female.png";
        if(file_exists($row->image)){
            $image=$row->image;
        }
        
        $row->image=$image;
        $mydata ='<div id="active_contacts">
            <img src="'.$image.'"  >
            '. $row->username.'
        </div>';

        $messages ='
        
        <div id="messages_holder">';

                // read from DB
                $a['msg_id']=$arr['msg_id'];

                $sql="select * from messages where msg_id= :msg_id";

                $result2=$DB->read($sql,$a); 
                $result2=array_reverse($result2);
                if(is_array($result2)){
                    $result2=array_reverse($result2);
                    foreach($result2 as $data){
                        $myuser=$DB->get_user($data->sender);
                    if($_SESSION['userid']==$data->sender){
                        $messages.=message_right($data,$myuser);
                    }else{
                        $messages.=message_left($data,$myuser);

                    }
                    }
                }
        $messages.='
      
        </div>
        <div id="send_message_button">
            <div class="input-container">
            <label id="paper_clip"for="file"> <img src="img/attach-file.png" style="opacity: 0.8; width: 40px; " alt=""> </label>

                <input placeholder="Message" type="file" id="message_file" style="display: none;"></input>
                <input placeholder="Message" onkeyup="enter_press(event)" id="message_text" type="text" class="input"  autocomplete="off">
                <span id="send_button"><input type="button" onclick="send_message(event)" value="Send"></span>
            </div>
        </div>
        
        
        ';

        $info->user=$mydata;
        $info->messages=$messages;
        $info->data_type="send_message";
        echo json_encode($info);
    }else{
        // user not found    
        $info->user="No contact found to start chat.";
        $info->data_type="send_message";
        echo json_encode($info);
    }




}
elseif (isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "delete_message") {
    $arr['row_id'] = null;
    if (isset($DATA_OBJ->find->row_id)) {
        $arr['row_id'] = $DATA_OBJ->find->row_id;
    }

    $sql = "SELECT * FROM messages WHERE id = :row_id LIMIT 1";
    $result = $DB->read($sql, $arr);

    if (is_array($result)) {
        $sql = "DELETE FROM messages WHERE id = :row_id LIMIT 1";
        $DB->write($sql, array('row_id' => $arr['row_id']));
    }
}



function message_left($data,$row){
    $image=($row->gender=="male")? "img/user_male.png":"img/user_female.png";
    if(file_exists($row->image)){
        $image=$row->image;
    }
    return' 
    <div id="message_left">
        <img src="'.$image.'"  id="prof">
        '.$data->message.' <br>
       
        <span style="font-size:10px; color:#513535;">'.date("jS M H:i a",strtotime( $data->date)).'</span>
        <img src="'.$data->files.'" style="width:100%;" id="shared_img">
    </div>';
}

function message_right($data,$row){
    $image=($row->gender=="male")? "img/user_male.png":"img/user_female.png";
    if(file_exists($row->image)){
        $image=$row->image;
    }
    $a=' 
    <div id="message_right">
    <div id="seen_img">';
    if($data->seen){
        $a.='<img src="img/seen.png" id="seen">';        
    }elseif($data->received){
            $a.='<img src="img/sent.png" id="seen">';    
    }
  
     $a.='</div>
     <div id="del_cont"><img src="img/delete_msg.png" class="delete_msg"   onclick="delete_message(event)" del_msg_id="'.$data->id.'"></div>
    <img src="'.$image.'" id="prof" > 
        '.$data->message.' <br>
           
        <div id="datespan"><span style="font-size:10px;">'.date("jS M H:i a",strtotime( $data->date)).'</span></div>
        <img src="'.$data->files.'" style="width:100%;" id="shared_img">
    </div>
   ';
    return $a;
}

function get_random_string_max($length) {

	$array = array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
	$text = "";

	$length = rand(4,$length);

	for($i=0;$i<$length;$i++) {

		$random = rand(0,61);
		
		$text .= $array[$random];

	}

	return $text;
}

?>
