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
    const prelimInput = document.getElementById("editPrelim");
    prelimInput.focus();
    prelimInput.dispatchEvent(new Event("input"));
  },
  close: function () {
    this.element.close();
  }
};

/**
 * Simple delete function that directly handles the deletion
 * @param {HTMLButtonElement} button 
 */
function openDeleteModal(button) {
    const row = button.closest('tr');
    const gradesId = row.getAttribute('data-grades-id');
    
    if (!gradesId || gradesId === "0") {
        alert("No grades found to delete");
        return;
    }

    if (confirm("Are you sure you want to delete these grades?")) {
        // Create form data
        const formData = new FormData();
        formData.append('grades_id', gradesId);
        formData.append('action', 'delete_grades'); // Changed to match PHP

        // Send delete request
        fetch('inputgrades.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Clear all grade fields
                row.querySelector('td:nth-child(9)').textContent = '';  // Prelim
                row.querySelector('td:nth-child(10)').textContent = '';  // Midterm
                row.querySelector('td:nth-child(11)').textContent = ''; // Finals
                row.querySelector('td:nth-child(12)').textContent = ''; // Final Grade
                row.querySelector('td:nth-child(13)').textContent = ''; // Status
                
                // Reset grades_id
                row.setAttribute('data-grades-id', '0');
                
                alert('Grades deleted successfully');
            } else {
                throw new Error(data.message || 'Failed to delete grades');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting grades: ' + error.message);
        });
    }
}

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

/**
 * Handles the deletion of student grades
 * @param {HTMLButtonElement} button - The delete button clicked
 */
function deleteGrades(button) {
  const row = button.closest('tr');
  const gradesId = row.getAttribute('data-grades-id');
  const schoolId = row.getAttribute('data-school-id');

  if (!gradesId || gradesId === "0") {
    alert("No grades found to delete");
    return;
  }

  if (confirm("Are you sure you want to delete these grades?")) {
    const formData = new FormData();
    formData.append('action', 'delete_grade');
    formData.append('grades_id', gradesId);
    formData.append('school_id', schoolId);

    fetch('inputgrades.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Clear all grade-related fields in the row
        row.children[7].textContent = ''; // Prelim
        row.children[8].textContent = ''; // Midterm
        row.children[9].textContent = ''; // Finals
        row.children[10].textContent = ''; // Final Grades
        row.children[11].textContent = ''; // Status
        
        // Reset the grades_id attribute
        row.setAttribute('data-grades-id', '0');
        
        alert('Grades deleted successfully');
      } else {
        alert(data.message || 'Error deleting grades');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while deleting grades');
    });
  }
}