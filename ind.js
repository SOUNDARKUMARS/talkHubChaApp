let sent_audio=new Audio("audio/sentmsg.mp3")
let received_audio=new Audio("audio/gotmsg.mp3")

let CURRENT_CHAT_USER=""
let SEEN_STATUS=false

function _(element){
    return document.getElementById(element)
}
let label_contact=_("label_contact")
label_contact.addEventListener("click",get_contacts)

let label_settings=_("label_settings")
label_settings.addEventListener("click",get_settings)

let label_chat=_("label_chat")
label_chat.addEventListener("click",get_chats)

let logout=_("logout")
logout.addEventListener("click",logout_user)

function get_data(find,type){
 let xml=new XMLHttpRequest()
 let loader_holder=_("loader_holder")
 loader_holder.className="loader_on"
 xml.onload=function(){
    // after send and open are processed, it will have it's actions
    if(xml.readyState==4 || xml.status==200){
        loader_holder.className="loader_off"
        handle_result(xml.responseText,type)
    }
 }
 let data={}
 data.find=find
 data.data_type=type
 data=JSON.stringify(data)

 xml.open('POST','api.php',true)
 xml.send(data)
}

function handle_result(result,type){
    // alert(result)

    if(result.trim() !=""){
        let inner_right_pannel=_("inner_right_pannel")
        let inner_left_pannel=_("inner_left_pannel")

        inner_right_pannel.style.overflow='visisble'
        
        let obj=JSON.parse(result)
        if(typeof(obj.logged_in) != "undefined" && !obj.logged_in){
            window.location="login.html"
        }else{
            switch(obj.data_type){
                case "user_info":
                    let username=_("username")
                    username.innerHTML=obj.username

                    let profile_image=_("profile_image")
                    profile_image.src=obj.image

                    let email=_("email")
                    email.innerHTML=obj.email
                break

                case "contact":
                    inner_right_pannel.style.overflow='hidden'
                    inner_left_pannel.innerHTML=obj.message
                    break

                case "chats_refresh":
                    SEEN_STATUS=false
                    let messages_holder=_("messages_holder")
                    messages_holder.innerHTML=obj.messages
                    if(typeof obj.new_message != "undefined"){
                        if(obj.new_message){
                            received_audio.play()
                        }
                    }
                    break

                case "send_message":
                    sent_audio.play()
                case "chats":
                    SEEN_STATUS=false
                    let innerleft_pannel=_("inner_left_pannel")
                    innerleft_pannel.innerHTML=obj.user
                    inner_right_pannel.innerHTML=obj.messages
                    let messages__holder=_("messages_holder")

                    setTimeout(function(){
                        
                    messages__holder.scrollTo(0,messages__holder.scrollHeight)
                    let message_text=_("message_text")
                    message_text.focus()
                    },100)

                    break

                case "send_image":
                    // alert(obj.message)
                    break
                case "settings":
                    let innerLeftPannel=_("inner_left_pannel")
                    innerLeftPannel.innerHTML=obj.message
                    break

                case "save_settings":
                    
                    get_data({},"user_info")
                    get_data({},"settings")
                    break

              
            }
        }
        
    }
}
function logout_user(){
    let ans=confirm("Are sure you wanna Logout?")
    if(ans){get_data({},"logout")}
}
// check the type of data from the data object
get_data({},"user_info")
get_data({},"contact")
let contact_check=_('contact_check')
contact_check.checked=true


function get_contacts(e){
    get_data({},"contact")

}

function get_chats(e){
    get_data({
        seen:SEEN_STATUS
    },"chats")

}

function get_settings(e){
    get_data({},"settings")

}

function send_message(e){
    let message_text=_("message_text")
    if(message_text.value.trim()==""){
        alert("type something to send")
        return;
    }
    get_data({
        message:message_text.value.trim(),
        userid:CURRENT_CHAT_USER
    },"send_message")
}

