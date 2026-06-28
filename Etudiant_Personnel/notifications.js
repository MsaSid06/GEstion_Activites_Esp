function removeNotif(id) {
  if (confirm("Supprimer cette notification ?")) {
    document.getElementById(id).remove();
  }
}
