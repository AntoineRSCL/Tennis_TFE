const myBurgerMenu = document.querySelector(".menuBurger");
const myLinksBurger = document.querySelectorAll(".containerMenuBurger ul li");
const navIcon = document.querySelector('#nav-icon1');
const menuUser = document.querySelector(".menuUserHeader")
const btnUser = document.querySelector(".infoUserHeader")
const svgUser = document.querySelector(".logoMUH")

// Vérifiez que navIcon et myBurgerMenu existent avant d'ajouter des écouteurs d'événements
if (navIcon && myBurgerMenu) {
    // Partie Menu Burger
    navIcon.addEventListener('click', function() {
        this.classList.toggle('open');
        myBurgerMenu.classList.toggle('open');
    });

    myLinksBurger.forEach((link) => {
        link.addEventListener("click", () => {
            myBurgerMenu.classList.remove('open');
            navIcon.classList.remove('open');
        });
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth > 800 && myBurgerMenu.classList.contains('open')) {
            myBurgerMenu.classList.remove('open');
            navIcon.classList.remove('open');
        }
    });
}

if(menuUser)
{
    menuUser.addEventListener('click', function() {
        menuUser.classList.toggle('active');
        if(menuUser.classList.contains('active')){
            svgUser.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M64 32C28.7 32 0 60.7 0 96L0 416c0 35.3 28.7 64 64 64l320 0c35.3 0 64-28.7 64-64l0-320c0-35.3-28.7-64-64-64L64 32zM224 160c6.7 0 13 2.8 17.6 7.7l104 112c6.5 7 8.2 17.2 4.4 25.9s-12.5 14.4-22 14.4l-208 0c-9.5 0-18.2-5.7-22-14.4s-2.1-18.9 4.4-25.9l104-112c4.5-4.9 10.9-7.7 17.6-7.7z"/></svg>'
        }else{
            svgUser.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M384 480c35.3 0 64-28.7 64-64l0-320c0-35.3-28.7-64-64-64L64 32C28.7 32 0 60.7 0 96L0 416c0 35.3 28.7 64 64 64l320 0zM224 352c-6.7 0-13-2.8-17.6-7.7l-104-112c-6.5-7-8.2-17.2-4.4-25.9s12.5-14.4 22-14.4l208 0c9.5 0 18.2 5.7 22 14.4s2.1 18.9-4.4 25.9l-104 112c-4.5 4.9-10.9 7.7-17.6 7.7z"/></svg>'
        }
    })
}

