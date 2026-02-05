// App base scripts
document.addEventListener("DOMContentLoaded", () => {
  const items = document.querySelectorAll("[data-reveal]");
  items.forEach((item, index) => {
    item.style.animationDelay = `${index * 90}ms`;
    item.classList.add("reveal");
  });
});
