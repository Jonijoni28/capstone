const addEndpoint = "/capstone/add_student_grade.php";
const editEndpoint = "/capstone/edit_student_grade.php";

/**
 * @typedef {Object} Grades
 * @property {HTMLInputElement} prelim
 * @property {HTMLInputElement} midterm
 * @property {HTMLInputElement} finals
 */

/**
 * @typedef {Object} Modal
 * @property {HTMLDialogElement} element
 * @property {function(): void} open
 * @property {function(): void} close
 */

/** @type {Grades} */
const grades = {
  prelim: document.getElementById("editPrelim"),
  midterm: document.getElementById("editMidterm"),
  finals: document.getElementById("editFinals")
};

/** @type {Modal} */
const editModal = {
  element: document.getElementById("editModal"),
  open: function () {
    this.element.show();
    // Focus on the first input element inside the modal
    const prelimInput = document.getElementById("editPrelim");
    prelimInput.focus();
    prelimInput.dispatchEvent(new Event("input")); // Trigger input event to validate immediately
  },
  close: function () {
    this.element.close();
  }
};

/**
 * Edits the details of a row from the table based on the button clicked.
 * @param {HTMLButtonElement} button - The button element clicked.
 */
function editGradesInfo(button) {
  /** @type {HTMLTableRowElement} */
  const row = button.parentElement.parentElement;
  /**@type {string} */
  const gradesId = row.getAttribute('data-grades-id');
  /**@type {string} */
  const schoolId = row.getAttribute('data-school-id');

  /** @type {boolean} */
  let isGradesEmpty = (gradesId === "0") ? true : false;

  grades.prelim.value = row.children[7].textContent;
  grades.midterm.value = row.children[8].textContent;
  grades.finals.value = row.children[9].textContent;

  editModal.open();

  document.getElementById('editForm').onsubmit = function (event) {
    event.preventDefault();
    const editForm = document.getElementById('editForm');
    const formData = new FormData(editForm);

    const addRequest = new Request(addEndpoint, {
      method: "POST",
      body: editForm
    });

    if (isGradesEmpty) {
      fetch(addRequest)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          
          return response.text();
        })
        .then(text => {
          alert(text);
          updateTableRow(row);
        })
        .catch(error => {
          console.error('There was a problem with your fetch operation:', error);
        });

      editModal.close();
    } else {
      alert("TODO: Update grades!");

      updateTableRow(row);

      editModal.close();
    }
  };
}

/**
 * Updates the table row after editing with the new data.
 * @param {HTMLTableRowElement} row - The table row to be updated.
 */
function updateTableRow(row) {
  row.children[7].textContent = grades.prelim.value;
  row.children[8].textContent = grades.midterm.value;
  row.children[9].textContent = grades.finals.value;
}