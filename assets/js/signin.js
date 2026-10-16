// Switch between the Login and Register forms
const tabButtons = document.querySelectorAll(".tab-btn");
const switchLinks = document.querySelectorAll(".switch-link");
const formBoxes = document.querySelectorAll(".form-box");

function showForm(targetId) {
  formBoxes.forEach((box) => box.classList.toggle("active", box.id === targetId));
  tabButtons.forEach((btn) =>
    btn.classList.toggle("active", btn.dataset.target === targetId)
  );
}

tabButtons.forEach((btn) =>
  btn.addEventListener("click", () => showForm(btn.dataset.target))
);

switchLinks.forEach((link) =>
  link.addEventListener("click", (e) => {
    e.preventDefault();
    showForm(link.dataset.target);
  })
);