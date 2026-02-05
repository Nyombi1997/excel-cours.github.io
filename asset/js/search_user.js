let search_user = document.getElementById("search_user"),
div_detail_user = document.getElementById("div_detail_user");
let oldData = div_detail_user.innerHTML;
search_user.addEventListener("input",function(){
    let value = search_user.value.trim();
    if(value === "")
    {
        div_detail_user.innerHTML = oldData;
    }
    $.post(
        "/fonctions/search_user.php",
        {
            q : value
        },
        function(data){
            if(data.msg == "")
            {
                div_detail_user.innerHTML = `
                    <div class="div_no_actif_user_manage_user">
                        Aucun utilisateur enregistrer
                    </div>`;
            }
            else
            {
                div_detail_user.innerHTML = data.msg;
            }
        }
    )
})