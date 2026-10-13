document.addEventListener('click', function (e) {
  var addBtn = e.target.closest('[data-add]');
  if (addBtn) {
    e.preventDefault();
    var list = document.getElementById(addBtn.getAttribute('data-add'));
    if (!list) return;
    var tmpl = list.querySelector(':scope > template');
    if (!tmpl) return;
    var idx = parseInt(list.dataset.nextIndex || '0', 10);
    list.dataset.nextIndex = String(idx + 1);
    var token = list.dataset.idxToken;
    var html = tmpl.innerHTML.split(token).join(idx);
    var tmp = document.createElement('div');
    tmp.innerHTML = html.trim();
    var node = tmp.firstElementChild;
    if (node) list.insertBefore(node, tmpl);
    return;
  }
  var rmBtn = e.target.closest('[data-remove]');
  if (rmBtn) {
    e.preventDefault();
    var item = rmBtn.closest('[data-item]');
    if (item) item.remove();
  }
});
