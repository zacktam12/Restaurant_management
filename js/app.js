// Custom JavaScript to replace Bootstrap functionality

// Modal functionality
document.addEventListener('DOMContentLoaded', function() {
    // Get the modal
    var modal = document.getElementById("placeModal");
    
    // Get the button that opens the modal
    var modalButtons = document.querySelectorAll("button[data-name]");
    
    // Get the <span> element that closes the modal
    var closeButtons = document.getElementsByClassName("btn-close");
    
    // When the user clicks on a button, open the modal 
    modalButtons.forEach(function(button) {
        button.addEventListener("click", function() {
            modal.classList.add("show");
            modal.style.display = "block";
            
            // Populate modal with data
            var name = this.getAttribute('data-name');
            var description = this.getAttribute('data-description');
            var city = this.getAttribute('data-city');
            var country = this.getAttribute('data-country');
            var rating = this.getAttribute('data-rating');
            var category = this.getAttribute('data-category');
            
            // Update the modal's content
            document.getElementById('placeModalLabel').textContent = name + ' Details';
            document.getElementById('modal-place-name').textContent = name;
            document.getElementById('modal-place-description').textContent = description;
            document.getElementById('modal-place-location').textContent = city + ', ' + country;
            document.getElementById('modal-place-rating').textContent = rating;
            document.getElementById('modal-place-category').textContent = category.charAt(0).toUpperCase() + category.slice(1);
            document.getElementById('modal-place-name-input').value = name;
            document.getElementById('modal-place-city-input').value = city;
        });
    });
    
    // When the user clicks on <span> (x), close the modal
    for (var i = 0; i < closeButtons.length; i++) {
        closeButtons[i].addEventListener("click", function() {
            var modal = this.closest(".modal");
            modal.classList.remove("show");
            modal.style.display = "none";
        });
    }
    
    // When the user clicks anywhere outside of the modal, close it
    window.addEventListener("click", function(event) {
        if (event.target.classList.contains("modal")) {
            event.target.classList.remove("show");
            event.target.style.display = "none";
        }
    });
    
    // Alert dismiss functionality
    var alertCloseButtons = document.querySelectorAll(".alert .btn-close");
    alertCloseButtons.forEach(function(button) {
        button.addEventListener("click", function() {
            var alert = this.closest(".alert");
            alert.classList.remove("show");
            setTimeout(function() {
                alert.style.display = "none";
            }, 150);
        });
    });
    
    // Auto-submit search form when user stops typing for 500ms
    var searchInput = document.getElementById('search');
    if (searchInput) {
        var searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                document.querySelector('form.g-3').submit();
            }, 500);
        });
    }
    
    // Auto-submit when category changes
    var categorySelect = document.getElementById('category');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            document.querySelector('form.g-3').submit();
        });
    }
    
    // Tab functionality
    var tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var tabId = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and panes
            document.querySelectorAll('.tab-button').forEach(function(btn) {
                btn.classList.remove('active');
            });
            document.querySelectorAll('.tab-pane').forEach(function(pane) {
                pane.classList.remove('active');
            });
            
            // Add active class to clicked button and corresponding pane
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
});