function enter_press(e){
    // alert(event.key)
    if(event.key=="Enter"){
        send_message(e) 
    }
    SEEN_STATUS=true

}

setInterval(function(){

    if(CURRENT_CHAT_USER !=""){
        get_data({userid:CURRENT_CHAT_USER,
                    seen:SEEN_STATUS
                },"chats_refresh")
    }
},10000)


function set_seen(e){
    SEEN_STATUS=true

}

function delete_message(e){
    if(confirm("You Wanna Delete this Message for Everyone?")){
        let del_msg_id=e.target.getAttribute("del_msg_id")
        get_data({
            row_id:del_msg_id
        },"delete_message")
        get_data({userid:CURRENT_CHAT_USER,
                    seen:SEEN_STATUS
                },"chats_refresh")
    }
}





function collect_data(){
    let save_settings_button=_("save_settings_button")
    save_settings_button.disabled=true
    save_settings_button.value="Saving...Please Wait..."

    let myform=_("myform")
    let inputs=myform.getElementsByTagName("INPUT")
    let data={}
     for (let i=0; i<inputs.length; i++){

        let key= inputs[i].name

        switch (key){
            case "username":
                data.username=inputs[i].value
                break

            case "email":
                data.email=inputs[i].value
                break

            case "password":
                data.password=inputs[i].value
                break
     
            case "gender":
                if(inputs[i].checked){
                    data.gender=inputs[i].value
                }
            break  
        }

   }
   send_data(data,"save_settings")
}
function send_data(data,type){
    let xml = new XMLHttpRequest();

xml.onload = function(){

    if(xml.readyState == 4 || xml.status == 200){

        handle_result(xml.responseText);
        let save_settings_button = _("save_settings_button");
        save_settings_button.disabled = false;
        save_settings_button.value = "Signup";
    }
}

data.data_type = type;
let data_string = JSON.stringify(data);

xml.open("POST","api.php",true);
xml.send(data_string);
}


// change profile image
function upload_profile_image(files){
    let change_image_input=_("change_image_input")
    change_image_input.disabled=true
    change_image_input.innerHTML="Uploading Image..."
    
    let myform=new FormData()

    let xml=new XMLHttpRequest()

    xml.onload=function(){
        if(xml.status==200 || xml.readyState==4){


            get_data({},"user_info")
            get_data({},"settings")
            change_image_input.disabled=false
            change_image_input.innerHTML="Change Image"
        }

    }
    myform.append('file',files[0])
    myform.append('data_type',"change_image_profile")
    
    xml.open("POST","uploader.php",true)
    xml.send(myform)
}

function handle_drag_and_drop(event){
if(event.type=="dragover"){
    event.preventDefault()
    event.target.className="dragging"
}
else if(event.type=="drop"){
    event.target.className=""
    event.preventDefault()
    upload_profile_image(event.dataTransfer.files)
}
else if(event.type=="dragleave"){
    event.preventDefault()
    event.target.className=""

}

}
function start_chat(e){
    let userid=e.target.getAttribute("userid")
    if(e.target.id==""){
        userid=e.target.parentNode.getAttribute("userid")
    }
    CURRENT_CHAT_USER=userid
    let chat_check=_("chat_check")
    chat_check.checked=true
    get_data({userid:CURRENT_CHAT_USER},"chats")
}

function send_image(files){
    let myform=new FormData()
    let xml=new XMLHttpRequest()
    xml.onload=function(){
        if(xml.status==200 || xml.readyState==4){
            handle_result(xml.responseText,"send_image")
            get_data({userid:CURRENT_CHAT_USER,
                    seen:SEEN_STATUS
            },"chats_refresh")
        }

    }
    myform.append('file',files[0])
    myform.append('data_type',"send_image")
    myform.append('userid',CURRENT_CHAT_USER)

    xml.open("POST","uploader.php",true)
    xml.send(myform)
}