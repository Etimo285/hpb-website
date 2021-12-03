let body = document.querySelector('html');
//let savedColor = localStorage.getItem('cp-mycolor');
let colorBtn = [].slice.call(document.querySelectorAll('[data-color]'));

let colorChange = function (color) {
    body.className = '';
    body.classList.add(color);
    localStorage.setItem('cp-mycolor', color);
};

colorBtn.forEach(function (button) {
    button.addEventListener('click', function () {
        let color = button.getAttribute('data-color');
        colorChange(color);
    });
});

/*if (savedColor !== '') {
    colorChange(savedColor);
}*/