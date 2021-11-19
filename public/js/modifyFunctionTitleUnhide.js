// userId correspond à user.id dans la boucle twig de articleList.html.twig
function unhide(userId) {
    let form = document.getElementById('modifyFunctionTitleForm-'+userId);

    if (form.style.display === "none") {

        form.style.display = "block";
    } else {
        form.style.display = "none";
    }
}