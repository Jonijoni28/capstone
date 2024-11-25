const deleteEndpoint = "/delete_student.php";
const editEndpoint = "/edit_student_info.php";
const addEndpoint = "/add_student_info.php";

/**
 * Deletes a row from the table based on the button clicked.
 * @param {HTMLButtonElement} button - The button element clicked.
 */
function deleteStudent(button) {
  /** @type {HTMLTableRowElement} */
  const row = button.parentElement.parentElement;
  /** @type {string} */
  const dataId = row.getAttribute("data-id");
  /** @type {boolean} */
  const result = confirm("Do you really want to delete?");

  const url = `${deleteEndpoint}?school_id=${encodeURIComponent(dataId)}`;
  const request = new Request(url, {
    method: "DELETE",
  });

  if (result) {
    fetch(request)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }

        return response.text();
      })
      .then((text) => {
        row.remove(); // Remove the row from the table
        alert(text);
      })
      .catch(error => {
        console.error('There was a problem with your fetch operation:', error);
      });
  }
}

/**
 * Edits the details of a row from the table based on the button clicked.
 * @param {HTMLButtonElement} button - The button element clicked.
 */
function editStudentInfo(button) {
  /**@type {HTMLTableRowElement} */
  const row = button.parentElement.parentElement;
  /** @type {string} */
  const dataId = row.getAttribute("data-id");

  document.getElementById('editSchoolId').value = dataId;
  document.getElementById('editFirstName').value = row.children[2].textContent;
  document.getElementById('editLastName').value = row.children[3].textContent;
  document.getElementById('editGender').value = row.children[4].textContent;
  document.getElementById('editSemester').value = row.children[5].textContent;
  document.getElementById('editNSTP').value = row.children[6].textContent;
  document.getElementById('editDepartment').value = row.children[7].textContent;
  document.getElementById('editCourse').value = row.children[8].textContent;

  const editModal = document.getElementById('editModal');
  editModal.showModal();

  document.getElementById('editForm').onsubmit = function (event) {
    event.preventDefault();
    saveEdit(dataId, row); // Pass the row to saveEdit function
  };
}

/**
 * Closes the edit modal dialog.
 */
function closeModal() {
  const editModal = document.getElementById('editModal');
  editModal.close();
}

/**
 * Saves the edited details of a student.
 * @param {string} dataId - The ID of the student.
 * @param {HTMLTableRowElement} row - The table row of the student to be edited.
 */
function saveEdit(dataId, row) {
  const editForm = document.getElementById('editForm');
  const formData = new FormData(editForm);

  const url = `${editEndpoint}?school_id=${encodeURIComponent(dataId)}`;

  fetch(url, {
    method: "POST",
    body: formData
  })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.text();
    })
    .then(() => {
      alert('Data updated successfully');
      updateTableRow(row); // Update the row in the table
      closeModal(); // Close the modal
    })
    .catch(error => {
      console.error('There was a problem with your fetch operation:', error);
    });
}

/**
 * Updates the table row after editing with the new data.
 * @param {HTMLTableRowElement} row - The table row to be updated.
 */
function updateTableRow(row) {
  row.children[1].textContent = document.getElementById('editSchoolId').value;
  row.children[2].textContent = document.getElementById('editFirstName').value;
  row.children[3].textContent = document.getElementById('editLastName').value;
  row.children[4].textContent = document.getElementById('editGender').value;
  row.children[5].textContent = document.getElementById('editSemester').value;
  row.children[6].textContent = document.getElementById('editNSTP').value;
  row.children[7].textContent = document.getElementById('editDepartment').value;
  row.children[8].textContent = document.getElementById('editCourse').value;
}

/**
 * Adds a new student to the table.
 * @param {object} newData - Data of the new student.
 */
function addTableRow(newData) {
  // Create new table row
  const newRow = document.createElement('tr');
  newRow.setAttribute('data-id', newData.school_id);

  // Create table data cells for each column
  const columns = ['school_id', 'first_name', 'last_name', 'gender', 'semester', 'nstp', 'department', 'course'];
  columns.forEach(column => {
    const cell = document.createElement('td');
    cell.textContent = newData[column];
    newRow.appendChild(cell);
  });

  // Create action buttons cell
  const actionCell = document.createElement('td');
  const editButton = document.createElement('button');
  editButton.textContent = 'Edit';
  editButton.className = 'editButton';
  editButton.addEventListener('click', function () {
    editStudentInfo(this);
  });
  const deleteButton = document.createElement('button');
  deleteButton.textContent = 'Delete';
  deleteButton.className = 'deleteButton';
  deleteButton.addEventListener('click', function () {
    deleteStudent(this);
  });
  actionCell.appendChild(editButton);
  actionCell.appendChild(deleteButton);
  newRow.appendChild(actionCell);

  // Append the new row to the table body
  document.getElementById('tableBody').appendChild(newRow);
}

