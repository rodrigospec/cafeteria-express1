/* Menú responsive: sin dependencias externas. */
document.addEventListener("DOMContentLoaded", () => {
  const btn = document.getElementById("menuButton"),
    nav = document.getElementById("menuPrincipal");
  if (!btn || !nav) return;
  btn.addEventListener("click", () => {
    const open = nav.classList.toggle("open");
    btn.setAttribute("aria-expanded", String(open));
  });
});
