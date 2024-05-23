/**
 * Edits the details of a row from the table based on the button clicked.
 * @param {HTMLButtonElement} button - The button element clicked.
 */
function editGradesInfo(button) {
    /**@type {HTMLTableRowElement} */
  const row = button.parentElement.parentElement;
  /** @type {string} */
  const dataId = row.getAttribute("data-id");

  document.getElementById("editPrelim").value = row.children[7].textContent;
  document.getElementById("editMidterm").value = row.children[8].textContent;
  document.getElementById("editFinals").value = row.children[9].textContent;

  openEditModal();

  document.getElementById('editForm').onsubmit = function (event) {
    event.preventDefault();
  };
}

function openEditModal() {
    const editModal = document.getElementById("editModal");
    editModal.show();
}

function closeEditModal() {
    const editModal = document.getElementById("editModal");
    editModal.close();
}