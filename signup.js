function _(element){
    return document.getElementById(element)
}

function handle_result(result){
    let data=JSON.parse(result)
    if(data.data_type=="info"){
        window.location="index.html"
        console.log("redirection to index.php")
    }
  }

  
function send_data(data,type){
    let xml=new XMLHttpRequest()
    xml.onload=function(){
        if(xml.status==200 || xml.readyState==4){
          handle_result(xml.responseText)
        }

    }
    data.data_type=type
    let data_string=JSON.stringify(data)

    xml.open('POST','api.php',true)
    xml.send(data_string)
}



function collect_data(){

    signup_btn.disabled = true;
    signup_btn.value = "Loading...Please wait..";

    let myform=_("myform")
    let inputs=myform.getElementsByTagName("INPUT")
    let data={}
    for (let i = inputs.length - 1; i >= 0; i--){

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
    
            case "gender_female":
            case "gender_male":
                if(inputs[i].checked){
                    data.gender=inputs[i].value
                }
            break  
        }

  }
  send_data(data,"signup")
}

let signup_btn=_("signup_submit")
signup_btn.addEventListener("click",collect_data)


