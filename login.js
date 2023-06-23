function _(element){
    return document.getElementById(element)
}

let login_btn=_("login_submit")
login_btn.addEventListener("click",collect_data)

function collect_data(e){
  e.preventDefault()
  login_btn.disabled=true
  login_btn.value = "Loading...Please wait.."

    let myform=_("myform")
    let inputs=myform.getElementsByTagName("INPUT")
    let data={}
    for (let i=0; i<inputs.length; i++){

        let key= inputs[i].name

        switch (key){
            case "email":
                data.email=inputs[i].value
                break
            case "password":
                data.password=inputs[i].value
                break

        }

   }
   send_data(data,"login")
}
function send_data(data,type){
    let xml=new XMLHttpRequest()
    xml.onload=function(){
        if( xml.readyState==4 || xml.status==200 ){
          handle_result(xml.responseText)
          login_btn.disabled=false
          login_btn.value = "Login"

        }

    }
    data.data_type=type
    let data_string=JSON.stringify(data)

    xml.open('POST','api.php',true)
    xml.send(data_string)
}
function handle_result(result){
  let data=JSON.parse(result)
  alert(data.message)
  if(data.data_type=="info"){
      window.location="index.html"      
  }
}