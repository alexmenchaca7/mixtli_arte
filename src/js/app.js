document.addEventListener("DOMContentLoaded", () => {
    const categoriasBtn = document.getElementById("categorias-btn");
    const categoriasModal = document.getElementById("categorias-modal");
    const closeBtn = document.querySelector(".close");

    function enableScroll() {
        document.body.style.overflow = "";
    }

    categoriasModal.classList.remove("show");

    categoriasBtn.addEventListener("click", function () {
        categoriasModal.classList.add("show");
        document.body.style.overflow = "hidden";
    });

    closeBtn.addEventListener("click", function () {
        categoriasModal.classList.remove("show");
        enableScroll();
    });

    window.addEventListener("click", function (e) {
        if (e.target === categoriasModal) {
            categoriasModal.classList.remove("show");
            enableScroll();
        }
    });
});