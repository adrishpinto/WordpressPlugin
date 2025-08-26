// lang_switcher.js

function getCookie(name) {
  const cookies = document.cookie.split("; ");
  for (let c of cookies) {
    const [key, value] = c.split("=");
    if (key.trim() === name) return decodeURIComponent(value);
  }
  return "";
}

function setCookie(name, value, days) {
  const maxAge = days * 24 * 60 * 60;
  document.cookie = `${name}=${encodeURIComponent(
    value
  )}; path=/; max-age=${maxAge}`;
}

document.addEventListener("DOMContentLoaded", function () {
  var box = document.getElementById("activeloc-dropdown");
  var menu = document.getElementById("activeloc-options");
  var currentFlag = document.getElementById("activeloc-current-flag");

  currentFlag.addEventListener("click", function () {
    menu.classList.toggle("show");
  });
  document.addEventListener("click", function (e) {
    if (!box.contains(e.target)) menu.classList.remove("show");
  });

  var items = menu.querySelectorAll("li");
  items.forEach(function (li) {
    li.addEventListener("click", function () {
      var lang = li.dataset.lang;
      var url = li.dataset.url;
      setCookie("activeloc_lang", lang, 365);
      window.location.href = url;
    });
  });

  var savedLang = getCookie("activeloc_lang");
  if (savedLang) {
    items.forEach(function (li) {
      if (li.dataset.lang === savedLang) li.style.border = "2px solid red";
    });
    var savedItem = Array.from(items).find(
      (li) => li.dataset.lang === savedLang
    );
    if (savedItem) currentFlag.textContent = savedItem.textContent;
  }
});
