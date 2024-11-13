document.addEventListener("DOMContentLoaded", function() {
    const dropdownBtns = document.querySelectorAll(".dropdown-btn");
    dropdownBtns.forEach(button => {
        button.addEventListener("click", function() {
            // Toggle dropdown menu visibility
            this.classList.toggle("active");
            const dropdownContent = this.nextElementSibling;
            if (dropdownContent.style.display === "block") {
                dropdownContent.style.display = "none";
                this.querySelector(".dropdown-icon").style.transform = "rotate(0deg)";
            } else {
                dropdownContent.style.display = "block";
                this.querySelector(".dropdown-icon").style.transform = "rotate(180deg)";
            }
        });
    });
});
