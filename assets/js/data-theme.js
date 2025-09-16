const toggleSwitch = document.querySelector('.theme-switch input[type="checkbox"]');
const mediaQuery = window.matchMedia('(max-width: 640px)');

const logoPequenaLight = document.getElementById('pequena-light');
const logoPequenaDark = document.getElementById('pequena-dark');

function switchTheme(e) {
    if (e.target.checked) {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');

        logoPequenaLight.style.display = 'none';
        logoPequenaDark.style.display = 'block';
    }
    else {
        document.documentElement.setAttribute('data-theme', 'light');
        localStorage.setItem('theme', 'light');

        logoPequenaLight.style.display = 'block';
        logoPequenaDark.style.display = 'none';
    }
}

toggleSwitch.addEventListener('change', switchTheme, false);


const currentTheme = localStorage.getItem('theme') ? localStorage.getItem('theme') : null;

if (currentTheme) {
    document.documentElement.setAttribute('data-theme', currentTheme);

    if (currentTheme === 'dark') {
        toggleSwitch.checked = true;
    } else {
        toggleSwitch.checked = false;
        logoPequenaDark.style.display = 'none';
    }
}