/**
 * Submits add student form data.
 */
function submitAddForm() {
  const addForm = document.getElementById('addForm');
  const formData = new URLSearchParams(new FormData(addForm)); // Serialize form data

  fetch(addEndpoint, {
    method: "POST",
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded' // Set content type
    },
    body: formData
  })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.text();
    })
    .then(data => {
      if (data.includes("Success")) {
        closeAddModal();
        alert('Student added successfully');
        window.location.reload(); // Reload the page or refresh table data if needed
      } else {
        alert(data); // Display the response from the server
      }
    })
    .catch(error => {
      console.error('There was a problem with your fetch operation:', error);
      alert('An error occurred while adding the student');
    });
}

// Attach the submit event listener
document.getElementById('addForm').addEventListener('submit', function (event) {
  event.preventDefault(); // Prevent form submission
  submitAddForm();
});

// Function to open add student modal
function openAddModal() {
  const addModal = document.getElementById('addModal');
  addModal.showModal();
}

// Function to close add student modal
function closeAddModal() {
  const addModal = document.getElementById('addModal');
  addModal.close();
}

// Pagination state management
const DEFAULT_ROWS_PER_PAGE = 5;
class PaginationState {
  constructor() {
    const urlParams = new URLSearchParams(window.location.search);
    this.currentPage = parseInt(urlParams.get('page')) || 1;
    this.rowsPerPage = DEFAULT_ROWS_PER_PAGE;
  }

  updateURL() {
    const url = new URL(window.location);
    url.searchParams.set('page', this.currentPage);
    window.history.pushState({}, '', url);
  }

  setPage(pageNum) {
    this.currentPage = pageNum;
    this.updateURL();
  }
}

const paginationState = new PaginationState();

// Update the pagination display function
function updatePaginationDisplay() {
  const table = document.getElementById("editableTable");
  const tbody = table.querySelector('tbody');
  const rows = Array.from(tbody.getElementsByTagName("tr"))
    .filter(row => !row.id.includes('noResultsRow')); // Exclude the no results row
    
  const totalRows = rows.length;
  const totalPages = Math.ceil(totalRows / paginationState.rowsPerPage);

  // Hide all rows first
  rows.forEach(row => row.style.display = 'none');

  // Show rows for current page
  const startIndex = (paginationState.currentPage - 1) * paginationState.rowsPerPage;
  const endIndex = Math.min(startIndex + paginationState.rowsPerPage, totalRows);

  rows.slice(startIndex, endIndex).forEach(row => row.style.display = '');

  // Reset select all checkbox when changing pages
  const selectAllCheckbox = document.getElementById('selectAllCheckbox');
  if (selectAllCheckbox) {
    selectAllCheckbox.checked = false;
  }

  // Reset individual checkboxes for hidden rows
  rows.forEach(row => {
    const checkbox = row.querySelector('.selectStudentCheckbox');
    if (checkbox && row.style.display === 'none') {
      checkbox.checked = false;
    }
  });

  // Update selection actions visibility
  toggleSelectionActions();

  // Update pagination controls
  updatePaginationControls(totalPages);
}

function updatePaginationControls(totalPages) {
  const paginationElement = document.getElementById('pagination');
  paginationElement.innerHTML = '';

  // Previous button
  const prevButton = document.getElementById('prevPage');
  prevButton.disabled = paginationState.currentPage === 1;

  // Page numbers
  for (let i = 1; i <= totalPages; i++) {
    const pageButton = document.createElement('button');
    pageButton.innerHTML = i;
    pageButton.classList.add('page-button');
    if (i === paginationState.currentPage) {
      pageButton.classList.add('active');
    }
    pageButton.onclick = () => changePage(i);
    paginationElement.appendChild(pageButton);
  }

  // Next button
  const nextButton = document.getElementById('nextPage');
  nextButton.disabled = paginationState.currentPage === totalPages;
}

// Update the change page function
function changePage(pageNum) {
  paginationState.setPage(pageNum);
  updatePaginationDisplay();
  
  // Reset select all checkbox when changing pages
  const selectAllCheckbox = document.getElementById('selectAllCheckbox');
  if (selectAllCheckbox) {
    selectAllCheckbox.checked = false;
  }
  
  // Hide selection actions when changing pages
  const selectionActions = document.getElementById('selectionActions');
  if (selectionActions) {
    selectionActions.style.display = 'none';
  }
}

function prevPage() {
  if (paginationState.currentPage > 1) {
    changePage(paginationState.currentPage - 1);
  }
}

function nextPage() {
  const table = document.getElementById("editableTable");
  const totalRows = table.getElementsByTagName("tr").length - 1;
  const totalPages = Math.ceil(totalRows / paginationState.rowsPerPage);

  if (paginationState.currentPage < totalPages) {
    changePage(paginationState.currentPage + 1);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  updatePaginationDisplay();
});