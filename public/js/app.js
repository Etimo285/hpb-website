$(function(){
    // Rend temporaire les div alerts (messages)
    if ($('div.alert')) {
        $('div.alert').delay(3500).fadeOut(300, function() {
            $('div.alert').remove();
        });
    }
});
function openNav() {
    document.getElementById("sideNavigation").style.width = "250px";
    document.getElementById("main").style.marginLeft = "250px";
}

function closeNav() {
    document.getElementById("sideNavigation").style.width = "0";
    document.getElementById("main").style.marginLeft = "0";
}