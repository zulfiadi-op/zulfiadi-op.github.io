const hamburger = document.querySelector('.header .nav-bar .nav-list .hamburger');
const mobile_menu = document.querySelector('.header .nav-bar .nav-list ul');
const menu_item = document.querySelectorAll('.header .nav-bar .nav-list ul li a');
const header = document.querySelector('.header.container');

hamburger.addEventListener('click', () => {
	hamburger.classList.toggle('active');
	mobile_menu.classList.toggle('active');
});

document.addEventListener('scroll', () => {
	var scroll_position = window.scrollY;
	if (scroll_position > 250) {
		header.style.backgroundColor = '#29323c';
	} else {
		header.style.backgroundColor = 'transparent';
	}
});

menu_item.forEach((item) => {
	item.addEventListener('click', () => {
		hamburger.classList.toggle('active');
		mobile_menu.classList.toggle('active');
	});
});



// main.js
document.addEventListener('DOMContentLoaded', function () {
  // Aktifkan nav-link sesuai nama file
  const navLinks = document.querySelectorAll('.navbar .nav-link');
  const current = window.location.pathname.split('/').pop() || 'index.html';
  navLinks.forEach(link => {
    const href = link.getAttribute('href');
    if (href === current || (href === 'index.html' && current === '')) {
      link.classList.add('active');
    }
  });

  // Modal image preview (jika ada)
  const projectCards = document.querySelectorAll('.project-card');
  const projectModalImg = document.getElementById('projectModalImg');
  projectCards.forEach(card => {
    card.addEventListener('click', function () {
      const imgSrc = this.getAttribute('data-img');
      if (projectModalImg && imgSrc) projectModalImg.src = imgSrc;
    });
  });
});
