document.addEventListener('DOMContentLoaded', () => {
  const filterButtons = document.querySelectorAll('.filter-btn');
  const articles = document.querySelectorAll('article');
  const sections = document.querySelectorAll('main section');
  const kategoriDiv = document.querySelector('.kategori');

  // Insert filter grid SETELAH div .kategori, bukan prepend ke main
  const filterGrid = document.createElement('div');
  filterGrid.className = 'grid';
  filterGrid.style.display = 'none';
  filterGrid.style.padding = '0 50px 30px';
  kategoriDiv.insertAdjacentElement('afterend', filterGrid);

  filterButtons.forEach(button => {
    button.addEventListener('click', () => {
      const filter = button.getAttribute('data-filter');

      filterButtons.forEach(btn => btn.classList.remove('aktif'));
      button.classList.add('aktif');

      if (filter === 'all') {
        filterGrid.style.display = 'none';
        filterGrid.innerHTML = '';
        sections.forEach(section => section.style.display = 'block');

      } else {
        sections.forEach(section => section.style.display = 'none');

        filterGrid.innerHTML = '';
        filterGrid.style.display = 'grid';

        articles.forEach(article => {
          if (article.getAttribute('data-category') === filter) {
            filterGrid.appendChild(article.cloneNode(true));
          }
        });

        if (filterGrid.children.length === 0) {
          filterGrid.style.display = 'block';
          filterGrid.innerHTML = '<p style="color:#666; padding:20px 0;">Belum ada artikel dalam kategori ini.</p>';
        }
      }
    });
  });
});