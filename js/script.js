document.addEventListener("DOMContentLoaded", function() {

    document.querySelector('.search-button').addEventListener('click', function() {
    const searchContainer = document.querySelector('.search-container');
    searchContainer.classList.toggle('active');
  });


});

