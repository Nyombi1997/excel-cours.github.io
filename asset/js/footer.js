/* formulaire pour souscrire au news letter */
let
form_souscription_new_letter = document.getElementById("form_souscription_new_letter"),
email = document.getElementById("email_souscription_new_letter");
let
btn = form_souscription_new_letter.querySelector("button[type='submit']");
let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
valid = true;
form_souscription_new_letter.addEventListener("submit",function(e){
    e.preventDefault();
    btn.setAttribute("disabled","");
    let temp_btn = btn.innerHTML;
    btn.innerHTML = `<i class="fa-solid fa-circle-notch rotate"></i>`;
    let
    value_email = email.value.trim();
    value_email = email.value.replace(/ +/g,"");
    if(value_email == "")
    {
            Swal.fire({
            icon: "error",
            title: "Entrez une adresse email",
            text: "",
            confirmButtonText: "OK",
            confirmButtonColor: "#056bf1"
        })
        email.focus();
        btn.removeAttribute("disabled");
        btn.innerHTML = temp_btn;
        valid = false;
    }
    else if(emailRegex.test(email.value.trim()) == false)
    {
            Swal.fire({
            icon: "error",
            title: "Entrez une adresse email correct",
            text: "",
            confirmButtonText: "OK",
            confirmButtonColor: "#056bf1"
        })
        email.focus();
        btn.removeAttribute("disabled");
        btn.innerHTML = temp_btn;
        valid = false;
    }
    else
    {
        $.post("/fonctions/check_email_souscription.php",
            {email: email.value.trim()},
            function(data){
                if(data.result == "error")
                {
                    Swal.fire({
                        icon: "error",
                        title: data.msg,
                        text: "",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#056bf1"
                    })
                    email.focus();
                    btn.removeAttribute("disabled");
                    btn.innerHTML = temp_btn;
                }
                else
                {
                    Swal.fire({
                        icon: "success",
                        title: data.msg,
                        text: "",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#056bf1",
                        timer : 3000
                    })
                    btn.removeAttribute("disabled");
                    btn.innerHTML = temp_btn;
                    email.value = "";
                }
            }
        )
    }
})