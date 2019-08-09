let visibleMenu = false;

function openMenu (e) {
    e.preventDefault();
    if (visibleMenu) {
        resetMenu();
    } else {
        showMenu();
    }
}

function showMenu (e) {
    document.querySelector("#nav-docs").style.marginLeft = "0";
    Object.assign(document.querySelector("#content-docs").style, {
        marginLeft: "16.5em",
        marginRight: "-15em"
    });
    visibleMenu = true;
}

function resetMenu (e) {
    document.querySelector("#nav-docs").style.marginLeft = "";
    Object.assign(document.querySelector("#content-docs"), {
        marginLeft: "",
        marginRight: ""
    });
    visibleMenu = false;
}

function openMenuSup (e) {
    e.preventDefault();
    var menu = document.querySelectorAll("header nav")[0],
        self = this;
    if (menu.style.display === "") {
        menu.style.display = "block";
        self.className = "open";
    } else {
        menu.style.display = "";
        self.className = "";
    }
}

const menu = document.querySelector("#menu-list");
const navIcon = document.querySelector("#nav-icon");
navIcon.addEventListener("click", openMenuSup);
window.addEventListener("resize", function(e) {
    document.querySelectorAll("header nav")[0].style.display = "";
    navIcon.className = "";
});
if (menu) {
    menu.addEventListener("click", openMenu);
    window.addEventListener("resize", resetMenu);
}
